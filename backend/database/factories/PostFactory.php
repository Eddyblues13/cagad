<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'title' => rtrim(fake()->sentence(6), '.'),
            'excerpt' => fake()->paragraph(),
            'body' => collect(fake()->paragraphs(5))->map(fn (string $paragraph): string => "<p>{$paragraph}</p>")->implode(''),
            'author_name' => fake()->name(),
            'status' => PostStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(?CarbonInterface $at = null): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatus::Published,
            'published_at' => $at ?? now()->subDays(fake()->numberBetween(1, 60)),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatus::Published,
            'published_at' => now()->addDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function withCover(): static
    {
        return $this->state(function (): array {
            $publicId = 'cagad/blog/covers/'.Str::lower(Str::random(12));

            return [
                'cover_image_url' => "https://res.cloudinary.com/demo/image/upload/v1700000000/{$publicId}.jpg",
                'cover_image_public_id' => $publicId,
                'cover_image_alt' => fake()->sentence(4),
            ];
        });
    }
}
