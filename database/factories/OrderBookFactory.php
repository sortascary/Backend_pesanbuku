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
        $order = Order::inRandomOrder()->with('user')->first();
        $userDaerah = $order->user->daerah;

        $bookclass = BookClass::inRandomOrder()->with('book.bookdaerah')->first();

        $matchedPrice = $bookclass->book->bookdaerah
            ->firstWhere('daerah', $userDaerah)->price ?? 0;

        return [
            'order_id' => Order::inRandomOrder()->first()?->id ?? Order::factory(),
            'book_class_id' => $bookclass->id,
            'name' => $bookclass->book->name,
            'bought_price' => $matchedPrice,
            'isDone' => $this->faker->boolean,
            'amount' => $this->faker->numberBetween(10, 20),
            'subtotal' => $this->faker->numberBetween(10000, 20000),
        ];
    }
}
