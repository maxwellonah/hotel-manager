@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-6">Receptionist Dashboard</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Quick Stats -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Today's Check-ins</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ \App\Models\Booking::whereDate('check_in', today())->where('status', 'confirmed')->count() }}</p>
                    </div>
                    
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Today's Check-outs</h3>
                        <p class="text-3xl font-bold text-green-600">{{ \App\Models\Booking::whereDate('check_out', today())->where('status', 'confirmed')->count() }}</p>
                    </div>
                    
                    <div class="bg-yellow-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Available Rooms</h3>
                        <p class="text-3xl font-bold text-yellow-600">{{ \App\Models\Room::where('status', 'available')->count() }}</p>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="mt-8">
                    <h3 class="text-xl font-semibold mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="{{ route('bookings.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded text-center transition duration-300">
                            New Booking
                        </a>
                        <a href="{{ route('check-in.index') }}" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded text-center transition duration-300">
                            Check-in Guest
                        </a>
                        <a href="{{ route('check-out.index') }}" class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded text-center transition duration-300">
                            Check-out Guest
                        </a>
                    </div>
                </div>
                
                <!-- Today's Arrivals -->
                <div class="mt-8">
                    <h3 class="text-xl font-semibold mb-4">Today's Arrivals</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        @php
                            $todayArrivals = \App\Models\Booking::with('room', 'user')
                                ->whereDate('check_in', today())
                                ->where('status', 'confirmed')
                                ->orderBy('check_in')
                                ->get();
                        @endphp
                        
                        @if($todayArrivals->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($todayArrivals as $booking)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $booking->user->email }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $booking->room->room_number }} ({{ $booking->room->roomType->name }})</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $booking->check_in->format('M d, Y') }}</div>
                                                    <div class="text-sm text-gray-500">{{ $booking->check_out->format('M d, Y') }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ ucfirst($booking->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('bookings.show', $booking) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                                    <a href="{{ route('check-in.process', $booking) }}" class="text-green-600 hover:text-green-900">Check-in</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500">No arrivals scheduled for today.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
