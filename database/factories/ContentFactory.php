<?php

namespace Database\Factories;

use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
{
    protected $model = Content::class;

    public function definition(): array
    {
        $title = Str::title(fake()->words(5, true));

        return [
            'type' => 'post',
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'excerpt' => fake()->paragraph(),
            'body' => collect(fake()->paragraphs(5))->map(fn (string $p): string => '<p>'.$p.'</p>')->implode("\n"),
            'status' => 'draft',
            'published_at' => null,
            'featured' => false,
            'is_demo' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => 'published',
            'published_at' => now()->subDays(fake()->numberBetween(1, 400)),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => 'scheduled',
            'published_at' => now()->addWeek(),
        ]);
    }

    public function demo(): static
    {
        return $this->state(fn (): array => [
            'is_demo' => true,
            'status' => 'draft',
            'review_notes' => 'Demo content created during development. Requires organisational verification before publication.',
        ]);
    }

    public function type(string $type): static
    {
        return $this->state(fn (): array => ['type' => $type]);
    }
}
