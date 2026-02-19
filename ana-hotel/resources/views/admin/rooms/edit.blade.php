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
                                    required onchange="updatePriceDisplay(this.value)">
                                <option value="">Select a room type</option>
                                @foreach($roomTypes as $type)
                                    <option value="{{ $type->id }}" data-price="{{ $type->price_per_night }}" data-name="{{ $type->name }}" {{ (old('room_type_id', $room->room_type_id) == $type->id) ? 'selected' : '' }}>
                                        {{ $type->name }} ({{ $type->capacity }} guests) - ${{ number_format($type->price_per_night, 2) }}/night
                                    </option>
                                @endforeach
                            </select>
                            @error('room_type_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Price Display & Edit -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price per Night</label>
                            <div class="mt-1 flex space-x-2">
                                <div class="flex-1 p-3 bg-gray-100 border border-gray-300 rounded-md">
                                    <span class="text-lg font-semibold text-gray-900" id="priceDisplay">$0.00</span>
                                    <span class="text-sm text-gray-600">/night</span>
                                </div>
                                <button type="button" onclick="togglePriceEdit()" 
                                        class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                                    Edit
                                </button>
                            </div>
                            <div id="priceEditSection" class="mt-2 hidden">
                                <input type="number" id="priceEditInput" step="0.01" min="0" 
                                       class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Enter new price">
                                <div class="mt-2 flex space-x-2">
                                    <button type="button" onclick="savePrice()" 
                                            class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                        Save
                                    </button>
                                    <button type="button" onclick="togglePriceEdit()" 
                                            class="px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Room Type Name Edit -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Room Type Name</label>
                            <div class="mt-1 flex space-x-2">
                                <div class="flex-1 p-3 bg-gray-100 border border-gray-300 rounded-md">
                                    <span class="text-lg font-semibold text-gray-900" id="typeNameDisplay">-</span>
                                </div>
                                <button type="button" onclick="toggleNameEdit()" 
                                        class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                                    Edit
                                </button>
                            </div>
                            <div id="nameEditSection" class="mt-2 hidden">
                                <input type="text" id="nameEditInput" 
                                       class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Enter new room type name">
                                <div class="mt-2 flex space-x-2">
                                    <button type="button" onclick="saveName()" 
                                            class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                        Save
                                    </button>
                                    <button type="button" onclick="toggleNameEdit()" 
                                            class="px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </div>
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
                        <a href="{{ route('admin.rooms.index') }}" 
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

@push('scripts')
<script>
let currentRoomTypeId = null;

function updatePriceDisplay(roomTypeId) {
    const roomTypes = @json($roomTypes);
    const selectedType = roomTypes.find(type => type.id == roomTypeId);
    
    currentRoomTypeId = roomTypeId;
    
    if (selectedType) {
        document.getElementById('priceDisplay').textContent = `$${parseFloat(selectedType.price_per_night).toFixed(2)}`;
        document.getElementById('typeNameDisplay').textContent = selectedType.name;
        document.getElementById('priceEditInput').value = selectedType.price_per_night;
        document.getElementById('nameEditInput').value = selectedType.name;
    } else {
        document.getElementById('priceDisplay').textContent = '$0.00';
        document.getElementById('typeNameDisplay').textContent = '-';
        document.getElementById('priceEditInput').value = '';
        document.getElementById('nameEditInput').value = '';
    }
}

function togglePriceEdit() {
    const editSection = document.getElementById('priceEditSection');
    editSection.classList.toggle('hidden');
    
    if (!editSection.classList.contains('hidden')) {
        document.getElementById('priceEditInput').focus();
    }
}

function toggleNameEdit() {
    const editSection = document.getElementById('nameEditSection');
    editSection.classList.toggle('hidden');
    
    if (!editSection.classList.contains('hidden')) {
        document.getElementById('nameEditInput').focus();
    }
}

async function savePrice() {
    if (!currentRoomTypeId) {
        alert('Please select a room type first');
        return;
    }
    
    const newPrice = document.getElementById('priceEditInput').value;
    
    if (!newPrice || newPrice <= 0) {
        alert('Please enter a valid price');
        return;
    }
    
    try {
        const response = await fetch(`/admin/room-types/${currentRoomTypeId}/update-price`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ price: newPrice })
        });
        
        if (response.ok) {
            // Update the display
            document.getElementById('priceDisplay').textContent = `$${parseFloat(newPrice).toFixed(2)}`;
            
            // Update the dropdown option
            const option = document.querySelector(`option[value="${currentRoomTypeId}"]`);
            if (option) {
                option.setAttribute('data-price', newPrice);
                const roomTypes = @json($roomTypes);
                const roomType = roomTypes.find(type => type.id == currentRoomTypeId);
                if (roomType) {
                    roomType.price_per_night = newPrice;
                    option.textContent = `${roomType.name} (${roomType.capacity} guests) - $${parseFloat(newPrice).toFixed(2)}/night`;
                }
            }
            
            togglePriceEdit();
            alert('Price updated successfully!');
        } else {
            alert('Failed to update price');
        }
    } catch (error) {
        console.error('Error updating price:', error);
        alert('Error updating price');
    }
}

async function saveName() {
    if (!currentRoomTypeId) {
        alert('Please select a room type first');
        return;
    }
    
    const newName = document.getElementById('nameEditInput').value.trim();
    
    if (!newName) {
        alert('Please enter a valid room type name');
        return;
    }
    
    try {
        const response = await fetch(`/admin/room-types/${currentRoomTypeId}/update-name`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ name: newName })
        });
        
        if (response.ok) {
            // Update the display
            document.getElementById('typeNameDisplay').textContent = newName;
            
            // Update the dropdown option
            const option = document.querySelector(`option[value="${currentRoomTypeId}"]`);
            if (option) {
                option.setAttribute('data-name', newName);
                const roomTypes = @json($roomTypes);
                const roomType = roomTypes.find(type => type.id == currentRoomTypeId);
                if (roomType) {
                    roomType.name = newName;
                    option.textContent = `${newName} (${roomType.capacity} guests) - $${parseFloat(roomType.price_per_night).toFixed(2)}/night`;
                }
            }
            
            toggleNameEdit();
            alert('Room type name updated successfully!');
        } else {
            alert('Failed to update room type name');
        }
    } catch (error) {
        console.error('Error updating name:', error);
        alert('Error updating room type name');
    }
}

// Set initial price display for current room type
document.addEventListener('DOMContentLoaded', function() {
    const currentRoomTypeId = '{{ $room->room_type_id }}';
    if (currentRoomTypeId) {
        updatePriceDisplay(currentRoomTypeId);
    }
});
</script>
@endpush
@endsection
