@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Edit Room: {{ $room->room_number }}</h2>
                    <a href="{{ route('admin.rooms.index') }}" class="text-gray-600 hover:text-gray-900">
                        &larr; Back to Rooms
                    </a>
                </div>

                <form action="{{ route('admin.rooms.update', $room) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Room Number -->
                        <div>
                            <label for="room_number" class="block text-sm font-medium text-gray-700">Room Number *</label>
                            <input type="text" name="room_number" id="room_number" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('room_number', $room->room_number) }}" required>
                            @error('room_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Room Type -->
                        <div>
                            <label for="room_type_id" class="block text-sm font-medium text-gray-700">Room Type *</label>
                            <select name="room_type_id" id="room_type_id" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                                <option value="">Select a room type</option>
                                @foreach($roomTypes as $type)
                                    <option value="{{ $type->id }}" {{ (old('room_type_id', $room->room_type_id) == $type->id) ? 'selected' : '' }}>
                                        {{ $type->name }} ({{ $type->capacity }} guests)
                                    </option>
                                @endforeach
                            </select>
                            @error('room_type_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Assigned Housekeeper -->
                        <div>
                            <label for="housekeeping_user_id" class="block text-sm font-medium text-gray-700">Assigned Housekeeper</label>
                            <select name="housekeeping_user_id" id="housekeeping_user_id" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Unassigned</option>
                                @foreach($housekeepers as $hk)
                                    <option value="{{ $hk->id }}" {{ (string)old('housekeeping_user_id', (string)$room->housekeeping_user_id) === (string)$hk->id ? 'selected' : '' }}>
                                        {{ $hk->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('housekeeping_user_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Floor -->
                        <div>
                            <label for="floor" class="block text-sm font-medium text-gray-700">Floor *</label>
                            <input type="number" name="floor" id="floor" min="1" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('floor', $room->floor) }}" required>
                            @error('floor')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                            <select name="status" id="status" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                                <option value="available" {{ (old('status', $room->status) == 'available') ? 'selected' : '' }}>Available</option>
                                <option value="occupied" {{ (old('status', $room->status) == 'occupied') ? 'selected' : '' }}>Occupied</option>
                                <option value="maintenance" {{ (old('status', $room->status) == 'maintenance') ? 'selected' : '' }}>Maintenance</option>
                                <option value="cleaning" {{ (old('status', $room->status) == 'cleaning') ? 'selected' : '' }}>Cleaning</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Current Image -->
                        @if($room->image_path)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Current Image</label>
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $room->image_path) }}" alt="Room {{ $room->room_number }}" class="h-48 w-auto rounded">
                                </div>
                            </div>
                        @endif

                        <!-- Image Upload -->
                        <div class="md:col-span-2">
                            <label for="image" class="block text-sm font-medium text-gray-700">
                                {{ $room->image_path ? 'Change Room Image' : 'Room Image' }}
                            </label>
                            <div class="mt-1 flex items-center">
                                <input type="file" name="image" id="image" 
                                       class="p-2 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer focus:outline-none"
                                       accept="image/*">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Upload a new image (max 2MB). Leave empty to keep the current image.</p>
                            @error('image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('rooms.index') }}" 
                           class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Update Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
