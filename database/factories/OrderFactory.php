<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    private const ORDER_STATUSES = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];

    private const ORDER_PAYMENT_METHODS = ['card', 'cash', 'webpay'];

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->value('id'),
            'total' => fake()->randomFloat(2, 10, 500),
            'status' => fake()->randomElement(self::ORDER_STATUSES),
            'shipping_address' => fake()->address(),
            'payment_method' => fake()->randomElement(self::ORDER_PAYMENT_METHODS),
        ];
    }
}
