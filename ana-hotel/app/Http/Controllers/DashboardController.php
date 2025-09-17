<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard based on user role.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $data = [
            'user' => $user,
            'greeting' => $this->getTimeBasedGreeting()
        ];
        
        switch ($user->role) {
            case 'admin':
                // Add admin-specific data
                $data['stats'] = [
                    'totalRooms' => \App\Models\Room::count(),
                    'availableRooms' => \App\Models\Room::where('status', 'available')->count(),
                    'totalBookings' => \App\Models\Booking::count(),
                    'activeBookings' => \App\Models\Booking::whereIn('status', ['confirmed', 'checked_in'])->count(),
                ];
                return view('dashboard.admin', $data);
                
            case 'receptionist':
                // Add receptionist-specific data
                $today = now()->format('Y-m-d');
                $data['todayCheckIns'] = \App\Models\Booking::whereDate('check_in', $today)
                    ->where('status', 'confirmed')
                    ->count();
                $data['todayCheckOuts'] = \App\Models\Booking::whereDate('check_out', $today)
                    ->where('status', 'checked_in')
                    ->count();
                $data['availableRooms'] = \App\Models\Room::where('status', 'available')->count();
                return view('dashboard.receptionist', $data);
                
            case 'housekeeping':
                // Add housekeeping-specific data
                $data['assignedTasks'] = \App\Models\HousekeepingTask::where('assigned_to', $user->id)
                    ->where('status', '!=', 'completed')
                    ->count();
                $data['pendingTasks'] = \App\Models\HousekeepingTask::where('status', 'pending')
                    ->count();
                $data['completedTasksToday'] = \App\Models\HousekeepingTask::where('assigned_to', $user->id)
                    ->whereDate('completed_at', now()->toDateString())
                    ->count();
                return view('dashboard.housekeeping', $data);
                
            case 'guest':
                // Add guest-specific data
                $data['upcomingBookings'] = \App\Models\Booking::where('user_id', $user->id)
                    ->where('status', 'confirmed')
                    ->whereDate('check_in', '>=', now())
                    ->orderBy('check_in')
                    ->get();
                $data['currentStay'] = \App\Models\Booking::where('user_id', $user->id)
                    ->where('status', 'checked_in')
                    ->first();
                return view('dashboard.guest', $data);
                
            default:
                return view('dashboard.guest', $data);
        }
    }

    /**
     * Get a time-based greeting message.
     *
     * @return string
     */
    protected function getTimeBasedGreeting()
    {
        $hour = now()->hour;
        
        if ($hour < 12) {
            return 'Good Morning';
        } elseif ($hour < 17) {
            return 'Good Afternoon';
        } else {
            return 'Good Evening';
        }
    }
}
