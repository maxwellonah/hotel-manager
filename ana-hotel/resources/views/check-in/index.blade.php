@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Guest Management</h2>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">
                        &larr; Back to Dashboard
                    </a>
                </div>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Pending Check-ins Section -->
                <div class="mb-12">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 rounded-t-lg">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Pending Check-ins for {{ now()->format('F j, Y') }}
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            List of guests expected to check in today.
                        </p>
                    </div>

                    <div class="bg-white shadow overflow-hidden sm:rounded-b-lg">
                        @if($pendingCheckIns->isEmpty())
                            <div class="px-4 py-5 sm:px-6">
                                <p class="text-gray-500">No pending check-ins for today.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Guest
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Room
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Check-in
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Check-out
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($pendingCheckIns as $booking)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $booking->user->name }}
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                {{ $booking->user->email }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $booking->room->room_number }} ({{ $booking->room->roomType->name }})
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $booking->room->roomType->capacity }} guests
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ \Carbon\Carbon::parse($booking->check_in)->format('M j, Y') }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ \Carbon\Carbon::parse($booking->check_in)->format('g:i A') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ \Carbon\Carbon::parse($booking->check_out)->format('M j, Y') }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ \Carbon\Carbon::parse($booking->check_out)->format('g:i A') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                    <a href="{{ route('check-in.process', $booking) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        Check-in
                                                    </a>
                                                    <a href="{{ route('bookings.show', $booking) }}" class="text-gray-600 hover:text-gray-900">
                                                        View
                                                    </a>
                                                    <form action="{{ route('check-in.cancel', $booking) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" 
                                                            onclick="return confirm('Are you sure you want to cancel this check-in? This action cannot be undone.')"
                                                            class="text-red-600 hover:text-red-900 focus:outline-none"
                                                            title="Cancel Check-in">
                                                            Cancel
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="px-4 py-3 bg-gray-50 sm:px-6">
                                {{ $pendingCheckIns->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Checked-in Guests Section -->
                <div>
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 rounded-t-lg">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Currently Checked-in Guests
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            List of guests currently staying at the hotel.
                        </p>
                    </div>

                    <div class="bg-white shadow overflow-hidden sm:rounded-b-lg">
                        @if($checkedInGuests->isEmpty())
                            <div class="px-4 py-5 sm:px-6">
                                <p class="text-gray-500">No guests are currently checked in.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Guest
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Room
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Checked In
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Check-out
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($checkedInGuests as $booking)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $booking->user->name }}
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                {{ $booking->user->email }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $booking->room->room_number }} ({{ $booking->room->roomType->name }})
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $booking->room->roomType->capacity }} guests
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ \Carbon\Carbon::parse($booking->checked_in_at)->format('M j, Y') }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ \Carbon\Carbon::parse($booking->checked_in_at)->format('g:i A') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ \Carbon\Carbon::parse($booking->check_out)->format('M j, Y') }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ \Carbon\Carbon::parse($booking->check_out)->format('g:i A') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                    <a href="{{ route('check-in.extend', $booking) }}" class="text-blue-600 hover:text-blue-900" title="Extend Stay">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                        Extend
                                                    </a>
                                                    <a href="{{ route('check-out.process', $booking) }}" class="text-yellow-600 hover:text-yellow-900" title="Check-out">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                        </svg>
                                                        Check-out
                                                    </a>
                                                    <a href="{{ route('bookings.show', $booking) }}" class="text-gray-600 hover:text-gray-900" title="View Details">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
