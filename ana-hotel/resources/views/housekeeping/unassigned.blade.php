@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Unassigned Rooms</h2>
                    <a href="{{ route('housekeeping.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Dashboard</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assign To</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($rooms as $room)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->room_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->roomType->name ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <form action="{{ route('rooms.update', $room) }}" method="POST" class="flex items-center space-x-2">
                                            @csrf
                                            @method('PUT')
                                            <select name="housekeeping_user_id" class="border-gray-300 rounded-md shadow-sm">
                                                <option value="">Unassigned</option>
                                                @foreach($housekeepers as $hk)
                                                    <option value="{{ $hk->id }}">{{ $hk->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="room_number" value="{{ $room->room_number }}">
                                            <input type="hidden" name="room_type_id" value="{{ $room->room_type_id }}">
                                            <input type="hidden" name="floor" value="{{ $room->floor }}">
                                            <input type="hidden" name="status" value="{{ $room->status }}">
                                            <button type="submit" class="px-3 py-1.5 rounded bg-indigo-600 text-white text-xs hover:bg-indigo-700">Assign</button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                        <a href="{{ route('rooms.edit', $room) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">All rooms are assigned.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $rooms->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
