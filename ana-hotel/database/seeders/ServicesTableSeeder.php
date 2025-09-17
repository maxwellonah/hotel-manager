<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            [
                'name' => 'Airport Transfer',
                'description' => 'Comfortable private transfer from the airport to the hotel',
                'price' => 50.00,
                'is_available' => true
            ],
            [
                'name' => 'Spa Treatment',
                'description' => 'Relaxing 60-minute spa treatment',
                'price' => 80.00,
                'is_available' => true
            ],
            [
                'name' => 'Breakfast Buffet',
                'description' => 'Full breakfast buffet with a variety of options',
                'price' => 25.00,
                'is_available' => true
            ],
            [
                'name' => 'Laundry Service',
                'description' => 'Same-day laundry and dry cleaning service',
                'price' => 15.00,
                'is_available' => true
            ],
            [
                'name' => 'Room Service',
                'description' => '24-hour room service with a selection of meals and beverages',
                'price' => 10.00,
                'is_available' => true
            ]
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
