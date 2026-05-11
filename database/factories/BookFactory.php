<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Book> */
class BookFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug(fake()->unique()->sentence(4)),
            'author_id' => Author::inRandomOrder()->value('id'),
            'description' => fake()->paragraph(5),
            'price' => fake()->randomFloat(2, 5, 100),
            'stock' => fake()->numberBetween(0, 50),
            'publish_year' => fake()->year(),
            'cover_image' => fake()->imageUrl,
            'average_rating' => 0,
            'ratings_count' => 0,
            'status' => fake()->randomElement(['active', 'draft', 'archived']),
        ];
    }
}
