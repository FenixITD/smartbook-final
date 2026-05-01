<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderItem> */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 5, 1000);

        return [
            'order_id' => Order::factory(),
            'book_id' => Book::inRandomOrder()->value('id'),
            'quantity' => fake()->numberBetween(1, 5),
            'price_at_purchase' => $price,
        ];
    }
}
