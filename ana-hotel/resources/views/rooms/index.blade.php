@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Available Rooms') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Search Filters -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
            <div class="p-6 bg-white border-b border-gray-200">
                <form action="{{ route('rooms.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="check_in" class="block text-sm font-medium text-gray-700">Check-in</label>
                            <input type="date" name="check_in" id="check_in" 
                                   value="{{ request('check_in', \Carbon\Carbon::today()->format('Y-m-d')) }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="check_out" class="block text-sm font-medium text-gray-700">Check-out</label>
                            <input type="date" name="check_out" id="check_out" 
                                   value="{{ request('check_out', \Carbon\Carbon::tomorrow()->format('Y-m-d')) }}" 
                                   min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="guests" class="block text-sm font-medium text-gray-700">Guests</label>
                            <select name="guests" id="guests" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ request('guests', 1) == $i ? 'selected' : '' }}>{{ $i }} {{ $i === 1 ? 'Guest' : 'Guests' }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                                Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Room Types -->
        @if($roomTypes->isEmpty())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg text-center p-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">No rooms available</h3>
                <p class="mt-1 text-sm text-gray-500">There are no rooms available for the selected dates. Please try different dates.</p>
            </div>
        @else
            <div class="space-y-8">
                @foreach($roomTypes as $roomType)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="md:flex">
                                <div class="md:w-1/3">
                                    @if($roomType->photos->isNotEmpty())
                                        <img src="{{ asset('storage/' . $roomType->photos->first()->path) }}" 
                                             alt="{{ $roomType->name }}" 
                                             class="w-full h-48 object-cover rounded-lg">
                                    @else
                                        <div class="w-full h-48 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400">No image available</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="md:ml-6 md:w-2/3">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900">{{ $roomType->name }}</h3>
                                            <p class="mt-1 text-gray-600">{{ $roomType->description }}</p>
                                            
                                            <!-- Amenities -->
                                            @if($roomType->amenities->isNotEmpty())
                                                <div class="mt-3">
                                                    <p class="text-sm font-medium text-gray-700">Amenities:</p>
                                                    <div class="mt-1 flex flex-wrap gap-2">
                                                        @foreach($roomType->amenities->take(5) as $amenity)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                {{ $amenity->name }}
                                                            </span>
                                                        @endforeach
                                                        @if($roomType->amenities->count() > 5)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                +{{ $roomType->amenities->count() - 5 }} more
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <p class="text-2xl font-bold text-blue-600">${{ number_format($roomType->price_per_night, 2) }}</p>
                                            <p class="text-sm text-gray-500">per night</p>
                                            <p class="text-sm text-gray-500">{{ $roomType->capacity }} {{ Str::plural('guest', $roomType->capacity) }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6 flex justify-end">
                                        <a href="{{ route('bookings.create', [
                                            'room_type_id' => $roomType->id,
                                            'check_in' => request('check_in'),
                                            'check_out' => request('check_out'),
                                            'guests' => request('guests', 1)
                                        ]) }}" 
                                           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition duration-300">
                                            Book Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Set minimum check-out date to be after check-in date
    document.getElementById('check_in').addEventListener('change', function() {
        const checkInDate = new Date(this.value);
        const checkOutInput = document.getElementById('check_out');
        const nextDay = new Date(checkInDate);
        nextDay.setDate(checkInDate.getDate() + 1);
        
        // Format date as YYYY-MM-DD
        const nextDayFormatted = nextDay.toISOString().split('T')[0];
        checkOutInput.min = nextDayFormatted;
        
        // If current check-out is before new check-in, update it
        if (new Date(checkOutInput.value) <= checkInDate) {
            checkOutInput.value = nextDayFormatted;
        }
    });
</script>
@endpush
@endsection
