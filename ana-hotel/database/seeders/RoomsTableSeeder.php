<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\RoomType;

class RoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all room types
        $roomTypes = RoomType::all();
        
        // Define floor configurations
        $floors = [
            ['number' => 1, 'prefix' => '1', 'room_count' => 15],
            ['number' => 2, 'prefix' => '2', 'room_count' => 15],
            ['number' => 3, 'prefix' => '3', 'room_count' => 10],
            ['number' => 4, 'prefix' => '4', 'room_count' => 5],
            ['number' => 5, 'prefix' => '5', 'room_count' => 5]
        ];
        
        $roomNumber = 101;
        $roomTypeIndex = 0;
        $roomTypeCount = count($roomTypes);
        
        foreach ($floors as $floor) {
            for ($i = 1; $i <= $floor['room_count']; $i++) {
                // Determine room type based on floor
                $roomTypeIndex = ($roomTypeIndex + $i - 1) % $roomTypeCount;
                $roomType = $roomTypes[$roomTypeIndex];
                
                // Create room with only the fields that exist in the rooms table
                Room::create([
                    'room_number' => (string)$roomNumber,
                    'room_type_id' => $roomType->id,
                    'floor' => $floor['number'],
                    'status' => 'available',
                    'notes' => $roomType->name . ' on floor ' . $floor['number']
                ]);
                
                $roomNumber++;
            }
            
            // Reset room number for next floor
            $roomNumber = ($floor['number'] + 1) * 100 + 1;
        }
    }
}
