<?php

namespace Database\Factories;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscriber>
 */
class SubscriberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'status' => 'pending',
            'consented_at' => now(),
        ];
    }

    public function subscribed(): static
    {
        return $this->state(fn (): array => ['status' => 'subscribed', 'confirmed_at' => now()]);
    }
}
