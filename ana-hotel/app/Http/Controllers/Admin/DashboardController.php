<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_rooms' => Room::count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Payment::sum('amount'),
            'today_checkins' => Booking::whereDate('check_in', today())->count(),
            'today_checkouts' => Booking::whereDate('check_out', today())->count(),
        ];

        // Recent bookings
        $recentBookings = Booking::with(['room', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // Recent users
        $recentUsers = User::latest()
            ->take(5)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentBookings', 'recentUsers'));
    }
}
