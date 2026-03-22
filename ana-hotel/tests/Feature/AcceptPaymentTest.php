<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptPaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_completed_payment_when_accepting_payment_for_an_unpaid_future_booking()
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
            'status' => 'available',
        ]);

        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'user_id' => $guest->id,
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'payment_confirmed_at' => null,
            'total_price' => 25000,
            'check_in' => now()->addDays(2)->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($staff)->post(route('bookings.accept-payment', $booking));

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'payment_status' => 'paid',
        ]);

        $this->assertEquals(1, Payment::where('booking_id', $booking->id)
            ->where('status', Payment::STATUS_COMPLETED)
            ->count());
    }
}
