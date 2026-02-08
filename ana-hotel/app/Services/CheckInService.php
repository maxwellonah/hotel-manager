<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    public function completeCheckIn(Booking $booking, array $validatedData, bool $requireId): void
    {
        DB::transaction(function () use ($booking, $validatedData, $requireId) {
            $user = $booking->user;
            if ($requireId) {
                $user->update([
                    'identification_type' => $validatedData['identification_type'],
                    'identification_number' => $validatedData['identification_number'],
                ]);
            }

            $booking->update([
                'status' => 'checked_in',
                'check_in_notes' => $validatedData['notes'] ?? null,
                'checked_in_at' => now(),
                'identification_number' => $requireId ? $validatedData['identification_number'] : $user->identification_number,
                'identification_type' => $requireId ? $validatedData['identification_type'] : $user->identification_type,
            ]);

            $booking->room->update(['status' => 'occupied']);
        });
    }

    public function cancelCheckIn(Booking $booking): void
    {
        if (!in_array($booking->status, ['confirmed', 'checked_in'])) {
            throw new \Exception('Only confirmed or checked-in bookings can be cancelled.');
        }

        DB::transaction(function () use ($booking) {
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'Cancelled during check-in by receptionist'
            ]);

            if ($booking->room) {
                $booking->room->update(['status' => 'available']);
            }

            Payment::where('booking_id', $booking->id)
                ->where('status', '!=', Payment::STATUS_COMPLETED)
                ->delete();

            $booking->delete();
        });
    }

    public function extendStay(Booking $booking, array $validatedData): array
    {
        if ($booking->status !== 'checked_in') {
            throw new \Exception('Only checked-in guests can extend their stay.');
        }

        $additionalNights = $validatedData['additional_nights'];
        $newCheckOut = Carbon::parse($booking->check_out)->addDays($additionalNights);
        $pricePerNight = $booking->room->roomType->price_per_night;
        $additionalCost = $pricePerNight * $additionalNights;

        DB::transaction(function () use ($booking, $newCheckOut, $additionalCost, $additionalNights, $validatedData) {
            $booking->update([
                'check_out' => $newCheckOut,
                'total_price' => $booking->total_price + $additionalCost,
                'special_requests' => $booking->special_requests . "\n\nStay extended by {$additionalNights} night(s) on " . now()->format('Y-m-d') . ". " . ($validatedData['notes'] ?? ''),
            ]);

            Payment::create([
                'booking_id' => $booking->id,
                'transaction_reference' => 'EXT' . strtoupper(uniqid()),
                'amount' => $additionalCost,
                'payment_method' => $validatedData['payment_method'] ?? 'cash',
                'status' => Payment::STATUS_COMPLETED,
                'notes' => "Extension payment for {$additionalNights} night(s)",
                'paid_at' => now(),
            ]);
        });
        
        return ['additionalNights' => $additionalNights, 'newCheckOut' => $newCheckOut];
    }
}
