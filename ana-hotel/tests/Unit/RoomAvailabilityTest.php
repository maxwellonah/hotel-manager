<?php

namespace Tests\Unit;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomAvailabilityTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test room type
        $this->roomType = \App\Models\RoomType::create([
            'name' => 'Deluxe',
            'description' => 'A deluxe room with a view',
            'price_per_night' => 200.00,
            'capacity' => 2,
            'beds' => 1,
            'size' => '30m²',
            'max_occupancy' => 3,
        ]);
        
        // Create test rooms
        $this->room1 = \App\Models\Room::create([
            'room_number' => '101',
            'room_type_id' => $this->roomType->id,
            'floor' => 1,
            'status' => 'available',
            'notes' => 'A nice room'
        ]);
        
        $this->room2 = \App\Models\Room::create([
            'room_number' => '102',
            'room_type_id' => $this->roomType->id,
            'floor' => 1,
            'status' => 'available',
            'notes' => 'Another nice room with minibar'
        ]);
        
        // Create a test user
        $this->user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    
    /** @test */
    public function it_checks_room_availability_for_given_dates()
    {
        $checkIn = now()->addDays(1)->format('Y-m-d');
        $checkOut = now()->addDays(3)->format('Y-m-d');
        
        $response = $this->postJson('/api/v1/check-availability', [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => 1,
            'children' => 0,
        ]);
        
        $response->assertStatus(200);
        $responseData = $response->json();
        
        // Check the response structure matches the RoomAvailabilityResource
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
        
        // Check meta data
        $this->assertEquals($checkIn, $responseData['meta']['check_in']);
        $this->assertEquals($checkOut, $responseData['meta']['check_out']);
        $this->assertEquals(2, $responseData['meta']['total_available']);
        
        // Check rooms data
        $this->assertCount(2, $responseData['data']);
        $roomNumbers = collect($responseData['data'])->pluck('room_number')->toArray();
        $this->assertContains('101', $roomNumbers);
        $this->assertContains('102', $roomNumbers);
    }
    
    /** @test */
    public function it_filters_rooms_by_room_type()
    {
        // Create a different room type
        $anotherRoomType = RoomType::create([
            'name' => 'Standard',
            'description' => 'A standard room',
            'price_per_night' => 100.00,
            'capacity' => 2,
            'beds' => 1,
            'size' => '25m²',
            'max_occupancy' => 2,
        ]);
        
        // Create a room of the new type
        $standardRoom = Room::create([
            'room_number' => '201',
            'room_type_id' => $anotherRoomType->id,
            'floor' => 2,
            'status' => 'available',
            'notes' => 'Standard room',
        ]);
        
        $response = $this->postJson('/api/v1/check-availability', [
            'check_in' => now()->addDays(1)->format('Y-m-d'),
            'check_out' => now()->addDays(3)->format('Y-m-d'),
            'room_type_id' => $anotherRoomType->id,
            'adults' => 1,
            'children' => 0,
        ]);
        
        $response->assertStatus(200);
        $responseData = $response->json();
        
        // Check the response structure matches the RoomAvailabilityResource
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
        
        // Check meta data
        $this->assertEquals(1, $responseData['meta']['total_available']);
        
        // Check rooms data
        $this->assertCount(1, $responseData['data']);
        $this->assertEquals('201', $responseData['data'][0]['room_number']);
    }
    
    /** @test */
    public function it_excludes_booked_rooms_from_availability()
    {
        // Book room 1 for the test dates
        Booking::create([
            'room_id' => $this->room1->id,
            'user_id' => $this->user->id,
            'check_in' => now()->addDays(1),
            'check_out' => now()->addDays(3),
            'status' => 'confirmed',
            'total_price' => 400.00,
            'adults' => 2,
            'children' => 0,
        ]);
        
        // Check availability for the same dates
        $response = $this->postJson('/api/v1/check-availability', [
            'check_in' => now()->addDays(1)->format('Y-m-d'),
            'check_out' => now()->addDays(3)->format('Y-m-d'),
            'adults' => 1,
            'children' => 0,
        ]);
        
        $response->assertStatus(200);
        $responseData = $response->json();
        
        // Check the response structure matches the RoomAvailabilityResource
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
        
        // Only room 2 should be available
        $this->assertEquals(1, $responseData['meta']['total_available']);
        $this->assertCount(1, $responseData['data']);
        $this->assertEquals('102', $responseData['data'][0]['room_number']);
    }
    
    /** @test */
    public function it_handles_early_checkin_scenario()
    {
        // Create a booking that ends today (early check-in scenario)
        $booking = Booking::create([
            'room_id' => $this->room1->id,
            'user_id' => $this->user->id,
            'check_in' => now()->subDays(1),
            'check_out' => now()->addDays(1),
            'status' => 'confirmed',
            'total_price' => 400.00,
            'adults' => 2,
            'children' => 0,
            'is_early_checkin' => true,
        ]);
        
        // Don't update the room status to 'occupied' as the controller filters by 'available' status
        // $this->room1->update(['status' => 'occupied']);
        
        // Try to book the same room for today (should be allowed for early check-in)
        $response = $this->postJson('/api/v1/check-availability', [
            'check_in' => now()->format('Y-m-d'),
            'check_out' => now()->addDays(1)->format('Y-m-d'),
            'ignore_booking_id' => $booking->id,
            'adults' => 1,
            'children' => 0,
        ]);
        
        $response->assertStatus(200);
        $responseData = $response->json();
        
        // Check the response structure matches the RoomAvailabilityResource
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
        
        // The room should be available when ignoring the current booking
        $this->assertEquals(1, $responseData['meta']['total_available']);
        $this->assertCount(1, $responseData['data']);
        
        // Check that the room is in the available rooms list
        $roomNumbers = collect($responseData['data'])->pluck('room_number')->toArray();
        $this->assertContains($this->room1->room_number, $roomNumbers);
        $this->assertNotContains($this->room2->room_number, $roomNumbers);
    }
    
    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/v1/check-availability', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in', 'check_out']);
    }
    
    /** @test */
    public function it_validates_check_in_date_is_not_in_the_past()
    {
        $response = $this->postJson('/api/v1/check-availability', [
            'check_in' => now()->subDay()->format('Y-m-d'),
            'check_out' => now()->addDay()->format('Y-m-d'),
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in']);
    }
    
    /** @test */
    public function it_validates_check_out_date_is_after_check_in()
    {
        $response = $this->postJson('/api/v1/check-availability', [
            'check_in' => now()->addDays(2)->format('Y-m-d'),
            'check_out' => now()->addDay()->format('Y-m-d'),
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_out']);
    }
}
