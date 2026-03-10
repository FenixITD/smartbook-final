<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::all()->random()?->id,
            'book_id' => Book::all()->random()?->id,
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }
}
