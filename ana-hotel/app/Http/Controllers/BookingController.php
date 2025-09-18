<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $bookings = \App\Models\Booking::with(['room.roomType', 'user'])
            ->latest()
            ->paginate(10);
            
        return view('bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new booking.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roomTypes = \App\Models\RoomType::where('is_available', true)->get();
        
        return view('bookings.create', [
            'roomTypes' => $roomTypes,
            'guests' => auth()->user()->role === 'admin' ? \App\Models\User::where('role', 'guest')->get() : null
        ]);
    }

    /**
     * Store a newly created booking in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'user_id' => 'required_if:is_guest_booking,1|exists:users,id',
            'check_in' => 'required|date|after_or_equal:today',
            'is_early_checkin' => 'nullable|boolean',
            'check_out' => 'required|date|after:check_in',
            'special_requests' => 'nullable|string|max:1000',
            'is_guest_booking' => 'sometimes|in:1',
            'is_early_checkin' => 'sometimes|boolean',
        ]);

        // Set user_id to current user if not a guest booking
        if (!isset($validated['is_guest_booking']) || $validated['is_guest_booking'] != '1') {
            $validated['user_id'] = auth()->id();
        }

        // Check if early check-in is requested
        $isEarlyCheckin = $request->has('is_early_checkin') && $request->is_early_checkin;
        
        // If early check-in is requested, we need to find a room that's available now
        $checkInDate = $isEarlyCheckin ? now() : $validated['check_in'];
        
        // Get the first available room of the selected type
        $room = \App\Models\Room::where('room_type_id', $validated['room_type_id'])
            ->where('status', 'available')
            ->whereDoesntHave('bookings', function($query) use ($checkInDate, $validated) {
                $query->where('status', '!=', 'cancelled')
                    ->where(function($q) use ($checkInDate, $validated) {
                        $q->whereBetween('check_in', [$checkInDate, $validated['check_out']])
                          ->orWhereBetween('check_out', [$checkInDate, $validated['check_out']])
                          ->orWhere(function($q) use ($checkInDate, $validated) {
                              $q->where('check_in', '<', $checkInDate)
                                ->where('check_out', '>', $validated['check_out']);
                          });
                    });
            })
            ->first();

        if (!$room) {
            return back()->withErrors([
                'room_type_id' => 'No rooms of this type are available for the selected dates.'
            ])->withInput();
        }

        // Calculate total price
        $roomType = \App\Models\RoomType::findOrFail($validated['room_type_id']);
        $checkIn = new Carbon($validated['check_in']);
        $checkOut = new Carbon($validated['check_out']);
        $nights = $checkIn->diffInDays($checkOut);
        $totalPrice = $roomType->price_per_night * $nights;

        // Create the booking
        $bookingData = [
            'room_id' => $room->id,
            'user_id' => $validated['user_id'] ?? auth()->id(),
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'status' => $isEarlyCheckin ? 'checked_in' : 'confirmed',
            'special_requests' => $request->input('special_requests'),
            'total_price' => $totalPrice,
            'is_early_checkin' => $isEarlyCheckin,
        ];
        
        if ($isEarlyCheckin) {
            $bookingData['checked_in_at'] = now();
        }
        
        $booking = new \App\Models\Booking($bookingData);
        $booking->save();

        // Update room status
        $room->status = 'occupied';
        $room->save();

        return redirect()->route('bookings.show', $booking->id)
            ->with('success', 'Booking created successfully!');
    }

    /**
     * Display the specified booking.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $booking = \App\Models\Booking::with(['room.roomType', 'user'])->findOrFail($id);
        
        return view('bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified booking.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $booking = \App\Models\Booking::findOrFail($id);
        $rooms = \App\Models\Room::where('status', 'available')
            ->orWhere('id', $booking->room_id)
            ->get();
        $guests = \App\Models\User::where('role', 'guest')->get();
        
        return view('bookings.edit', [
            'booking' => $booking,
            'rooms' => $rooms,
            'guests' => $guests
        ]);
    }

    /**
     * Update the specified booking in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * Process early check-in for a booking.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function earlyCheckIn($id)
    {
        $booking = \App\Models\Booking::findOrFail($id);
        
        if ($booking->processEarlyCheckIn()) {
            return response()->json([
                'success' => true,
                'message' => 'Early check-in processed successfully.',
                'status' => $booking->status,
                'is_early_checkin' => $booking->is_early_checkin
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'This booking cannot be checked in early. It may not be confirmed, already checked in, or not paid for.',
        ], 422);
    }
    
    public function update(Request $request, $id)
    {
        $booking = \App\Models\Booking::findOrFail($id);
        
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'user_id' => 'required|exists:users,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
            'special_requests' => 'nullable|string',
            'is_early_checkin' => 'nullable|boolean',
        ]);
        
        // Check if early check-in is being set
        $isEarlyCheckin = $request->has('is_early_checkin') && $request->is_early_checkin;
        
        // Check room availability if room or dates changed
        if ($booking->room_id != $validated['room_id'] || 
            $booking->check_in != $validated['check_in'] || 
            $booking->check_out != $validated['check_out']) {
                
            // If early check-in is requested, we need to check availability from now
            $checkInDate = $isEarlyCheckin ? now() : $validated['check_in'];
            
            // Check if the room is available for the selected dates (excluding this booking)
            $isRoomAvailable = !\App\Models\Booking::where('room_id', $validated['room_id'])
                ->where('id', '!=', $booking->id)
                ->where('status', '!=', 'cancelled')
                ->where(function($query) use ($checkInDate, $validated) {
                    $query->whereBetween('check_in', [$checkInDate, $validated['check_out']])
                          ->orWhereBetween('check_out', [$checkInDate, $validated['check_out']])
                          ->orWhere(function($q) use ($checkInDate, $validated) {
                              $q->where('check_in', '<', $checkInDate)
                                ->where('check_out', '>', $validated['check_out']);
                          });
                })
                ->exists();

            if (!$isRoomAvailable) {
                return back()->withErrors([
                    'room_id' => 'The selected room is not available for the selected dates.'
                ])->withInput();
            }
        }

        // Calculate total price if room or dates changed
        if ($booking->room_id != $validated['room_id'] || 
            $booking->check_in != $validated['check_in'] || 
            $booking->check_out != $validated['check_out']) {
                
            $room = \App\Models\Room::findOrFail($validated['room_id']);
            $days = (new \DateTime($validated['check_in']))->diff(new \DateTime($validated['check_out']))->days;
            $validated['total_price'] = $room->roomType->price_per_night * $days;
        }

        // Handle early check-in if requested
        if ($isEarlyCheckin && $booking->status === 'confirmed') {
            $validated['status'] = 'checked_in';
            $validated['checked_in_at'] = now();
            $validated['is_early_checkin'] = true;
        }
        
        // Update the booking
        $booking->update($validated);
        
        // Update room status based on booking status
        $room = $booking->room;
        if (in_array($booking->status, ['checked_in', 'confirmed']) && $room->status !== 'occupied') {
            $room->status = 'occupied';
            $room->save();
        } elseif ($booking->status === 'checked_out' && $room->status === 'occupied') {
            // Check if there are other active bookings for this room
            $hasActiveBookings = \App\Models\Booking::where('room_id', $room->id)
                ->where('status', 'checked_in')
                ->where('id', '!=', $booking->id)
                ->exists();
                
            if (!$hasActiveBookings) {
                $room->status = 'available';
                $room->save();
            }
        }

        return redirect()->route('bookings.show', $booking->id)
            ->with('success', 'Booking updated successfully!');
    }

    /**
     * Remove the specified booking from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $booking = \App\Models\Booking::findOrFail($id);
        
        // Update room status to available when booking is deleted
        if ($booking->room) {
            $booking->room->update(['status' => 'available']);
        }
        
        $booking->delete();
        
        return redirect()->route('bookings.index')
            ->with('success', 'Booking deleted successfully!');
    }

    /**
     * Accept payment for a booking (mark as paid) without checking the guest in.
     * This helps record the day money was received for clearer reports.
     *
     * @param  int  $bookingId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function acceptPayment($bookingId)
    {
        $booking = \App\Models\Booking::with('payments')->findOrFail($bookingId);

        // Only admins/receptionists can reach here via middleware; double-check state
        if ($booking->status === 'checked_in') {
            return back()->with('error', 'Guest is already checked in.');
        }

        // Ensure there is at least one payment we can confirm. If none is completed yet,
        // attempt to confirm the most recent pending payment.
        $completedPayment = $booking->payments()
            ->where('status', \App\Models\Payment::STATUS_COMPLETED)
            ->latest('paid_at')
            ->first();

        if (!$completedPayment) {
            $pendingPayment = $booking->payments()
                ->where('status', \App\Models\Payment::STATUS_PENDING)
                ->latest()
                ->first();

            if ($pendingPayment) {
                // Mark the pending payment as completed now
                $pendingPayment->status = \App\Models\Payment::STATUS_COMPLETED;
                $pendingPayment->paid_at = now();
                $pendingPayment->save();
            } else {
                // No payment record at all — create a manual completion entry to keep audit trail
                \App\Models\Payment::create([
                    'booking_id' => $booking->id,
                    'transaction_reference' => 'MANUAL' . strtoupper(uniqid()),
                    'amount' => $booking->total_price ?? 0,
                    'payment_method' => 'cash',
                    'status' => \App\Models\Payment::STATUS_COMPLETED,
                    'notes' => 'Manually confirmed by staff (no prior payment record).',
                    'paid_at' => now(),
                ]);
            }
        }

        // Mark booking as paid and stamp confirmation time
        $booking->payment_status = 'paid';
        $booking->payment_confirmed_at = now();
        $booking->save();

        return back()->with('success', 'Payment accepted and recorded successfully.');
    }

    /**
     * Admin: Create a pending payment for a booking to simulate/test payment flow.
     *
     * @param int $bookingId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createPendingPayment($bookingId)
    {
        $booking = \App\Models\Booking::with('payments')->findOrFail($bookingId);

        // Prevent creating if already paid fully or guest is checked in
        if ($booking->status === 'checked_in') {
            return back()->with('error', 'Cannot create a pending payment for a checked-in booking.');
        }
        if ($booking->payment_status === 'paid') {
            return back()->with('error', 'Booking is already marked as paid.');
        }

        // Create a small pending payment entry
        \App\Models\Payment::create([
            'booking_id' => $booking->id,
            'transaction_reference' => 'PEND' . strtoupper(uniqid()),
            'amount' => $booking->total_price ?? 0,
            'payment_method' => 'cash',
            'status' => \App\Models\Payment::STATUS_PENDING,
            'notes' => 'Created by admin for testing/simulation from UI',
        ]);

        return back()->with('success', 'Pending payment created for this booking.');
    }
}
