@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-6">Housekeeping Dashboard</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Task Stats -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Assigned Tasks</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ \App\Models\HousekeepingTask::where('assigned_to', auth()->id())->where('status', '!=', 'completed')->count() }}</p>
                    </div>
                    
                    <div class="bg-yellow-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Pending Tasks</h3>
                        <p class="text-3xl font-bold text-yellow-600">{{ \App\Models\HousekeepingTask::where('assigned_to', auth()->id())->where('status', 'pending')->count() }}</p>
                    </div>
                    
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Completed Today</h3>
                        <p class="text-3xl font-bold text-green-600">{{ \App\Models\HousekeepingTask::where('assigned_to', auth()->id())->where('status', 'completed')->whereDate('completed_at', today())->count() }}</p>
                    </div>
                </div>
                
                <!-- Your Tasks -->
                <div class="mt-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Your Tasks</h3>
                        <a href="{{ route('housekeeping-tasks.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">View All</a>
                    </div>
                    
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul class="divide-y divide-gray-200">
                            @php
                                $tasks = \App\Models\HousekeepingTask::with('room', 'assignedTo')
                                    ->where('assigned_to', auth()->id())
                                    ->where('status', '!=', 'completed')
                                    ->orderBy('priority', 'desc')
                                    ->orderBy('created_at', 'asc')
                                    ->take(5)
                                    ->get();
                            @endphp
                            
                            @forelse($tasks as $task)
                                <li>
                                    <a href="{{ route('housekeeping-tasks.show', $task) }}" class="block hover:bg-gray-50">
                                        <div class="px-4 py-4 sm:px-6">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-indigo-600 truncate">
                                                    {{ $task->task_type === 'cleaning' ? 'Room Cleaning' : ucfirst($task->task_type) }} - Room {{ $task->room->room_number }}
                                                </p>
                                                <div class="ml-2 flex-shrink-0 flex">
                                                    @if($task->priority === 'high')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                            High Priority
                                                        </span>
                                                    @elseif($task->priority === 'medium')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            Medium Priority
                                                        </span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Low Priority
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="mt-2 sm:flex sm:justify-between">
                                                <div class="sm:flex">
                                                    <p class="flex items-center text-sm text-gray-500">
                                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                        </svg>
                                                        {{ $task->created_at->diffForHumans() }}
                                                    </p>
                                                </div>
                                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                    </svg>
                                                    {{ $task->assignedTo->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="p-4 text-center text-gray-500">
                                    No tasks assigned to you at the moment.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="mt-8">
                    <h3 class="text-xl font-semibold mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('housekeeping-tasks.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded text-center transition duration-300">
                            New Task
                        </a>
                        <a href="{{ route('housekeeping-tasks.index') }}?status=pending" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded text-center transition duration-300">
                            View Pending Tasks
                        </a>
                        <a href="{{ route('housekeeping-tasks.index') }}?status=completed" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded text-center transition duration-300">
                            View Completed Tasks
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
