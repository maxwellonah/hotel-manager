<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class HousekeepingTaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     */
    public function index(Request $request)
    {
        // Ensure today's tasks exist for the current housekeeper's assigned occupied rooms
        if (auth()->check() && auth()->user()->role !== 'admin') {
            $today = now()->toDateString();
            $me = auth()->user();
            $assignedRooms = $me->assignedRooms()->with(['roomType'])->get();

            foreach ($assignedRooms as $room) {
                $occupied = $room->status === 'occupied' || $room->getCurrentBooking();
                if ($occupied) {
                    // If ANY task exists for today (regardless of status), do not create another
                    $existing = HousekeepingTask::where('room_id', $room->id)
                        ->where('assigned_to', $me->id)
                        ->whereDate('due_date', $today)
                        ->first();
                    if (!$existing) {
                        HousekeepingTask::create([
                            'room_id' => $room->id,
                            'assigned_to' => $me->id,
                            'assigned_by' => $me->id,
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

        $query = HousekeepingTask::with(['room', 'assignedTo', 'assignedBy'])
            ->latest();

        // Filter by status if provided
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // If not admin, show only tasks assigned to the current user
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            $query->where('assigned_to', auth()->id());
        }

        $tasks = $query->paginate(15)->withQueryString();

        return view('housekeeping.tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        $rooms = Room::orderBy('room_number')->get(['id','room_number']);
        $staff = User::orderBy('name')->get(['id','name']);

        return view('housekeeping.tasks.create', compact('rooms', 'staff'));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'assigned_to' => 'required|exists:users,id',
            'task_type' => 'required|in:cleaning,inspection,maintenance,deep_cleaning,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
        ]);

        $validated['assigned_by'] = auth()->id();
        $validated['status'] = 'pending';

        $task = HousekeepingTask::create($validated);

        return redirect()->route('housekeeping-tasks.show', $task)->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(HousekeepingTask $housekeeping_task)
    {
        $task = $housekeeping_task->load(['room', 'assignedTo', 'assignedBy']);
        return view('housekeeping.tasks.show', compact('task'));
    }

    /**
     * Mark a task as completed by the assignee (or admin).
     */
    public function complete(HousekeepingTask $housekeeping_task)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        // Only admin or the assigned user can complete
        if ($user->role !== 'admin' && $housekeeping_task->assigned_to !== $user->id) {
            abort(403, 'Unauthorized');
        }

        if ($housekeeping_task->status === 'completed') {
            return back()->with('success', 'Task already completed.');
        }

        $housekeeping_task->markAsCompleted('Marked done by ' . $user->name);
        return back()->with('success', 'Task marked as completed.');
    }

    /**
     * Cancel a housekeeping task (admin only).
     */
    public function cancel(HousekeepingTask $housekeeping_task)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        if ($housekeeping_task->status === 'completed') {
            return back()->with('error', 'Cannot cancel a completed task.');
        }

        $housekeeping_task->update([
            'status' => 'cancelled',
            'notes' => trim(($housekeeping_task->notes ?: '') . '\nCancelled by ' . $user->name . ' on ' . now()->format('Y-m-d H:i')),
        ]);

        return back()->with('success', 'Task cancelled.');
    }
}
