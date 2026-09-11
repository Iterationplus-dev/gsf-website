<?php

namespace Database\Factories;

use App\Models\ImpactMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImpactMetric>
 */
class ImpactMetricFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'People reached',
            'value' => fake()->numberBetween(50, 5_000),
            'year' => (int) now()->format('Y'),
            'geography' => 'Rivers State, Nigeria',
            'source' => 'Programme records',
            'published' => true,
        ];
    }

    /** A figure recorded but not yet evidenced, which must stay off the public site. */
    public function unsourced(): static
    {
        return $this->state(fn (): array => ['source' => null, 'published' => false]);
    }
}
