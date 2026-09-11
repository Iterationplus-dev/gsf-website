<?php

namespace App\Jobs;

use App\Mail\Acknowledgement;
use App\Models\Donation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendDonationReceipt implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public int $donationId, public bool $resend = false) {}

    public function middleware(): array
    {
        return [(new WithoutOverlapping('receipt:'.$this->donationId))->releaseAfter(30)->expireAfter(120)];
    }

    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public function handle(): void
    {
        $donation = Donation::findOrFail($this->donationId);
        if ($donation->status !== 'success' || ($donation->receipt_sent_at && ! $this->resend)) {
            return;
        }
        $url = URL::temporarySignedRoute('donations.receipt', now()->addDays(30), ['donation' => $donation->reference]);
        Mail::to($donation->email)->send(new Acknowledgement('Thank you for supporting GSF', 'We received your donation of '.$donation->currency.' '.number_format($donation->amount_minor / 100, 2).'. Your reference is '.$donation->reference.'.', $url, 'View your receipt'));
        $donation->update(['receipt_sent_at' => now()]);
    }
}
