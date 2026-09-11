<?php

namespace Database\Factories;

use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'submission_key' => (string) Str::uuid(),
            'type' => 'contact',
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'message' => fake()->paragraph(),
            'status' => 'new',
            'consented_at' => now(),
        ];
    }
}
