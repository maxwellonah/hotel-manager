<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\HousekeepingTask;
use App\Models\User;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    /**
     * Display an overview of rooms and their housekeeping status.
     */
    public function rooms(Request $request)
    {
        $query = Room::with(['roomType'])
            ->withCount(['bookings as active_bookings_count' => function ($q) {
                $q->whereIn('status', ['confirmed', 'checked_in'])
                  ->whereDate('check_out', '>=', now());
            }]);

        // Optional filters
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $rooms = $query->orderBy('room_number')->paginate(20)->withQueryString();

        // Load recent housekeeping task for each room
        $recentTasks = HousekeepingTask::select('id', 'room_id', 'status', 'task_type', 'priority', 'updated_at')
            ->latest('updated_at')
            ->get()
            ->groupBy('room_id');

        return view('housekeeping.rooms', compact('rooms', 'recentTasks'));
    }

    /**
     * Housekeeping dashboard: show all housekeepers and their assigned rooms.
     * Automatically create today's cleaning task for occupied rooms if not done yet today.
     */
    public function dashboard(Request $request)
    {
        // Ensure only admins can access (route already has middleware, double-check here)
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
        $today = now()->toDateString();

        // Load all users with housekeeping role and their assigned rooms
        $housekeepers = User::where('role', 'housekeeping')
            ->with(['assignedRooms' => function($q) {
                $q->with(['roomType']);
            }])
            ->orderBy('name')
            ->get();

        // For each assigned occupied room, ensure a cleaning task exists for today
        foreach ($housekeepers as $hk) {
            foreach ($hk->assignedRooms as $room) {
                // Check if the room is currently occupied (status or active booking)
                $occupied = $room->status === 'occupied' || $room->getCurrentBooking();

                if ($occupied) {
                    // If ANY task exists for today (regardless of status), do not create another
                    $existing = HousekeepingTask::where('room_id', $room->id)
                        ->where('assigned_to', $hk->id)
                        ->where(function($q) use ($today) {
                            $q->whereDate('due_date', $today)
                              ->orWhere(function($qq) use ($today) {
                                  $qq->whereNull('due_date')
                                     ->whereDate('created_at', $today);
                              });
                        })
                        ->first();

                    if (!$existing) {
                        HousekeepingTask::create([
                            'room_id' => $room->id,
                            'assigned_to' => $hk->id,
                            'assigned_by' => auth()->id() ?? $hk->id,
                            'task_type' => 'cleaning',
                            'status' => 'pending',
                            'priority' => 'high',
                            'description' => 'Daily cleaning for occupied room',
                            'due_date' => now(),
                        ]);
                    }
                }
            }
        }

        // Build a per-housekeeper summary including today's tasks
        $summary = [];
        foreach ($housekeepers as $hk) {
            $rooms = $hk->assignedRooms;
            $taskMap = HousekeepingTask::with('room')
                ->where('assigned_to', $hk->id)
                ->where(function($q) use ($today) {
                    $q->whereDate('due_date', $today)
                      ->orWhere(function($qq) use ($today) {
                          $qq->whereNull('due_date')
                             ->whereDate('created_at', $today);
                      });
                })
                ->get()
                ->groupBy('room_id');

            $summary[] = [
                'user' => $hk,
                'rooms' => $rooms,
                'tasks' => $taskMap,
            ];
        }

        // Build an "All Tasks" list with filters
        $filters = [
            'user_id' => $request->query('user_id'),
            'status' => $request->query('status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $tasksQuery = HousekeepingTask::with(['room', 'assignedTo'])
            ->when($filters['user_id'], function ($q, $val) { $q->where('assigned_to', $val); })
            ->when($filters['status'], function ($q, $val) { $q->where('status', $val); })
            ->when($filters['date_from'], function ($q, $val) { $q->whereDate('due_date', '>=', $val); })
            ->when($filters['date_to'], function ($q, $val) { $q->whereDate('due_date', '<=', $val); })
            ->orderByDesc('due_date')
            ->orderByDesc('created_at');

        $allTasks = $tasksQuery->paginate(20)->withQueryString();

        // For filter dropdown
        $hkOptions = User::where('role', 'housekeeping')->orderBy('name')->get(['id','name']);

        return view('housekeeping.dashboard', [
            'summary' => $summary,
            'today' => $today,
            'allTasks' => $allTasks,
            'filters' => $filters,
            'hkOptions' => $hkOptions,
        ]);
    }

    /**
     * Mark today's housekeeping task for a given room and user as completed.
     */
    public function completeTodayTask(Request $request, Room $room, User $user)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $today = now()->toDateString();
        $task = HousekeepingTask::where('room_id', $room->id)
            ->where('assigned_to', $user->id)
            ->whereDate('created_at', $today)
            ->latest()
            ->first();

        if (!$task) {
            return back()->with('error', 'No task found for today.');
        }

        if ($task->status === 'completed') {
            return back()->with('success', 'Task was already completed.');
        }

        $task->markAsCompleted('Marked done via dashboard');

        return back()->with('success', 'Task marked as completed.');
    }

    /**
     * Export filtered housekeeping tasks as CSV (admin only)
     */
    public function exportTasks(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $filters = [
            'user_id' => $request->query('user_id'),
            'status' => $request->query('status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $tasks = HousekeepingTask::with(['room', 'assignedTo'])
            ->when($filters['user_id'], function ($q, $val) { $q->where('assigned_to', $val); })
            ->when($filters['status'], function ($q, $val) { $q->where('status', $val); })
            ->when($filters['date_from'], function ($q, $val) { $q->whereDate('due_date', '>=', $val); })
            ->when($filters['date_to'], function ($q, $val) { $q->whereDate('due_date', '<=', $val); })
            ->orderByDesc('due_date')
            ->orderByDesc('created_at')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="housekeeping_tasks.csv"',
        ];

        $callback = function() use ($tasks) {
            $handle = fopen('php://output', 'w');
            // Header row
            fputcsv($handle, ['Due Date', 'Housekeeper', 'Room', 'Status', 'Type', 'Created At']);
            foreach ($tasks as $t) {
                fputcsv($handle, [
                    optional($t->due_date)->format('Y-m-d'),
                    optional($t->assignedTo)->name,
                    optional($t->room)->room_number,
                    $t->status,
                    $t->task_type,
                    optional($t->created_at)->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show unassigned rooms for balancing workloads (admin only)
     */
    public function unassignedRooms()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $rooms = Room::with('roomType')
            ->whereNull('housekeeping_user_id')
            ->orderBy('room_number')
            ->paginate(20);

        $housekeepers = User::where('role', 'housekeeping')->orderBy('name')->get(['id','name']);

        return view('housekeeping.unassigned', compact('rooms', 'housekeepers'));
    }
}
