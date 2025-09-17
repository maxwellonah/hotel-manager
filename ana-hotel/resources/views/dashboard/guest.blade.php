@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-6">Welcome, {{ auth()->user()->name }}!</h2>
                
                <!-- Upcoming Stay -->
                @php
                    $upcomingStay = \App\Models\Booking::with('room.roomType')
                        ->where('user_id', auth()->id())
                        ->where('status', 'confirmed')
                        ->where('check_in', '>=', now())
                        ->orderBy('check_in')
                        ->first();
                @endphp
                
                @if($upcomingStay)
                    <div class="bg-blue-50 p-6 rounded-lg mb-8">
                        <h3 class="text-xl font-semibold mb-4">Your Upcoming Stay</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-lg font-medium text-gray-900">{{ $upcomingStay->room->roomType->name }} Room</h4>
                                <p class="text-gray-600">Room {{ $upcomingStay->room->room_number }}</p>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500">Check-in</p>
                                    <p class="font-medium">{{ $upcomingStay->check_in->format('l, F j, Y') }}</p>
                                </div>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Check-out</p>
                                    <p class="font-medium">{{ $upcomingStay->check_out->format('l, F j, Y') }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col justify-between">
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500">Total for stay</p>
                                    <p class="text-2xl font-bold">${{ number_format($upcomingStay->total_amount, 2) }}</p>
                                    <p class="text-sm text-gray-500">{{ $upcomingStay->check_in->diffInDays($upcomingStay->check_out) }} nights</p>
                                </div>
                                <div class="flex space-x-3">
                                    <a href="{{ route('bookings.show', $upcomingStay) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center transition duration-300">
                                        View Details
                                    </a>
                                    <a href="{{ route('bookings.edit', $upcomingStay) }}" class="flex-1 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded text-center transition duration-300">
                                        Modify
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Current Stay -->
                @php
                    $currentStay = \App\Models\Booking::with('room.roomType')
                        ->where('user_id', auth()->id())
                        ->where('status', 'checked_in')
                        ->where('check_in', '<=', now())
                        ->where('check_out', '>=', now())
                        ->first();
                @endphp
                
                @if($currentStay)
                    <div class="bg-green-50 p-6 rounded-lg mb-8">
                        <h3 class="text-xl font-semibold mb-4">Your Current Stay</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-lg font-medium text-gray-900">{{ $currentStay->room->roomType->name }} Room</h4>
                                <p class="text-gray-600">Room {{ $currentStay->room->room_number }}</p>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500">Check-out</p>
                                    <p class="font-medium">{{ $currentStay->check_out->format('l, F j, Y') }} at 11:00 AM</p>
                                </div>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500">Room Status</p>
                                    <div class="mt-1 flex items-center">
                                        <span class="h-2.5 w-2.5 rounded-full bg-green-500 mr-2"></span>
                                        <span class="text-sm font-medium">Checked In</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col justify-between">
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Your stay includes:</p>
                                        <ul class="mt-1 space-y-1">
                                            <li class="flex items-center">
                                                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Free Wi-Fi</span>
                                            </li>
                                            <li class="flex items-center">
                                                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Daily Housekeeping</span>
                                            </li>
                                            <li class="flex items-center">
                                                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>24/7 Room Service</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="mt-4 flex space-x-3">
                                    <a href="{{ route('room-service.create') }}" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center transition duration-300">
                                        Order Room Service
                                    </a>
                                    <a href="{{ route('support.create') }}" class="flex-1 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded text-center transition duration-300">
                                        Request Help
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                    <a href="{{ route('bookings.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white p-6 rounded-lg shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 text-white mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold">Book a Room</h3>
                                <p class="text-sm text-blue-100">Find and reserve your perfect stay</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="{{ route('profile.bookings') }}" class="bg-green-600 hover:bg-green-700 text-white p-6 rounded-lg shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 text-white mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold">My Bookings</h3>
                                <p class="text-sm text-green-100">View and manage your reservations</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="{{ route('profile.edit') }}" class="bg-purple-600 hover:bg-purple-700 text-white p-6 rounded-lg shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 text-white mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold">Profile Settings</h3>
                                <p class="text-sm text-purple-100">Update your personal information</p>
                            </div>
                        </div>
                    </a>
                </div>
                
                <!-- Special Offers -->
                @if(!$upcomingStay && !$currentStay)
                    <div class="mt-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-6 text-white">
                        <div class="flex flex-col md:flex-row items-center">
                            <div class="md:w-2/3 mb-6 md:mb-0 md:pr-6">
                                <h3 class="text-2xl font-bold mb-2">Special Offer: 20% Off Your Next Stay</h3>
                                <p class="mb-4 text-blue-100">Book now and enjoy exclusive member benefits including free breakfast and late check-out.</p>
                                <a href="{{ route('rooms.index') }}" class="inline-block bg-white text-blue-600 hover:bg-gray-100 font-bold py-2 px-6 rounded-full transition duration-300">
                                    Book Now
                                </a>
                            </div>
                            <div class="md:w-1/3">
                                <img src="{{ asset('images/special-offer.jpg') }}" alt="Special Offer" class="rounded-lg shadow-lg">
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Local Attractions -->
                <div class="mt-10">
                    <h3 class="text-xl font-semibold mb-4">Explore Local Attractions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="{{ asset('images/attraction-1.jpg') }}" alt="City Center" class="w-full h-40 object-cover">
                            <div class="p-4">
                                <h4 class="font-semibold text-lg mb-1">City Center</h4>
                                <p class="text-gray-600 text-sm mb-3">5 min walk</p>
                                <p class="text-sm text-gray-700">Explore the vibrant city center with shopping, dining, and entertainment options.</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="{{ asset('images/attraction-2.jpg') }}" alt="Beach" class="w-full h-40 object-cover">
                            <div class="p-4">
                                <h4 class="font-semibold text-lg mb-1">Beach Front</h4>
                                <p class="text-gray-600 text-sm mb-3">10 min drive</p>
                                <p class="text-sm text-gray-700">Relax on the beautiful sandy beaches just a short drive from the hotel.</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="{{ asset('images/attraction-3.jpg') }}" alt="Museum" class="w-full h-40 object-cover">
                            <div class="p-4">
                                <h4 class="font-semibold text-lg mb-1">City Museum</h4>
                                <p class="text-gray-600 text-sm mb-3">15 min walk</p>
                                <p class="text-sm text-gray-700">Discover the rich history and culture of our city at the renowned City Museum.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
