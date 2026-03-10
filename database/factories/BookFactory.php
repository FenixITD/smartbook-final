<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'slug' => Str::slug(fake()->sentence(4)),
            'author_id' => Author::all()->random()?->id,
            'description' => fake()->paragraph(5),
            'price' => fake()->randomFloat(2, 5, 100),
            'stock' => fake()->numberBetween(0, 50),
            'publish_year' => fake()->year(),
            'cover_image' => fake()->imageUrl(300, 450, 'book'),
            'average_rating' => fake()->randomFloat(2, 0, 5),
            'ratings_count' => fake()->numberBetween(0, 500),
            'status' => fake()->randomElement(['active', 'draft', 'archived']),
        ];
    }
}
