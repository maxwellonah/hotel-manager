<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;

class RoomTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $roomTypes = [
            [
                'name' => 'Standard Room',
                'description' => 'Comfortable room with standard amenities',
                'price_per_night' => 100.00,
                'capacity' => 2,
                'beds' => 1,
                'size' => '25 sqm',
                'max_occupancy' => 2
            ],
            [
                'name' => 'Deluxe Room',
                'description' => 'Spacious room with premium amenities',
                'price_per_night' => 150.00,
                'capacity' => 2,
                'beds' => 1,
                'size' => '35 sqm',
                'max_occupancy' => 2
            ],
            [
                'name' => 'Family Suite',
                'description' => 'Perfect for families with extra space',
                'price_per_night' => 220.00,
                'capacity' => 4,
                'beds' => 2,
                'size' => '55 sqm',
                'max_occupancy' => 4
            ],
            [
                'name' => 'Executive Suite',
                'description' => 'Luxury suite with separate living area',
                'price_per_night' => 300.00,
                'capacity' => 2,
                'beds' => 1,
                'size' => '75 sqm',
                'max_occupancy' => 3
            ],
            [
                'name' => 'Presidential Suite',
                'description' => 'The ultimate in luxury and comfort',
                'price_per_night' => 500.00,
                'capacity' => 2,
                'beds' => 1,
                'size' => '100 sqm',
                'max_occupancy' => 4
            ]
        ];

        foreach ($roomTypes as $roomType) {
            RoomType::create([
                'name' => $roomType['name'],
                'description' => $roomType['description'],
                'price_per_night' => $roomType['price_per_night'],
                'capacity' => $roomType['capacity'],
                'beds' => $roomType['beds'],
                'size' => $roomType['size'],
                'max_occupancy' => $roomType['max_occupancy']
            ]);
        }
    }
}
