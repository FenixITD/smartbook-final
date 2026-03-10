<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::all()->random()?->id,
            'total' => fake()->randomFloat(2, 10, 500),
            'status' => fake()->randomElement(['pending', 'paid', 'shipped', 'delivered', 'cancelled']),
            'shipping_address' => fake()->address(),
            'payment_method' => fake()->randomElement(['card', 'cash', 'WebPay']),
        ];
    }
}
