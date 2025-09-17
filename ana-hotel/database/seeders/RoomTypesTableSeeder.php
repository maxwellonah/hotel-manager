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
                'amenities' => ['WiFi', 'TV', 'Air Conditioning', 'Coffee Maker'],
                'image' => 'standard-room.jpg'
            ],
            [
                'name' => 'Deluxe Room',
                'description' => 'Spacious room with premium amenities',
                'price_per_night' => 150.00,
                'capacity' => 2,
                'amenities' => ['WiFi', 'TV', 'Air Conditioning', 'Coffee Maker', 'Mini Bar', 'Safe'],
                'image' => 'deluxe-room.jpg'
            ],
            [
                'name' => 'Family Suite',
                'description' => 'Perfect for families with extra space',
                'price_per_night' => 220.00,
                'capacity' => 4,
                'amenities' => ['WiFi', 'TV', 'Air Conditioning', 'Coffee Maker', 'Mini Bar', 'Safe', 'Sofa Bed'],
                'image' => 'family-suite.jpg'
            ],
            [
                'name' => 'Executive Suite',
                'description' => 'Luxury suite with separate living area',
                'price_per_night' => 300.00,
                'capacity' => 2,
                'amenities' => ['WiFi', 'TV', 'Air Conditioning', 'Coffee Maker', 'Mini Bar', 'Safe', 'Work Desk', 'Bathrobe & Slippers'],
                'image' => 'executive-suite.jpg'
            ],
            [
                'name' => 'Presidential Suite',
                'description' => 'The ultimate in luxury and comfort',
                'price_per_night' => 500.00,
                'capacity' => 2,
                'amenities' => ['WiFi', 'TV', 'Air Conditioning', 'Coffee Maker', 'Mini Bar', 'Safe', 'Work Desk', 'Bathrobe & Slippers', 'Jacuzzi', 'Butler Service'],
                'image' => 'presidential-suite.jpg'
            ]
        ];

        foreach ($roomTypes as $roomType) {
            RoomType::create([
                'name' => $roomType['name'],
                'description' => $roomType['description'],
                'price_per_night' => $roomType['price_per_night'],
                'capacity' => $roomType['capacity'],
                'amenities' => json_encode($roomType['amenities']),
                'image' => $roomType['image'],
                'is_available' => true
            ]);
        }
    }
}
