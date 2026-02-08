<?php

namespace Tests\Unit;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected $roomType;
    protected $room1;
    protected $room2;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roomType = RoomType::factory()->create([
            'name' => 'Deluxe',
            'price_per_night' => 200.00,
            'capacity' => 2,
            'beds' => 1,
            'size' => '30m²',
            'max_occupancy' => 3,
        ]);

        $this->room1 = Room::factory()->create([
            'room_number' => '101',
            'room_type_id' => $this->roomType->id,
            'status' => 'available',
        ]);

        $this->room2 = Room::factory()->create([
            'room_number' => '102',
            'room_type_id' => $this->roomType->id,
            'status' => 'available',
        ]);

        $this->user = User::factory()->create();
    }
    /** @test */
    public function it_checks_room_availability_for_given_dates()
    {
        $checkIn = now()->addDays(1)->toDateString();
        $checkOut = now()->addDays(3)->toDateString();

        $response = $this->postJson('/api/v1/check-availability', [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => 1,
            'children' => 0,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['check_in', 'check_out', 'total_available']
            ]);

        $this->assertEquals(2, $response->json('meta.total_available'));

    }

    /** @test */
    public function it_filters_rooms_by_room_type()
    {
        $anotherRoomType = RoomType::factory()->create();

        Room::factory()->create([
            'room_number' => '201',
            'room_type_id' => $anotherRoomType->id,
            'status' => 'available',
        ]);

        $response = $this->postJson('/api/v1/check-availability', [
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'room_type_id' => $anotherRoomType->id,
        ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total_available'));
    }

    /** @test */
    public function it_excludes_booked_rooms_from_availability()
    {
        Booking::factory()->create([
            'room_id' => $this->room1->id,
            'user_id' => $this->user->id,
            'check_in' => now()->addDay(),
            'check_out' => now()->addDays(3),
        ]);

        $response = $this->postJson('/api/v1/check-availability', [
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
        ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total_available'));
        $this->assertEquals('102', $response->json('data.0.room_number'));
    }

    /** @test */
    public function it_handles_early_checkin_scenario()
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room1->id,
            'check_in' => now()->subDay(),
            'check_out' => now()->addDay(),
        ]);

        $response = $this->postJson('/api/v1/check-availability', [
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDay()->toDateString(),
            'ignore_booking_id' => $booking->id,
        ]);

        $response->assertOk();
        $this->assertEquals(2, $response->json('meta.total_available'));

    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->postJson('/api/v1/check-availability', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['check_in', 'check_out']);
    }

    /** @test */
    public function it_validates_check_in_date_is_not_in_the_past()
    {
        $this->postJson('/api/v1/check-availability', [
            'check_in' => now()->subDay()->toDateString(),
            'check_out' => now()->addDay()->toDateString(),
        ])->assertJsonValidationErrors(['check_in']);
    }

    /** @test */
    public function it_validates_check_out_date_is_after_check_in()
    {
        $this->postJson('/api/v1/check-availability', [
            'check_in' => now()->addDays(2)->toDateString(),
            'check_out' => now()->addDay()->toDateString(),
        ])->assertJsonValidationErrors(['check_out']);
    }
}
