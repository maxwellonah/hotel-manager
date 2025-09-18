<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    /**
     * Display a listing of pending check-ins.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $today = Carbon::today();
        
        $pendingCheckIns = Booking::with(['room.roomType', 'user', 'payments'])
            ->where('status', 'confirmed')
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>=', $today)
            ->orderBy('check_in')
            ->paginate(10, ['*'], 'pending_page');
            
        $checkedInGuests = Booking::with(['room.roomType', 'user'])
            ->where('status', 'checked_in')
            ->whereDate('check_out', '>=', $today)
            ->orderBy('check_out')
            ->paginate(10, ['*'], 'checked_in_page');
        
        // All bookings across all days (to allow accepting payment even if not checking in today)
        $allBookings = Booking::with(['room.roomType', 'user', 'payments'])
            ->orderByDesc('check_in')
            ->paginate(10, ['*'], 'all_page');
            
        return view('check-in.index', compact('pendingCheckIns', 'checkedInGuests', 'allBookings'));
    }

    /**
     * Show the check-in form for a specific booking.
     *
     * @param  Booking  $booking
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function process(Booking $booking)
    {
        if ($booking->status !== 'confirmed') {
            return redirect()->route('check-in.index')
                ->with('error', 'Only confirmed bookings can be checked in.');
        }

        $booking->load('user');
        return view('check-in.process', [
            'booking' => $booking,
            'guestHasId' => !empty($booking->user->identification_type) && !empty($booking->user->identification_number),
        ]);
    }

    /**
     * Complete the check-in process.
     *
     * @param  Request  $request
     * @param  Booking  $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request, Booking $booking)
    {
        if ($booking->status !== 'confirmed') {
            return redirect()->route('check-in.index')
                ->with('error', 'Only confirmed bookings can be checked in.');
        }
        
        $user = $booking->user;
        $requireId = empty($user->identification_type) || empty($user->identification_number);

        $rules = [
            'notes' => 'nullable|string|max:1000',
        ];
        if ($requireId) {
            $rules['identification_type'] = 'required|in:passport,id_card,driving_license';
            $rules['identification_number'] = 'required|string|max:50';
        } else {
            $rules['identification_type'] = 'nullable|in:passport,id_card,driving_license';
            $rules['identification_number'] = 'nullable|string|max:50';
        }
        $validated = $request->validate($rules);
        
        // Persist ID to user if newly provided
        if ($requireId && isset($validated['identification_type'], $validated['identification_number'])) {
            $user->update([
                'identification_type' => $validated['identification_type'],
                'identification_number' => $validated['identification_number'],
            ]);
        }

        // Update booking status
        $booking->update([
            'status' => 'checked_in',
            'check_in_notes' => $validated['notes'] ?? null,
            'checked_in_at' => now(),
            'identification_number' => $requireId ? $validated['identification_number'] : $user->identification_number,
            'identification_type' => $requireId ? $validated['identification_type'] : $user->identification_type,
        ]);
        
        // Update room status
        $booking->room->update(['status' => 'occupied']);
        
        return redirect()->route('check-in.index')
            ->with('success', 'Guest has been checked in successfully.');
    }

    /**
     * Cancel a check-in operation.
     *
     * @param  Booking  $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Booking $booking)
    {
        \Log::info('Cancel booking request received', [
            'booking_id' => $booking->id,
            'current_status' => $booking->status
        ]);

        // Allow cancellation for both confirmed and checked-in bookings
        if (!in_array($booking->status, ['confirmed', 'checked_in'])) {
            \Log::warning('Cannot cancel booking: Invalid status', [
                'booking_id' => $booking->id,
                'current_status' => $booking->status,
                'allowed_statuses' => ['confirmed', 'checked_in']
            ]);
            return redirect()->route('check-in.index')
                ->with('error', 'Only confirmed or checked-in bookings can be cancelled.');
        }
        
        try {
            // Start transaction
            \DB::beginTransaction();
            
            // Update booking status to cancelled
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'Cancelled during check-in by receptionist'
            ]);
            
            // Update room status back to available
            if ($booking->room) {
                $booking->room->update(['status' => 'available']);
                \Log::info('Room status updated', [
                    'room_id' => $booking->room->id,
                    'new_status' => 'available'
                ]);
            }
            
            // Also remove from All Bookings/Confirm Payments by deleting the booking record
            // Clean up any pending payments tied to this booking before deletion; keep completed payments for audit
            Payment::where('booking_id', $booking->id)
                ->where('status', '!=', Payment::STATUS_COMPLETED)
                ->delete();

            // Finally delete the booking record
            $booking->delete();

            \DB::commit();
            
            \Log::info('Check-in cancelled successfully', [
                'booking_id' => $booking->id
            ]);
            
            return redirect()->route('check-in.index')
                ->with('success', 'Check-in has been cancelled and the booking has been removed.');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error cancelling check-in', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while cancelling the check-in. Please try again.');
        }
    }

    /**
     * Show the form to extend a guest's stay.
     *
     * @param  Booking  $booking
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showExtendStayForm(Booking $booking)
    {
        if ($booking->status !== 'checked_in') {
            return redirect()->route('check-in.index')
                ->with('error', 'Only checked-in guests can extend their stay.');
        }
        
        return view('check-in.extend', compact('booking'));
    }

    /**
     * Process the extension of a guest's stay.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Booking  $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function extendStay(Request $request, Booking $booking)
    {
        if ($booking->status !== 'checked_in') {
            return redirect()->route('check-in.index')
                ->with('error', 'Only checked-in guests can extend their stay.');
        }

        $validated = $request->validate([
            'additional_nights' => 'required|integer|min:1|max:30',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|in:cash,credit_card,debit_card,bank_transfer',
        ]);

        // Calculate new checkout date and additional cost
        $additionalNights = $validated['additional_nights'];
        $newCheckOut = Carbon::parse($booking->check_out)->addDays($additionalNights);
        $pricePerNight = $booking->room->roomType->price_per_night;
        $additionalCost = $pricePerNight * $additionalNights;

        // Update booking
        $booking->update([
            'check_out' => $newCheckOut,
            'total_price' => $booking->total_price + $additionalCost,
            'special_requests' => $booking->special_requests . "\n\nStay extended by {$additionalNights} night(s) on " . now()->format('Y-m-d') . ". " . ($validated['notes'] ?? ''),
        ]);

        // Record payment for the extension so revenue includes the added amount
        Payment::create([
            'booking_id' => $booking->id,
            'transaction_reference' => 'EXT' . strtoupper(uniqid()),
            'amount' => $additionalCost,
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'status' => Payment::STATUS_COMPLETED,
            'notes' => "Extension payment for {$additionalNights} night(s)",
            'paid_at' => now(),
        ]);

        return redirect()->route('check-in.index')
            ->with('success', "Guest's stay has been extended by {$additionalNights} night(s) until {$newCheckOut->format('M d, Y')}.");
    }
}
