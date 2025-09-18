@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Housekeeping Dashboard</h2>
                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($today)->format('F j, Y') }}</div>
                </div>

                @if (session('success'))
                    <div class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @forelse($summary as $entry)
                    @php
                        $hk = $entry['user'];
                        $rooms = $entry['rooms'];
                        $tasks = $entry['tasks'];
                    @endphp
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-3">{{ $hk->name }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Occupied</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Today's Task</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($rooms as $room)
                                        @php
                                            $task = optional($tasks->get($room->id))->first();
                                            $occupied = $room->status === 'occupied' || $room->getCurrentBooking();
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->room_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->roomType->name ?? '—' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $occupied ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ $occupied ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($task)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($task->status==='completed') bg-green-100 text-green-800
                                                        @elseif($task->status==='in_progress') bg-blue-100 text-blue-800
                                                        @else bg-yellow-100 text-yellow-800 @endif">
                                                        {{ ucfirst(str_replace('_',' ', $task->status)) }}
                                                    </span>
                                                    @if($task->completed_at)
                                                        <span class="ml-2 text-xs text-gray-500">{{ $task->completed_at->format('H:i') }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @if($task && $task->status !== 'completed')
                                                    <form action="{{ route('housekeeping.tasks.complete-today', ['room' => $room->id, 'user' => $hk->id]) }}" method="POST" class="inline" onsubmit="return confirm('Mark today\'s cleaning as done for Room {{ $room->room_number }}?');">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-green-600 text-white text-xs hover:bg-green-700">Mark Done</button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-400 text-xs">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No rooms assigned.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="text-gray-500">No housekeepers found.</div>
                @endforelse

                <!-- All Tasks (Admin Overview) -->
                <div class="mt-10">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold">All Tasks</h3>
                        <a href="{{ route('housekeeping.dashboard.export', request()->query()) }}" class="px-3 py-2 text-sm rounded bg-gray-100 text-gray-700 hover:bg-gray-200">Export CSV</a>
                    </div>

                    <form method="GET" action="{{ route('housekeeping.dashboard') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Housekeeper</label>
                            <select name="user_id" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All</option>
                                @foreach($hkOptions as $hk)
                                    <option value="{{ $hk->id }}" {{ ($filters['user_id'] ?? '') == $hk->id ? 'selected' : '' }}>{{ $hk->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All</option>
                                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date From</label>
                            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date To</label>
                            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Filter</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Housekeeper</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($allTasks as $task)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($task->due_date)->format('Y-m-d') ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $task->assignedTo->name ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $task->room->room_number ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($task->status==='completed') bg-green-100 text-green-800
                                                @elseif($task->status==='in_progress') bg-blue-100 text-blue-800
                                                @elseif($task->status==='pending') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst(str_replace('_',' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst(str_replace('_',' ', $task->task_type)) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            @if($task->status !== 'completed')
                                                <form action="{{ route('housekeeping-tasks.complete', $task) }}" method="POST" class="inline" onsubmit="return confirm('Mark this task as completed?');">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-green-600 text-white text-xs hover:bg-green-700">Complete</button>
                                                </form>
                                            @endif
                                            @if($task->status !== 'completed' && $task->status !== 'cancelled')
                                                <form action="{{ route('housekeeping-tasks.cancel', $task) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this task?');">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-red-600 text-white text-xs hover:bg-red-700">Cancel</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No tasks found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $allTasks->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
