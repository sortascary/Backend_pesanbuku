<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\BookClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderBook>
 */
class OrderBookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::inRandomOrder()->first()?->id ?? Order::factory(),
            'book_class_id' => BookClass::inRandomOrder()->first()?->id,
            'isDone' => $this->faker->boolean,
            'amount' => $this->faker->numberBetween(10, 20),
        ];
    }
}
