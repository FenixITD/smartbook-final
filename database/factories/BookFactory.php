<?php

namespace Database\Factories;

use App\Models\Author;
use App\Services\Book\CoverImageService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $seed = $this->faker->numberBetween(1, 1000);

        try {
            $coverImage = app(CoverImageService::class)->generatePlaceholder($title, $seed);
        } catch (\Exception) {
            $coverImage = null;
        }

        return [
            'title' => $title,
            'slug' => Str::slug(fake()->unique()->sentence(4)),
            'author_id' => Author::all()->random()?->id,
            'description' => fake()->paragraph(5),
            'price' => fake()->randomFloat(2, 5, 100),
            'stock' => fake()->numberBetween(0, 50),
            'publish_year' => fake()->year(),
            'cover_image' => $coverImage,
            'average_rating' => fake()->randomFloat(2, 0, 5),
            'ratings_count' => fake()->numberBetween(0, 500),
            'status' => fake()->randomElement(['published', 'draft', 'archived']),
        ];
    }
}
