<?php

namespace App\Services;

use App\Jobs\SendDonationReceipt;
use App\Models\Donation;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DonationService
{
    public function __construct(private PaystackService $paystack) {}

    public function initialize(array $data): Donation
    {
        abort_unless(config('foundation.donations_enabled') && config('foundation.paystack_secret'), 503, 'Online giving is not yet available. Please contact GSF.');
        $parts = explode('.', (string) $data['amount']);
        $minor = ((int) $parts[0] * 100) + (int) str_pad($parts[1] ?? '', 2, '0');
        $donation = Donation::firstOrCreate(['submission_key' => $data['submission_key']], [
            'reference' => (string) Str::uuid(), 'name' => $data['name'], 'email' => strtolower($data['email']), 'phone' => $data['phone'] ?? null, 'anonymous' => $data['anonymous'] ?? false, 'message' => $data['message'] ?? null, 'campaign_id' => $data['campaign_id'] ?? null, 'amount_minor' => $minor, 'currency' => $data['currency'], 'consented_at' => now(),
        ]);
        if ($donation->email !== strtolower($data['email']) || $donation->amount_minor !== $minor || $donation->currency !== $data['currency'] || (string) $donation->campaign_id !== (string) ($data['campaign_id'] ?? '')) {
            throw ValidationException::withMessages(['amount' => 'This donation submission has already been used. Please reload the form.']);
        }
        if ($donation->checkout_url || $donation->status === 'success') {
            return $donation;
        }
        try {
            $url = $this->paystack->initialize($donation);
            $donation->update(['checkout_url' => $url]);
            PaymentTransaction::firstOrCreate(['event_key' => 'initialize:'.$donation->reference], ['donation_id' => $donation->id, 'status' => 'initialized']);
        } catch (\Throwable $exception) {
            PaymentTransaction::firstOrCreate(['event_key' => 'initialize-error:'.$donation->reference], ['donation_id' => $donation->id, 'status' => 'initialization_failed']);
            report($exception);
            throw ValidationException::withMessages(['amount' => 'We could not start checkout. Please try again shortly.']);
        }

        return $donation->refresh();
    }

    public function settle(array $data): Donation
    {
        return DB::transaction(function () use ($data): Donation {
            $donation = Donation::where('reference', $data['reference'] ?? '')->lockForUpdate()->firstOrFail();
            if (! isset($data['amount'],$data['currency'],$data['customer']['email']) || (string) $data['amount'] !== (string) $donation->amount_minor || $data['currency'] !== $donation->currency || strtolower($data['customer']['email']) !== strtolower($donation->email)) {
                throw ValidationException::withMessages(['payment' => 'Payment details do not match the donation.']);
            }
            $status = $data['status'] ?? 'pending';
            if ($donation->status === 'success') {
                return $donation;
            }
            if ($status === 'success' && empty($data['id'])) {
                throw ValidationException::withMessages(['payment' => 'Payment identifier is missing.']);
            }
            PaymentTransaction::firstOrCreate(['event_key' => $donation->reference.':'.$status], ['donation_id' => $donation->id, 'status' => $status, 'provider_id' => (string) ($data['id'] ?? '')]);
            if ($status === 'success') {
                $donation->update(['status' => 'success', 'provider_id' => (string) $data['id'], 'paid_at' => now()]);
                SendDonationReceipt::dispatch($donation->id)->afterCommit();
            } elseif (in_array($status, ['failed', 'abandoned', 'reversed'], true)) {
                $donation->update(['status' => 'failed']);
            }

            return $donation->refresh();
        }, 3);
    }
}
