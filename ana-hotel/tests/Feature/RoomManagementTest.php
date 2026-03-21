<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoomManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_clears_active_bookings_for_a_room_and_deletes_linked_children()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
        ]);

        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'occupied',
        ]);

        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'user_id' => $guest->id,
            'status' => 'checked_in',
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDay()->toDateString(),
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'transaction_reference' => 'TESTPAY123',
            'amount' => 200,
            'payment_method' => 'cash',
            'status' => Payment::STATUS_PENDING,
        ]);

        $service = Service::create([
            'name' => 'Laundry',
            'description' => 'Laundry service',
            'price' => 50,
            'is_available' => true,
        ]);

        DB::table('booking_services')->insert([
            'booking_id' => $booking->id,
            'service_id' => $service->id,
            'quantity' => 1,
            'price' => 50,
            'total_price' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.rooms.clear-active-bookings', $room));

        $response->assertRedirect(route('admin.rooms.index'));

        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id,
        ]);

        $this->assertDatabaseMissing('payments', [
            'id' => $payment->id,
        ]);

        $this->assertDatabaseMissing('booking_services', [
            'booking_id' => $booking->id,
            'service_id' => $service->id,
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'available',
        ]);
    }
}
