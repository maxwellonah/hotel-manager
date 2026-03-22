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

            // Update payment status from pending to paid
            $pendingPayment = $booking->payments()->where('status', Payment::STATUS_PENDING)->latest()->first();
            if ($pendingPayment) {
                $pendingPayment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'paid_at' => now(),
                    'notes' => ($pendingPayment->notes ?? '') . ' - Payment confirmed during check-in',
                ]);
            } else {
                // Check if completed payment already exists
                $existingCompletedPayment = $booking->payments()->where('status', Payment::STATUS_COMPLETED)->first();
                if ($existingCompletedPayment) {
                    // Payment already exists, just add a note
                    $existingCompletedPayment->update([
                        'notes' => ($existingCompletedPayment->notes ?? '') . ' - Guest checked in on ' . now()->format('Y-m-d H:i:s'),
                    ]);
                } else {
                    // Create payment record if none exists
                    Payment::create([
                        'booking_id' => $booking->id,
                        'transaction_reference' => 'CHECKIN' . strtoupper(uniqid()),
                        'amount' => $booking->total_price ?? 0,
                        'payment_method' => 'cash',
                        'status' => Payment::STATUS_COMPLETED,
                        'notes' => 'Payment confirmed during check-in',
                        'paid_at' => now(),
                    ]);
                }
            }

            // Update booking payment status
            $booking->update(['payment_status' => 'paid', 'payment_confirmed_at' => now()]);
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
            // Update booking details
            $booking->update([
                'check_out' => $newCheckOut,
                'total_price' => $booking->total_price + $additionalCost,
                'special_requests' => ($booking->special_requests ?? '') . "\n\nStay extended by {$additionalNights} night(s) on " . now()->format('Y-m-d') . ". " . ($validatedData['notes'] ?? ''),
            ]);

            // Create a NEW payment for the extension - NEVER modify existing payments
            Payment::create([
                'booking_id' => $booking->id,
                'transaction_reference' => 'EXT' . strtoupper(uniqid()),
                'amount' => $additionalCost,
                'payment_method' => $validatedData['payment_method'] ?? 'cash',
                'status' => Payment::STATUS_COMPLETED,
                'notes' => "Extension payment for {$additionalNights} night(s) at ₦{$pricePerNight}/night" . (!empty($validatedData['notes']) ? ' | ' . $validatedData['notes'] : ''),
                'paid_at' => now(),
            ]);

            // Log the extension for audit purposes
            \Log::info('Stay extended', [
                'booking_id' => $booking->id,
                'additional_nights' => $additionalNights,
                'additional_cost' => $additionalCost,
                'new_check_out' => $newCheckOut,
                'new_total_price' => $booking->total_price + $additionalCost,
                'extension_payment_ref' => 'EXT' . uniqid(),
            ]);

            $booking->update([
                'payment_status' => 'paid',
                'payment_confirmed_at' => now(),
            ]);
        });
        
        return ['additionalNights' => $additionalNights, 'newCheckOut' => $newCheckOut];
    }
}
