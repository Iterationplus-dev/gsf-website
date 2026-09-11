<?php

namespace Database\Factories;

use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => (string) Str::uuid(),
            'submission_key' => (string) Str::uuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => null,
            'anonymous' => false,
            'amount_minor' => fake()->numberBetween(10, 500) * 100_000,
            'currency' => 'NGN',
            'status' => 'pending',
            'consented_at' => now(),
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (): array => [
            'status' => 'success',
            'provider_id' => (string) fake()->unique()->numberBetween(1_000_000, 9_999_999),
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => ['status' => 'failed']);
    }
}
