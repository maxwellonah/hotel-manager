<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_checkout_when_a_completed_payment_already_exists()
    {
        $staff = User::factory()->create([
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
            'payment_status' => 'paid',
            'total_price' => 300,
            'check_in' => now()->subDay()->toDateString(),
            'check_out' => now()->toDateString(),
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'transaction_reference' => 'COMPLETE123',
            'amount' => 300,
            'payment_method' => 'cash',
            'status' => Payment::STATUS_COMPLETED,
            'notes' => 'Paid before checkout',
            'paid_at' => now()->subHours(2),
        ]);

        $response = $this->actingAs($staff)->put(route('check-out.complete', $booking), [
            'notes' => 'Checked out successfully',
        ]);

        $response->assertRedirect(route('check-out.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'checked_out',
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'cleaning',
        ]);

        $this->assertEquals(1, Payment::where('booking_id', $booking->id)
            ->where('status', Payment::STATUS_COMPLETED)
            ->count());
    }
}
