@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Guest Management (Reception)</h2>
                    <div class="space-x-2">
                        <a href="{{ route('reception.guests.index', ['filter' => 'payable']) }}" class="px-3 py-1.5 rounded text-sm {{ ($filter ?? 'payable') === 'payable' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">Payable</a>
                        <a href="{{ route('reception.guests.index', ['filter' => 'paid']) }}" class="px-3 py-1.5 rounded text-sm {{ ($filter ?? '') === 'paid' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">Paid</a>
                        <a href="{{ route('reception.guests.index', ['filter' => 'all']) }}" class="px-3 py-1.5 rounded text-sm {{ ($filter ?? '') === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">All</a>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($bookings as $booking)
                                @php
                                    $hasCompletablePayment = $booking->payments && $booking->payments->contains(function ($p) {
                                        return in_array($p->status, [\App\Models\Payment::STATUS_COMPLETED, \App\Models\Payment::STATUS_PENDING]);
                                    });
                                    $canAccept = $hasCompletablePayment && $booking->status !== 'checked_in' && ($booking->payment_status !== 'paid');
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $booking->user->name ?? 'Guest' }}
                                        <div class="text-xs text-gray-500">{{ $booking->user->email ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->booking_reference }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($booking->room)
                                            {{ $booking->room->roomType->name ?? 'Room' }} #{{ $booking->room->id }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ optional($booking->check_in)->format('Y-m-d') }} → {{ optional($booking->check_out)->format('Y-m-d') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ in_array($booking->status, ['confirmed','checked_in']) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst(str_replace('_',' ', $booking->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $booking->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst(str_replace('_',' ', $booking->payment_status ?? 'pending')) }}
                                            </span>
                                            @if($booking->payment_confirmed_at)
                                                <span class="text-xs text-gray-500">on {{ optional($booking->payment_confirmed_at)->format('Y-m-d H:i') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($canAccept)
                                            <form action="{{ route('bookings.accept-payment', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Confirm/Accept payment for this booking and record today as paid date?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-indigo-600 text-white text-xs hover:bg-indigo-700">Accept Payment</button>
                                            </form>
                                        @else
                                            @php $noPayments = !$booking->payments || $booking->payments->isEmpty(); @endphp
                                            @if($noPayments && auth()->check() && auth()->user()->role === 'admin' && $booking->status !== 'checked_in' && ($booking->payment_status !== 'paid'))
                                                <form action="{{ route('admin.bookings.create-pending-payment', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Create a pending payment record for this booking?');">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-yellow-600 text-white text-xs hover:bg-yellow-700">Create Pending Payment</button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No bookings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $bookings->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
