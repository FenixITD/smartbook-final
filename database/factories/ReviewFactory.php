<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Review> */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->value('id'),
            'book_id' => Book::inRandomOrder()->value('id'),
            'rating' => fake()->randomFloat(1, 1, 5),
            'comment' => fake()->paragraph(3),
        ];
    }
}
