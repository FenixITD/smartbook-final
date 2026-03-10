<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 5, 1000);
        return [
            'order_id' => Order::factory(),
            'book_id' => Book::all()->random()?->id,
            'quantity' => fake()->numberBetween(1, 5),
            'price_at_purchase' => $price,
        ];
    }
}
