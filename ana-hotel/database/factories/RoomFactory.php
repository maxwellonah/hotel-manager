<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RoomType;

class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'room_number' => $this->faker->unique()->numberBetween(100, 500),
            'room_type_id' => RoomType::factory(),
            'floor' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement(['available', 'occupied', 'maintenance']),
        ];
    }
}
