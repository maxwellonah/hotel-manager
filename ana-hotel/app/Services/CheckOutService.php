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
            $completedPayment = $booking->payments()
                ->where('status', Payment::STATUS_COMPLETED)
                ->latest('paid_at')
                ->first();

            $booking->update([
                'status' => 'checked_out',
                'checked_out_at' => now(),
                'payment_status' => $completedPayment ? 'paid' : ($booking->payment_status ?? 'pending'),
            ]);

            if (!$completedPayment) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'transaction_reference' => 'CHKOUT' . strtoupper(uniqid()),
                    'amount' => $booking->total_price + $additionalChargesTotal,
                    'status' => Payment::STATUS_COMPLETED,
                    'notes' => 'Payment recorded at checkout',
                    'paid_at' => now(),
                ]);
            } elseif ($additionalChargesTotal > 0 || !empty($validatedData['notes'])) {
                $noteParts = array_filter([
                    $completedPayment->notes,
                    $additionalChargesTotal > 0 ? 'Checkout additional charges: ' . number_format($additionalChargesTotal, 2) : null,
                    !empty($validatedData['notes']) ? 'Checkout note: ' . $validatedData['notes'] : null,
                ]);

                $completedPayment->update([
                    'notes' => implode(' | ', $noteParts),
                ]);
            }

            $booking->room->update(['status' => 'cleaning']);
        });
    }
}
