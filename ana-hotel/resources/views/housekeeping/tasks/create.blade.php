@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-6">Create Housekeeping Task</h2>

                @if ($errors->any())
                    <div class="mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('housekeeping-tasks.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="room_id" class="block text-sm font-medium text-gray-700">Room</label>
                        <select name="room_id" id="room_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Select a room</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">Room {{ $room->room_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assign To</label>
                        <select name="assigned_to" id="assigned_to" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Select staff</option>
                            @foreach($staff as $person)
                                <option value="{{ $person->id }}">{{ $person->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="task_type" class="block text-sm font-medium text-gray-700">Task Type</label>
                        <select name="task_type" id="task_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="cleaning">Room Cleaning</option>
                            <option value="inspection">Room Inspection</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="deep_cleaning">Deep Cleaning</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                        <select name="priority" id="priority" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                        <input type="datetime-local" name="due_date" id="due_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Optional details"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <a href="{{ route('housekeeping-tasks.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded mr-2">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Create Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
