<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_id' => Content::factory()->type('project'),
            'status' => 'active',
            'location' => 'Port Harcourt',
            'state' => 'Rivers State',
            'country' => 'Nigeria',
            'start_date' => now()->subYear(),
            'sdgs' => [1, 8],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'completed',
            'end_date' => now()->subMonths(2),
        ]);
    }
}
