<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Room;
use App\Models\User;

class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'room_id' => Room::factory(),
            'user_id' => User::factory(),
            'check_in' => $this->faker->dateTimeBetween('+1 days', '+2 days'),
            'check_out' => $this->faker->dateTimeBetween('+3 days', '+4 days'),
            'total_price' => $this->faker->randomFloat(2, 100, 1000),
        ];
    }
}
