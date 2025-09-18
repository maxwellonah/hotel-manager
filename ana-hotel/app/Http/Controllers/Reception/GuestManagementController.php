<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class GuestManagementController extends Controller
{
    /**
     * Show a simplified list of guest bookings for receptionists.
     * Focus on bookings that have a completed payment but are not checked in yet,
     * so reception can accept the payment and record the paid date.
     */
    public function index(Request $request)
    {
        $request->validate([
            'filter' => 'nullable|in:payable,all,paid'
        ]);

        $filter = $request->input('filter', 'payable');

        $query = Booking::query()
            ->with(['user', 'room.roomType', 'payments'])
            ->latest();

        switch ($filter) {
            case 'paid':
                $query->where('payment_status', 'paid');
                break;
            case 'all':
                // No extra filter
                break;
            case 'payable':
            default:
                // Has at least one pending or completed payment, not checked in yet, and not already marked paid
                $query->where(function ($q) {
                    $q->whereHas('payments', function ($p) {
                        $p->whereIn('status', [\App\Models\Payment::STATUS_COMPLETED, \App\Models\Payment::STATUS_PENDING]);
                    })
                    ->where('status', '!=', 'checked_in')
                    ->where(function ($q2) {
                        $q2->whereNull('payment_status')
                           ->orWhere('payment_status', '!=', 'paid');
                    });
                });
                break;
        }

        $bookings = $query->paginate(15);

        return view('reception.guests.index', [
            'bookings' => $bookings,
            'filter' => $filter,
        ]);
    }
}
