<?php

namespace Tests\Unit;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_persists_required_guest_counts_when_creating_a_booking()
    {
        $roomType = RoomType::factory()->create([
            'price_per_night' => 150,
        ]);

        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
        ]);

        $booking = app(BookingService::class)->createBooking([
            'room_type_id' => $roomType->id,
            'user_id' => $guest->id,
            'is_guest_booking' => '1',
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'adults' => 2,
            'special_requests' => 'Late arrival',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'room_id' => $room->id,
            'user_id' => $guest->id,
            'adults' => 2,
            'children' => 0,
            'payment_status' => 'pending',
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function it_keeps_the_room_available_for_a_confirmed_future_booking()
    {
        $roomType = RoomType::factory()->create([
            'price_per_night' => 150,
        ]);

        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
        ]);

        app(BookingService::class)->createBooking([
            'room_type_id' => $roomType->id,
            'user_id' => $guest->id,
            'is_guest_booking' => '1',
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addDays(7)->toDateString(),
            'adults' => 1,
        ]);

        $this->assertSame('available', $room->fresh()->status);
    }

    /** @test */
    public function it_uses_a_selected_available_room_when_provided()
    {
        $roomType = RoomType::factory()->create([
            'price_per_night' => 150,
        ]);

        $preferredRoom = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
        ]);

        $booking = app(BookingService::class)->createBooking([
            'room_type_id' => $roomType->id,
            'room_id' => $preferredRoom->id,
            'user_id' => $guest->id,
            'is_guest_booking' => '1',
            'check_in' => now()->addDays(2)->toDateString(),
            'check_out' => now()->addDays(4)->toDateString(),
            'adults' => 1,
        ]);

        $this->assertSame($preferredRoom->id, $booking->room_id);
    }

    /** @test */
    public function it_rejects_a_selected_room_that_is_under_maintenance()
    {
        $roomType = RoomType::factory()->create([
            'price_per_night' => 150,
        ]);

        $unavailableRoom = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'maintenance',
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The selected room is not available for the selected dates.');

        app(BookingService::class)->createBooking([
            'room_type_id' => $roomType->id,
            'room_id' => $unavailableRoom->id,
            'user_id' => $guest->id,
            'is_guest_booking' => '1',
            'check_in' => now()->addDays(2)->toDateString(),
            'check_out' => now()->addDays(4)->toDateString(),
            'adults' => 1,
        ]);
    }

    /** @test */
    public function it_allows_updating_to_a_room_when_the_previous_booking_checks_out_on_the_new_check_in_date()
    {
        $roomType = RoomType::factory()->create([
            'price_per_night' => 150,
        ]);

        $currentRoom = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $targetRoom = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
        ]);

        Booking::factory()->create([
            'room_id' => $targetRoom->id,
            'user_id' => $guest->id,
            'check_in' => '2026-03-26',
            'check_out' => '2026-03-27',
            'status' => 'checked_out',
            'payment_status' => 'paid',
            'adults' => 1,
            'children' => 0,
            'total_price' => 150,
        ]);

        $bookingToUpdate = Booking::factory()->create([
            'room_id' => $currentRoom->id,
            'user_id' => $guest->id,
            'check_in' => '2026-03-27',
            'check_out' => '2026-03-28',
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'adults' => 1,
            'children' => 0,
            'total_price' => 150,
        ]);

        app(BookingService::class)->updateBooking($bookingToUpdate, [
            'room_id' => $targetRoom->id,
            'user_id' => $guest->id,
            'check_in' => '2026-03-27',
            'check_out' => '2026-03-28',
            'status' => 'confirmed',
            'special_requests' => null,
        ]);

        $this->assertEquals($targetRoom->id, $bookingToUpdate->fresh()->room_id);
    }
}
