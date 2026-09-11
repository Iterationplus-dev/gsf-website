<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'disk' => 'public',
            'path' => 'media/'.fake()->uuid().'.jpg',
            'mime' => 'image/jpeg',
            'size' => fake()->numberBetween(40_000, 900_000),
            'alt' => fake()->sentence(6),
            'variants' => ['width' => 1600, 'height' => 1067],
            'approved' => true,
        ];
    }

    public function inGallery(): static
    {
        return $this->state(fn (): array => ['collection' => Media::GALLERY]);
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['approved' => false]);
    }

    public function document(): static
    {
        return $this->state(fn (): array => [
            'mime' => 'application/pdf',
            'path' => 'documents/'.fake()->uuid().'.pdf',
            'variants' => null,
        ]);
    }
}
