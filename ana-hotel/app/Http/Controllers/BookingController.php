<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RoomType;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index()
    {
        $bookings = Booking::with(['room.roomType', 'user'])->latest()->paginate(10);
        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $roomTypes = RoomType::where('is_available', true)->get();
        $guests = auth()->user()->role === 'admin' ? User::where('role', 'guest')->get() : null;
        return view('bookings.create', compact('roomTypes', 'guests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'user_id' => 'required_if:is_guest_booking,1|exists:users,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'special_requests' => 'nullable|string|max:1000',
            'is_guest_booking' => 'sometimes|in:1',
            'is_early_checkin' => 'sometimes|boolean',
        ]);

        try {
            $booking = $this->bookingService->createBooking($validated);
            return redirect()->route('bookings.show', $booking->id)->with('success', 'Booking created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $booking = Booking::with(['room.roomType', 'user'])->findOrFail($id);
        return view('bookings.show', compact('booking'));
    }

    public function edit($id)
    {
        $booking = Booking::findOrFail($id);
        $rooms = \App\Models\Room::where('status', 'available')->orWhere('id', $booking->room_id)->get();
        $guests = User::where('role', 'guest')->get();
        return view('bookings.edit', compact('booking', 'rooms', 'guests'));
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'user_id' => 'required|exists:users,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
            'special_requests' => 'nullable|string',
            'is_early_checkin' => 'nullable|boolean',
        ]);

        try {
            $this->bookingService->updateBooking($booking, $validated);
            return redirect()->route('bookings.show', $booking->id)->with('success', 'Booking updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['room_id' => $e->getMessage()])->withInput();
        }
    }
    
    public function earlyCheckIn($id)
    {
        try {
            $booking = Booking::findOrFail($id);
            $this->bookingService->processEarlyCheckIn($booking);

            return response()->json([
                'success' => true,
                'message' => 'Early check-in processed successfully.',
                'status' => $booking->status,
                'is_early_checkin' => $booking->is_early_checkin ?? false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        if ($booking->room) {
            $booking->room->update(['status' => 'available']);
        }
        $booking->delete();
        return redirect()->route('bookings.index')->with('success', 'Booking deleted successfully!');
    }

    public function acceptPayment($bookingId)
    {
        $booking = Booking::with('payments')->findOrFail($bookingId);

        if ($booking->status === 'checked_in') {
            return back()->with('error', 'Guest is already checked in.');
        }

        if (!$booking->payments()->where('status', \App\Models\Payment::STATUS_COMPLETED)->exists()) {
            $pendingPayment = $booking->payments()->where('status', \App\Models\Payment::STATUS_PENDING)->latest()->first();
            if ($pendingPayment) {
                $pendingPayment->update(['status' => \App\Models\Payment::STATUS_COMPLETED, 'paid_at' => now()]);
            } else {
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

        $booking->update(['payment_status' => 'paid', 'payment_confirmed_at' => now()]);

        return back()->with('success', 'Payment accepted and recorded successfully.');
    }

    public function createPendingPayment($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->status === 'checked_in') {
            return back()->with('error', 'Cannot create a pending payment for a checked-in booking.');
        }
        if ($booking->payment_status === 'paid') {
            return back()->with('error', 'Booking is already marked as paid.');
        }

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
