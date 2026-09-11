<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentTransaction>
 */
class PaymentTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'donation_id' => Donation::factory(),
            'event_key' => (string) Str::uuid(),
            'status' => 'initialized',
        ];
    }
}
