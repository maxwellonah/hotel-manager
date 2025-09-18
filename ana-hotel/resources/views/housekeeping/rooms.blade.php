@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Housekeeping - Room Status</h2>
                    <div class="space-x-2">
                        <a href="{{ route('housekeeping.rooms') }}" class="px-3 py-1.5 rounded text-sm bg-gray-100 text-gray-700">All</a>
                        <a href="{{ route('housekeeping.rooms', ['status' => 'available']) }}" class="px-3 py-1.5 rounded text-sm bg-green-100 text-green-800">Available</a>
                        <a href="{{ route('housekeeping.rooms', ['status' => 'occupied']) }}" class="px-3 py-1.5 rounded text-sm bg-yellow-100 text-yellow-800">Occupied</a>
                        <a href="{{ route('housekeeping.rooms', ['status' => 'maintenance']) }}" class="px-3 py-1.5 rounded text-sm bg-red-100 text-red-800">Maintenance</a>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Bookings</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recent Task</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($rooms as $room)
                                @php $recent = optional($recentTasks->get($room->id))[0] ?? null; @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->room_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->roomType->name ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($room->status==='available') bg-green-100 text-green-800
                                            @elseif($room->status==='occupied') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($room->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->active_bookings_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($recent)
                                            <span class="font-medium">{{ ucfirst(str_replace('_',' ', $recent->task_type)) }}</span>
                                            <span class="mx-1">•</span>
                                            <span class="text-xs uppercase">{{ $recent->status }}</span>
                                            <span class="mx-1">•</span>
                                            <span class="text-xs text-gray-500">{{ $recent->updated_at->diffForHumans() }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No rooms found.</td>
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
