<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\CheckInService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    protected $checkInService;

    public function __construct(CheckInService $checkInService)
    {
        $this->checkInService = $checkInService;
    }

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

        $allBookings = Booking::with(['room.roomType', 'user', 'payments'])
            ->orderByDesc('check_in')
            ->paginate(10, ['*'], 'all_page');

        return view('check-in.index', compact('pendingCheckIns', 'checkedInGuests', 'allBookings'));
    }

    public function process(Booking $booking)
    {
        if ($booking->status !== 'confirmed') {
            return redirect()->route('check-in.index')->with('error', 'Only confirmed bookings can be checked in.');
        }
        $booking->load('user');
        return view('check-in.process', [
            'booking' => $booking,
            'guestHasId' => !empty($booking->user->identification_type) && !empty($booking->user->identification_number),
        ]);
    }

    public function complete(Request $request, Booking $booking)
    {
        if ($booking->status !== 'confirmed') {
            return redirect()->route('check-in.index')->with('error', 'Only confirmed bookings can be checked in.');
        }

        $user = $booking->user;
        $requireId = empty($user->identification_type) || empty($user->identification_number);

        $rules = [
            'notes' => 'nullable|string|max:1000',
            'identification_type' => ($requireId ? 'required' : 'nullable') . '|in:passport,id_card,driving_license',
            'identification_number' => ($requireId ? 'required' : 'nullable') . '|string|max:50',
        ];
        $validated = $request->validate($rules);

        try {
            $this->checkInService->completeCheckIn($booking, $validated, $requireId);
            return redirect()->route('check-in.index')->with('success', 'Guest has been checked in successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred during check-in. Please try again.');
        }
    }

    public function cancel(Booking $booking)
    {
        try {
            $this->checkInService->cancelCheckIn($booking);
            return redirect()->route('check-in.index')->with('success', 'Check-in has been cancelled and the booking has been removed.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function showExtendStayForm(Booking $booking)
    {
        if ($booking->status !== 'checked_in') {
            return redirect()->route('check-in.index')->with('error', 'Only checked-in guests can extend their stay.');
        }
        return view('check-in.extend', compact('booking'));
    }

    public function extendStay(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'additional_nights' => 'required|integer|min:1|max:30',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|in:cash,credit_card,debit_card,bank_transfer',
        ]);

        try {
            $extensionData = $this->checkInService->extendStay($booking, $validated);
            return redirect()->route('check-in.index')->with('success', "Guest's stay has been extended by {$extensionData['additionalNights']} night(s) until {$extensionData['newCheckOut']->format('M d, Y')}.");
        } catch (\Exception $e) {
            return redirect()->route('check-in.index')->with('error', $e->getMessage());
        }
    }
}
