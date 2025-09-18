@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Housekeeping Tasks</h2>
                    <div class="space-x-2">
                        <a href="{{ route('housekeeping-tasks.index') }}" class="px-3 py-1.5 rounded text-sm bg-gray-100 text-gray-700">All</a>
                        <a href="{{ route('housekeeping-tasks.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded text-sm bg-yellow-100 text-yellow-800">Pending</a>
                        <a href="{{ route('housekeeping-tasks.index', ['status' => 'completed']) }}" class="px-3 py-1.5 rounded text-sm bg-green-100 text-green-800">Completed</a>
                        <a href="{{ route('housekeeping-tasks.create') }}" class="px-3 py-1.5 rounded text-sm bg-indigo-600 text-white">New Task</a>
                    </div>
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

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($tasks as $task)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $task->room->room_number ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst(str_replace('_',' ', $task->task_type)) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($task->priority==='high' || $task->priority==='urgent') bg-red-100 text-red-800
                                            @elseif($task->priority==='medium') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800 @endif">
                                            {{ ucfirst($task->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($task->status==='completed') bg-green-100 text-green-800
                                            @elseif($task->status==='pending') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst(str_replace('_',' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $task->assignedTo->name ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($task->due_date)->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('housekeeping-tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        @if($task->status !== 'completed' && auth()->check() && (auth()->user()->role === 'admin' || auth()->id() === $task->assigned_to))
                                            <form action="{{ route('housekeeping-tasks.complete', $task) }}" method="POST" class="inline" onsubmit="return confirm('Mark this task as done?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-green-600 text-white text-xs hover:bg-green-700">Complete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No tasks found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $tasks->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
