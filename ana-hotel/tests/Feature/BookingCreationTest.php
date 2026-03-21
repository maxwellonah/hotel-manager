<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_booking_and_saves_guest_identification_for_a_guest_without_id()
    {
        $staff = User::factory()->create([
            'role' => 'admin',
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
            'identification_type' => null,
            'identification_number' => null,
        ]);

        $roomType = RoomType::factory()->create([
            'is_available' => true,
            'price_per_night' => 200,
        ]);

        Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $response = $this->actingAs($staff)->post(route('bookings.store'), [
            'room_type_id' => $roomType->id,
            'user_id' => $guest->id,
            'is_guest_booking' => '1',
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'adults' => 2,
            'identification_type' => 'passport',
            'identification_number' => 'A12345678',
            'special_requests' => 'Late arrival',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'user_id' => $guest->id,
            'adults' => 2,
            'status' => 'confirmed',
            'payment_status' => 'pending',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $guest->id,
            'identification_type' => 'passport',
            'identification_number' => 'A12345678',
        ]);
    }
}
