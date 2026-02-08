<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class CheckOutService
{
    public function completeCheckOut(Booking $booking, array $validatedData): void
    {
        if ($booking->status !== 'checked_in') {
            throw new \Exception('Only checked-in guests can be checked out.');
        }

        $additionalChargesTotal = 0;
        if (!empty($validatedData['additional_charges'])) {
            foreach ($validatedData['additional_charges'] as $charge) {
                if (!empty($charge['description']) && !empty($charge['amount'])) {
                    $additionalChargesTotal += (float) $charge['amount'];
                }
            }
        }

        DB::transaction(function () use ($booking, $validatedData, $additionalChargesTotal) {
            $booking->update([
                'status' => 'checked_out',
                'checked_out_at' => now(),
                'check_out_notes' => $validatedData['notes'] ?? null,
                'additional_charges' => $validatedData['additional_charges'] ?? [],
                'additional_charges_total' => $additionalChargesTotal,
                'total_paid' => $booking->total_price + $additionalChargesTotal,
            ]);

            Payment::create([
                'booking_id' => $booking->id,
                'transaction_reference' => 'CHKOUT' . strtoupper(uniqid()),
                'amount' => $booking->total_price + $additionalChargesTotal,
                'status' => Payment::STATUS_COMPLETED,
                'notes' => 'Payment recorded at checkout',
                'paid_at' => now(),
            ]);

            $booking->room->update(['status' => 'cleaning']);
        });
    }
}
