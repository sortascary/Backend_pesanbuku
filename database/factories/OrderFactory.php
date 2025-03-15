<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userid = User::inRandomOrder()->first()?->id ?? User::factory();
        return [
            'user_id' => $userid,
            'payment' => $this->faker->randomElement(['cash', 'transfer', 'angsuran']),
            'isPayed' => $this->faker->boolean,
            'status' => $this->faker->randomElement(['diPesan', 'diProses', 'done']),
            'total_book_price' => $this->faker->numberBetween(10000, 20000),
        ];
    }
}
