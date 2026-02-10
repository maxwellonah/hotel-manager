@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Bookings Report</h2>
                        <p class="mt-1 text-sm text-gray-500">Comprehensive booking analytics and trends</p>
                    </div>
                    <a href="{{ route('admin.reports') }}" class="text-gray-600 hover:text-gray-900">
                        &larr; Back to Reports
                    </a>
                </div>

                <!-- Filters -->
                <div class="bg-gray-50 px-4 py-5 sm:px-6 rounded-lg mb-6">
                    <form id="filterForm" method="GET" action="{{ route('admin.reports.bookings') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" id="start_date" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   value="{{ $startDate }}">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" id="end_date" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   value="{{ $endDate }}">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">All Statuses</option>
                                @foreach(['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'] as $status)
                                    <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="room_type" class="block text-sm font-medium text-gray-700">Room Type</label>
                            <select name="room_type" id="room_type" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">All Room Types</option>
                                @foreach($roomTypes as $roomType)
                                    <option value="{{ $roomType->id }}" {{ $selectedRoomType == $roomType->id ? 'selected' : '' }}>
                                        {{ $roomType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end space-x-2 md:col-span-4">
                            <button type="submit" 
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Apply Filters
                            </button>
                            <a href="{{ route('admin.reports.bookings') }}" class="text-gray-600 hover:text-gray-800 px-4 py-2">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Bookings -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002 2M9 5a2 2 0 012-2h2a2 2 0 012-2" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Total Bookings
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ number_format($totalBookings) }}
                                </div>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Total Revenue
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    ${{ number_format($totalRevenue, 2) }}
                                </div>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Average Stay -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Avg. Stay Duration
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ number_format($averageStay, 1) }} nights
                                </div>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Average Daily Rate -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0 0v8m0-8v8m0 0h-8m-8 0H5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002 2m0 0V9a2 2 0 00-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Avg. Daily Rate
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    ${{ number_format($averageDailyRate, 2) }}
                                </div>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Occupancy Rate -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002 2m0 0V9a2 2 0 012-2h2a2 2 0 012-2m0 0V5a2 2 0 012-2h2a2 2 0 012-2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Occupancy Rate
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ number_format($occupancyRate, 1) }}%
                                </div>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Bookings by Status Chart -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bookings by Status</h3>
                    <div class="relative h-64">
                        <canvas id="bookingsByStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Room Type Distribution -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Room Type Distribution</h3>
                    <div class="relative h-64">
                        <canvas id="roomTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trends -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Booking Trends</h3>
                <div class="relative h-80">
                    <canvas id="monthlyTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Trends Over Time -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Status Trends Over Time</h3>
                <div class="relative h-80">
                    <canvas id="statusTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Bookings</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Guest
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Room
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Check-in
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Check-out
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Revenue
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($bookings as $booking)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-xs font-medium leading-none text-gray-600">
                                                {{ strtoupper(substr($booking->user->name ?? 'Unknown', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $booking->user->name ?? 'Unknown' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $booking->user->email ?? 'No email' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $booking->room->roomType->name ?? 'Unknown' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Room {{ $booking->room->number ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $booking->check_in->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $booking->check_out->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($booking->status == 'confirmed') bg-blue-100 text-blue-800 @elseif($booking->status == 'checked_in') bg-green-100 text-green-800 @elseif($booking->status == 'checked_out') bg-purple-100 text-purple-800 @elseif($booking->status == 'cancelled') bg-red-100 text-red-800 @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ number_format($booking->total_price, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No bookings found for the selected criteria
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($bookings->hasPages())
                <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart colors
        const chartColors = {
            confirmed: 'rgb(59, 130, 246)',
            checked_in: 'rgb(34, 197, 94)',
            checked_out: 'rgb(168, 85, 247)',
            cancelled: 'rgb(239, 68, 68)',
            pending: 'rgb(245, 158, 11)',
            primary: 'rgb(99, 102, 241)',
            success: 'rgb(34, 197, 94)',
            warning: 'rgb(245, 158, 11)',
            danger: 'rgb(239, 68, 68)',
            info: 'rgb(59, 130, 246)'
        };

        // Bookings by Status Chart
        const statusCtx = document.getElementById('bookingsByStatusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: @json(isset($statusCounts) && $statusCounts->isNotEmpty() ? collect($statusCounts)->keys()->map(fn($key) => ucfirst($key))->toArray() : []),
                datasets: [{
                    data: @json(isset($statusCounts) && $statusCounts->isNotEmpty() ? collect($statusCounts)->pluck('count')->toArray() : [0]),
                    backgroundColor: [
                        chartColors.confirmed,
                        chartColors.checked_in,
                        chartColors.checked_out,
                        chartColors.pending,
                        chartColors.cancelled
                    ],
                    borderWidth: 1,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                if (total === 0) return `${label}: ${value} (0%)`;
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Room Type Chart
        const roomTypeCtx = document.getElementById('roomTypeChart').getContext('2d');
        const roomTypeChart = new Chart(roomTypeCtx, {
            type: 'bar',
            data: {
                labels: @json(isset($roomTypeBookings) && $roomTypeBookings->isNotEmpty() ? collect($roomTypeBookings)->keys()->toArray() : []),
                datasets: [{
                    label: 'Bookings',
                    data: @json(isset($roomTypeBookings) && $roomTypeBookings->isNotEmpty() ? collect($roomTypeBookings)->pluck('count')->toArray() : []),
                    backgroundColor: chartColors.primary,
                    borderColor: chartColors.primary,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Monthly Trends Chart
        const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: @json(isset($monthlyBookings) && $monthlyBookings->isNotEmpty() ? collect($monthlyBookings)->pluck('month')->toArray() : []),
                datasets: [{
                    label: 'Bookings',
                    data: @json(isset($monthlyBookings) && $monthlyBookings->isNotEmpty() ? collect($monthlyBookings)->pluck('count')->toArray() : []),
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary.replace('rgb', 'rgba').replace(')', '0.1)'),
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Revenue',
                    data: @json(isset($monthlyBookings) && $monthlyBookings->isNotEmpty() ? collect($monthlyBookings)->pluck('revenue')->toArray() : []),
                    borderColor: chartColors.success,
                    backgroundColor: chartColors.success.replace('rgb', 'rgba').replace(')', '0.1)'),
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Status Trends Chart
        const statusTrendsCtx = document.getElementById('statusTrendsChart').getContext('2d');
        const statusTrendsChart = new Chart(statusTrendsCtx, {
            type: 'line',
            data: {
                labels: @json(isset($monthlyStatusTrends) && !empty($monthlyStatusTrends) ? array_keys($monthlyStatusTrends['confirmed'] ?? []) : []),
                datasets: [
                    {
                        label: 'Confirmed',
                        data: @json(isset($monthlyStatusTrends['confirmed']) ? array_values(array_map(function($data) { return $data['count']; }, $monthlyStatusTrends['confirmed'] ?? [])),
                        borderColor: chartColors.confirmed,
                        backgroundColor: chartColors.confirmed.replace('rgb', 'rgba').replace(')', '0.1)'),
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Checked In',
                        data: @json(isset($monthlyStatusTrends['checked_in']) ? array_values(array_map(function($data) { return $data['count']; }, $monthlyStatusTrends['checked_in'] ?? [])),
                        borderColor: chartColors.checked_in,
                        backgroundColor: chartColors.checked_in.replace('rgb', 'rgba').replace(')', '0.1)'),
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Checked Out',
                        data: @json(isset($monthlyStatusTrends['checked_out']) ? array_values(array_map(function($data) { return $data['count']; }, $monthlyStatusTrends['checked_out'] ?? [])),
                        borderColor: chartColors.checked_out,
                        backgroundColor: chartColors.checked_out.replace('rgb', 'rgba').replace(')', '0.1)'),
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Cancelled',
                        data: @json(isset($monthlyStatusTrends['cancelled']) ? array_values(array_map(function($data) { return $data['count']; }, $monthlyStatusTrends['cancelled'] ?? [])),
                        borderColor: chartColors.cancelled,
                        backgroundColor: chartColors.cancelled.replace('rgb', 'rgba').replace(')', '0.1)'),
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
