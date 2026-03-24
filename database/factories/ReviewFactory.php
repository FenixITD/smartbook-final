<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::all()->random()?->id,
            'book_id' => Book::all()->random()?->id,
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->paragraph(3),
        ];
    }
}
