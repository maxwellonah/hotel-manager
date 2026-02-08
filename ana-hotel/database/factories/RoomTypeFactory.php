<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price_per_night' => $this->faker->randomFloat(2, 100, 500),
            'capacity' => $this->faker->numberBetween(1, 4),
            'beds' => $this->faker->numberBetween(1, 2),
            'size' => $this->faker->numberBetween(20, 50) . 'm²',
            'max_occupancy' => $this->faker->numberBetween(1, 5),
        ];
    }
}
