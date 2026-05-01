<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Book;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CartItem> */
class CartItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->value('id'),
            'book_id' => Book::inRandomOrder()->value('id'),
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }
}
