<?php

namespace Database\Factories;

use App\Models\MembershipApplication;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MembershipApplication>
 */
class MembershipApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'submission_key' => (string) Str::uuid(),
            'contact_name' => fake()->name(),
            'contact_phone' => fake()->numerify('+234##########'),
            'organisation_name' => fake()->company().' Co-operative Society Limited',
            'organisation_address' => fake()->address(),
            'telephone' => fake()->numerify('+234##########'),
            'email' => fake()->unique()->safeEmail(),
            'cooperative_type' => fake()->randomElement(array_keys(MembershipApplication::TYPES)),
            'organisation_website' => null,
            'member_count' => fake()->numberBetween(MembershipApplication::MINIMUM_MEMBERS, 60),
            'ethnic_group' => fake()->word(),
            'status' => 'new',
            'consented_at' => now(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => ['status' => 'approved']);
    }
}
