<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\CheckOutService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckOutController extends Controller
{
    protected $checkOutService;

    public function __construct(CheckOutService $checkOutService)
    {
        $this->checkOutService = $checkOutService;
    }

    public function index()
    {
        $today = Carbon::today();
        $pendingCheckOuts = Booking::with(['room.roomType', 'user'])
            ->where('status', 'checked_in')
            ->whereDate('check_out', '<=', $today->addDay())
            ->orderBy('check_out')
            ->paginate(10);

        return view('check-out.index', compact('pendingCheckOuts'));
    }

    public function process(Booking $booking)
    {
        if ($booking->status !== 'checked_in') {
            return redirect()->route('check-out.index')->with('error', 'Only checked-in guests can be checked out.');
        }

        $additionalCharges = [
            ['description' => 'Mini-bar', 'amount' => 0],
            ['description' => 'Room Service', 'amount' => 0],
            ['description' => 'Laundry', 'amount' => 0],
            ['description' => 'Other', 'amount' => 0],
        ];

        return view('check-out.process', compact('booking', 'additionalCharges'));
    }

    public function complete(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'additional_charges' => 'nullable|array',
            'additional_charges.*.description' => 'required_with:additional_charges|string|max:255',
            'additional_charges.*.amount' => 'required_with:additional_charges|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->checkOutService->completeCheckOut($booking, $validated);
            return redirect()->route('check-out.index')->with('success', 'Guest has been checked out successfully. Room marked for cleaning.');
        } catch (\Exception $e) {
            return redirect()->route('check-out.index')->with('error', $e->getMessage());
        }
    }
}
