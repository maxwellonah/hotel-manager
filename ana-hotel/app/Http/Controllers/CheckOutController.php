<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckOutController extends Controller
{
    /**
     * Display a listing of pending check-outs.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $today = Carbon::today();
        
        $pendingCheckOuts = Booking::with(['room.roomType', 'user'])
            ->where('status', 'checked_in')
            ->whereDate('check_out', '<=', $today->addDay()) // Include tomorrow's check-outs
            ->orderBy('check_out')
            ->paginate(10);
            
        return view('check-out.index', compact('pendingCheckOuts'));
    }

    /**
     * Show the check-out form for a specific booking.
     *
     * @param  Booking  $booking
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function process(Booking $booking)
    {
        if ($booking->status !== 'checked_in') {
            return redirect()->route('check-out.index')
                ->with('error', 'Only checked-in guests can be checked out.');
        }
        
        // Calculate any additional charges
        $additionalCharges = [
            ['description' => 'Mini-bar', 'amount' => 0],
            ['description' => 'Room Service', 'amount' => 0],
            ['description' => 'Laundry', 'amount' => 0],
            ['description' => 'Other', 'amount' => 0],
        ];
        
        return view('check-out.process', compact('booking', 'additionalCharges'));
    }

    /**
     * Complete the check-out process.
     *
     * @param  Request  $request
     * @param  Booking  $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request, Booking $booking)
    {
        if ($booking->status !== 'checked_in') {
            return redirect()->route('check-out.index')
                ->with('error', 'Only checked-in guests can be checked out.');
        }
        
        $validated = $request->validate([
            'additional_charges' => 'nullable|array',
            'additional_charges.*.description' => 'required_with:additional_charges|string|max:255',
            'additional_charges.*.amount' => 'required_with:additional_charges|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Calculate total additional charges
        $additionalChargesTotal = 0;
        if (!empty($validated['additional_charges'])) {
            foreach ($validated['additional_charges'] as $charge) {
                if (!empty($charge['description']) && !empty($charge['amount'])) {
                    $additionalChargesTotal += (float) $charge['amount'];
                }
            }
        }
        
        // Update booking status (do not write payment_method on bookings)
        $booking->update([
            'status' => 'checked_out',
            'checked_out_at' => now(),
            'check_out_notes' => $validated['notes'] ?? null,
            'additional_charges' => $validated['additional_charges'] ?? [],
            'additional_charges_total' => $additionalChargesTotal,
            'total_paid' => $booking->total_price + $additionalChargesTotal,
        ]);

        // Record a completed payment for checkout
        Payment::create([
            'booking_id' => $booking->id,
            'transaction_reference' => 'CHKOUT' . strtoupper(uniqid()),
            'amount' => $booking->total_price + $additionalChargesTotal,
            'status' => Payment::STATUS_COMPLETED,
            'notes' => 'Payment recorded at checkout',
            'paid_at' => now(),
        ]);
        
        // Update room status for housekeeping
        // Use an allowed value for the rooms.status column (e.g., 'cleaning')
        $booking->room->update(['status' => 'cleaning']);
        
        return redirect()->route('check-out.index')
            ->with('success', 'Guest has been checked out successfully. Room marked for cleaning.');
    }
}
