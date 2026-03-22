<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\CheckInService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtendStayTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_extends_stay_and_records_only_the_additional_payment_amount()
    {
        $guest = User::factory()->create([
            'role' => 'guest',
        ]);

        $roomType = RoomType::factory()->create([
            'price_per_night' => 200,
        ]);

        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'occupied',
        ]);

        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'user_id' => $guest->id,
            'status' => 'checked_in',
            'payment_status' => 'paid',
            'total_price' => 400,
            'check_in' => now()->subDay()->toDateString(),
            'check_out' => now()->addDay()->toDateString(),
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'transaction_reference' => 'PAY123',
            'amount' => 400,
            'payment_method' => 'cash',
            'status' => Payment::STATUS_COMPLETED,
            'notes' => 'Original stay payment',
            'paid_at' => now()->subDay(),
        ]);

        $result = app(CheckInService::class)->extendStay($booking, [
            'additional_nights' => 2,
            'payment_method' => 'cash',
            'notes' => 'Guest extended stay',
        ]);

        $this->assertSame(2, $result['additionalNights']);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'total_price' => 800,
            'payment_status' => 'paid',
        ]);

        $this->assertEquals(2, Payment::where('booking_id', $booking->id)
            ->where('status', Payment::STATUS_COMPLETED)
            ->count());

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'amount' => 400,
            'status' => Payment::STATUS_COMPLETED,
        ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => 400,
            'status' => Payment::STATUS_COMPLETED,
            'payment_method' => 'cash',
        ]);
    }
}
