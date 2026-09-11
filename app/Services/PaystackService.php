<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackService
{
    public function initialize(Donation $donation): string
    {
        $response = Http::withToken($this->secret())->acceptJson()->timeout(20)->post('https://api.paystack.co/transaction/initialize', [
            'email' => $donation->email, 'amount' => $donation->amount_minor, 'currency' => $donation->currency, 'reference' => $donation->reference, 'callback_url' => route('donations.callback'),
        ]);
        $response->throw();
        $url = $response->json('data.authorization_url');
        if ($response->json('status') !== true || ! is_string($url) || parse_url($url, PHP_URL_SCHEME) !== 'https' || parse_url($url, PHP_URL_HOST) !== 'checkout.paystack.com') {
            throw new RuntimeException('Invalid checkout response.');
        }

        return $url;
    }

    public function verify(string $reference): array
    {
        $response = Http::withToken($this->secret())->acceptJson()->timeout(20)->get('https://api.paystack.co/transaction/verify/'.rawurlencode($reference));
        $response->throw();
        if ($response->json('status') !== true || ! is_array($response->json('data'))) {
            throw new RuntimeException('Unable to verify payment.');
        }

        return $response->json('data');
    }

    public function validSignature(string $payload, ?string $signature): bool
    {
        $key = config('foundation.paystack_secret');

        return is_string($key) && $key !== '' && is_string($signature) && hash_equals(hash_hmac('sha512', $payload, $key), $signature);
    }

    private function secret(): string
    {
        $key = config('foundation.paystack_secret');
        if (! is_string($key) || $key === '') {
            throw new RuntimeException('Donations are not configured.');
        }

        return $key;
    }
}
