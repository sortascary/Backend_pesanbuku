<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\bookDaerah>
 */
class BookDaerahFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => $this->faker->word,
            'price'  => $this->faker->randomElement([15000, 17000, 20000]),
            'daerah' => $this->faker->word,
        ];
    }
}
