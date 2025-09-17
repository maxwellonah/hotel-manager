@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Room Details: {{ $room->room_number }}</h2>
                    <div class="space-x-2">
                        <a href="{{ route('rooms.edit', $room) }}" 
                           class="text-indigo-600 hover:text-indigo-900">
                            Edit
                        </a>
                        <a href="{{ route('rooms.index') }}" class="text-gray-600 hover:text-gray-900">
                            Back to List
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Room Image -->
                    <div>
                        @if($room->image_path)
                            <img src="{{ asset('storage/' . $room->image_path) }}" 
                                 alt="Room {{ $room->room_number }}" 
                                 class="w-full h-auto rounded-lg shadow-md">
                        @else
                            <div class="bg-gray-100 rounded-lg flex items-center justify-center" style="height: 250px;">
                                <span class="text-gray-400">No image available</span>
                            </div>
                        @endif
                    </div>

                    <!-- Room Details -->
                    <div>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ $room->roomType->name }}</h3>
                                <p class="text-gray-500">Room {{ $room->room_number }} • Floor {{ $room->floor }}</p>
                            </div>

                            <div class="pt-4 border-t border-gray-200">
                                <h4 class="font-medium text-gray-900">Room Status</h4>
                                @php
                                    $statusColors = [
                                        'available' => 'bg-green-100 text-green-800',
                                        'occupied' => 'bg-red-100 text-red-800',
                                        'maintenance' => 'bg-yellow-100 text-yellow-800',
                                        'cleaning' => 'bg-blue-100 text-blue-800',
                                    ];
                                    $statusColor = $statusColors[$room->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                                    {{ ucfirst($room->status) }}
                                </span>
                            </div>

                            <div class="pt-4 border-t border-gray-200">
                                <h4 class="font-medium text-gray-900">Room Type Details</h4>
                                <ul class="mt-2 space-y-1 text-sm text-gray-600">
                                    <li>• Capacity: {{ $room->roomType->capacity }} guests</li>
                                    <li>• Price per night: ${{ number_format($room->roomType->price_per_night, 2) }}</li>
                                    @if($room->roomType->description)
                                        <li>• {{ $room->roomType->description }}</li>
                                    @endif
                                </ul>
                            </div>

                            @if($room->roomType->amenities->count() > 0)
                                <div class="pt-4 border-t border-gray-200">
                                    <h4 class="font-medium text-gray-900">Amenities</h4>
                                    <ul class="mt-2 grid grid-cols-2 gap-2 text-sm text-gray-600">
                                        @foreach($room->roomType->amenities as $amenity)
                                            <li class="flex items-center">
                                                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                {{ $amenity->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-200 flex space-x-3">
                            <a href="{{ route('rooms.edit', $room) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Edit Room
                            </a>
                            <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        onclick="return confirm('Are you sure you want to delete this room? This action cannot be undone.')">
                                    Delete Room
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
