<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookClass>
 */
class BookClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class' => $this->faker->word,
            'book_id' => $this->faker->word,
            'stock'  => $this->faker->randomElement([500, 550]),
        ];
    }
}
