@extends('layouts.app')

@section('content')
{{-- Debug output --}}
@php
    \Log::info('Bookings view data:', [
        'totalBookings' => $totalBookings ?? 'Not set',
        'totalRevenue' => $totalRevenue ?? 'Not set',
        'averageStay' => $averageStay ?? 'Not set',
        'averageDailyRate' => $averageDailyRate ?? 'Not set',
        'statusCounts' => $statusCounts ? $statusCounts->toArray() : 'Not set',
        'roomTypeBookings' => $roomTypeBookings ? $roomTypeBookings->toArray() : 'Not set',
        'monthlyBookings' => $monthlyBookings ?? 'Not set',
        'monthlyStatusTrends' => $monthlyStatusTrends ?? 'Not set',
        'startDate' => $startDate ?? 'Not set',
        'endDate' => $endDate ?? 'Not set',
        'selectedStatus' => $selectedStatus ?? 'Not set',
        'selectedRoomType' => $selectedRoomType ?? 'Not set',
    ]);
@endphp
{{-- Debug output --}}
@php
    \Log::info('Bookings view data:', [
        'totalBookings' => $totalBookings ?? 'Not set',
        'totalRevenue' => $totalRevenue ?? 'Not set',
        'averageStay' => $averageStay ?? 'Not set',
        'averageDailyRate' => $averageDailyRate ?? 'Not set',
        'statusCounts' => $statusCounts ? $statusCounts->toArray() : 'Not set',
        'roomTypeBookings' => $roomTypeBookings ? $roomTypeBookings->toArray() : 'Not set',
        'startDate' => $startDate ?? 'Not set',
        'endDate' => $endDate ?? 'Not set'
    ]);
@endphp
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Bookings Report</h2>
                    <a href="{{ route('admin.reports.index') }}" class="text-gray-600 hover:text-gray-900">
                        &larr; Back to Reports
                    </a>
                </div>

                <!-- Filters -->
                <div class="bg-gray-50 px-4 py-5 sm:px-6 rounded-lg mb-6">
                    <form id="filterForm" method="GET" action="{{ route('admin.reports.bookings') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" id="start_date" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   value="{{ isset($startDate) ? (is_string($startDate) ? $startDate : $startDate->format('Y-m-d')) : '' }}" required>
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" id="end_date" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   value="{{ isset($endDate) ? (is_string($endDate) ? $endDate : $endDate->format('Y-m-d')) : '' }}" required>
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
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Apply Filters
                            </button>
                            <a href="{{ route('admin.reports.bookings') }}" class="text-gray-600 hover:text-gray-800 px-4 py-2">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                    <!-- Total Bookings -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
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

                <!-- Bookings by Status -->
                <div class="bg-white shadow rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Bookings by Status</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4" style="min-height: 0;">
                        <!-- Doughnut Chart -->
                        <div class="lg:col-span-1" style="height: 280px;">
                            <div class="h-full w-full">
                                <canvas id="bookingsByStatusChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Status Table -->
                        <div class="lg:col-span-1 flex flex-col" style="height: 280px;">
                            <div class="flex-1 overflow-hidden flex flex-col">
                                <div class="overflow-auto flex-1" style="min-height: 0;">
                                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Count
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Revenue
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                % of Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @php
                                            $statusColors = [
                                                'confirmed' => 'rgba(79, 70, 229, 0.7)',
                                                'checked_in' => 'rgba(16, 185, 129, 0.7)',
                                                'checked_out' => 'rgba(59, 130, 246, 0.7)',
                                                'pending' => 'rgba(245, 158, 11, 0.7)',
                                                'cancelled' => 'rgba(239, 68, 68, 0.7)'
                                            ];
                                        @endphp
                                        
                                        @foreach($statusCounts as $status => $data)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ $statusColors[$status] ?? '#9CA3AF' }}"></span>
                                                {{ ucfirst($status) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                                {{ number_format($data['count']) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                                ${{ number_format($data['revenue'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                                {{ number_format($data['percentage'], 1) }}%
                                            </td>
                                        </tr>
                                        @endforeach
                                        
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                Total
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                {{ number_format($totalBookings) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                ${{ number_format($totalRevenue, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                100.0%
                                            </td>
                                        </tr>
                                    </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Trend -->
                        <div class="lg:col-span-1 flex flex-col" style="height: 280px;">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-medium text-gray-500">Status Trend (Last 6 Months)</h4>
                                <div class="flex items-center">
                                    <span class="text-xs text-gray-500">Show:</span>
                                    <select id="statusTrendFilter" class="ml-2 text-xs border-gray-300 rounded focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="count">Count</option>
                                        <option value="revenue">Revenue</option>
                                    </select>
                                </div>
                            </div>
                            <canvas id="statusTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trends -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                    <!-- Monthly Bookings & Revenue -->
                    <div class="bg-white shadow rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Monthly Bookings & Revenue</h3>
                            <div class="flex items-center">
                                <span class="text-xs text-gray-500 mr-2">Group by:</span>
                                <select id="monthlyGrouping" class="text-xs border-gray-300 rounded focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="month">Month</option>
                                    <option value="week">Week</option>
                                </select>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="monthlyTrendsChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Room Type Performance -->
                    <div class="bg-white shadow rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Room Type Performance</h3>
                        <div class="h-64">
                            <canvas id="roomTypePerformanceChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Room Type Analysis -->
                <div class="bg-white shadow rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-medium text-gray-900">Room Type Analysis</h3>
                        <div class="flex items-center">
                            <span class="text-xs text-gray-500 mr-2">Sort by:</span>
                            <select id="roomTypeSort" class="text-xs border-gray-300 rounded focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="revenue">Revenue</option>
                                <option value="bookings">Bookings</option>
                                <option value="rate">Average Rate</option>
                                <option value="stay">Average Stay</option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto max-h-96">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Room Type
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Bookings
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Revenue
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Avg. Rate
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Avg. Stay
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        % of Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($roomTypeBookings as $roomType => $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $roomType }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                        {{ number_format($data['count']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                        ${{ number_format($data['revenue'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                        ${{ number_format($data['average_rate'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                        {{ number_format($data['average_stay'], 1) }} nights
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                        {{ $totalRevenue > 0 ? number_format(($data['revenue'] / $totalRevenue) * 100, 1) : '0.0' }}%
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Total
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        {{ number_format($totalBookings) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        ${{ number_format($totalRevenue, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        ${{ number_format($averageDailyRate, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        {{ number_format($averageStay, 1) }} nights
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        100.0%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
    // Register the plugin to all charts
    Chart.register(ChartDataLabels);
    
    // Global chart configuration
    Chart.defaults.plugins.legend.display = true;
    Chart.defaults.plugins.legend.position = 'bottom';
    Chart.defaults.plugins.legend.labels = {
        boxWidth: 12,
        padding: 10,
        font: {
            size: 10
        }
    };
    Chart.defaults.plugins.tooltip.enabled = true;
    Chart.defaults.plugins.datalabels = {
        color: '#fff',
        font: {
            weight: 'bold',
            size: 8
        },
        formatter: function(value, context) {
            return Math.round(value) + '%';
        }
    };
    Chart.defaults.plugins.tooltip.footerFont = { size: 11 };
    
    // Common colors
    const chartColors = {
        confirmed: 'rgba(79, 70, 229, 0.7)',
        checked_in: 'rgba(16, 185, 129, 0.7)',
        checked_out: 'rgba(59, 130, 246, 0.7)',
        pending: 'rgba(245, 158, 11, 0.7)',
        cancelled: 'rgba(239, 68, 68, 0.7)',
        primary: 'rgba(79, 70, 229, 0.7)',
        success: 'rgba(16, 185, 129, 0.7)',
        info: 'rgba(59, 130, 246, 0.7)',
        warning: 'rgba(245, 158, 11, 0.7)',
        danger: 'rgba(239, 68, 68, 0.7)'
    };
    
    // Format currency
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('en-US', { 
            style: 'currency', 
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    };
    
    // Format number
    const formatNumber = (value) => {
        return new Intl.NumberFormat().format(value);
    };
    
    // Format percentage
    const formatPercentage = (value) => {
        return new Intl.NumberFormat('en-US', { 
            style: 'percent',
            minimumFractionDigits: 1,
            maximumFractionDigits: 1
        }).format(value / 100);
    };
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bookings by Status Chart
        const statusCtx = document.getElementById('bookingsByStatusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(collect($statusCounts)->keys()->map(fn($key) => ucfirst($key))->toArray()) !!},
                datasets: [{
                    data: {!! json_encode(collect($statusCounts)->pluck('count')->toArray()) !!},
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
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 11
                            }
                        }
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
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            const dataArr = ctx.chart.data.datasets[0].data;
                            const sum = dataArr.reduce((a, b) => a + b, 0);
                            if (sum === 0) return '0.0%'; // Prevent division by zero
                            const percentage = (value * 100 / sum).toFixed(1) + '%';
                            return percentage;
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 11
                        }
                    }
                },
                cutout: '65%',
                radius: '90%'
            }
        });

        // Initialize Status Trend Chart
        const statusTrendCtx = document.getElementById('statusTrendChart').getContext('2d');
        const statusTrendChart = new Chart(statusTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(collect($monthlyStatusTrends['labels'] ?? [])->toArray()) !!},
                datasets: [
                    {
                        label: 'Confirmed',
                        data: {!! json_encode(collect($monthlyStatusTrends['confirmed'] ?? [])->pluck('count')->toArray()) !!},
                        borderColor: chartColors.confirmed,
                        backgroundColor: chartColors.confirmed.replace('0.7', '0.1'),
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Checked In',
                        data: {!! json_encode(collect($monthlyStatusTrends['checked_in'] ?? [])->pluck('count')->toArray()) !!},
                        borderColor: chartColors.checked_in,
                        backgroundColor: chartColors.checked_in.replace('0.7', '0.1'),
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Checked Out',
                        data: {!! json_encode(collect($monthlyStatusTrends['checked_out'] ?? [])->pluck('count')->toArray()) !!},
                        borderColor: chartColors.checked_out,
                        backgroundColor: chartColors.checked_out.replace('0.7', '0.1'),
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Cancelled',
                        data: {!! json_encode(collect($monthlyStatusTrends['cancelled'] ?? [])->pluck('count')->toArray()) !!},
                        borderColor: chartColors.cancelled,
                        backgroundColor: chartColors.cancelled.replace('0.7', '0.1'),
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Bookings'
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });

        // Toggle between count and revenue in status trend chart
        const statusTrendFilter = document.getElementById('statusTrendFilter');
        if (statusTrendFilter) {
            statusTrendFilter.addEventListener('change', function() {
                const valueType = this.value; // 'count' or 'revenue'
                const datasets = statusTrendChart.data.datasets;
                
                datasets.forEach(dataset => {
                    const status = dataset.label.toLowerCase().replace(' ', '_');
                    if (valueType === 'revenue') {
                        // Update data to show revenue
                        dataset.data = {!! json_encode(collect($monthlyStatusTrends)->map(function($trends, $status) {
                            return collect($trends)->pluck('revenue')->toArray();
                        })->toArray()) !!}[status] || [];
                        statusTrendChart.options.scales.y.title.text = 'Revenue ($)';
                        statusTrendChart.options.scales.y.ticks.callback = function(value) {
                            return '$' + formatNumber(value);
                        };
                    } else {
                        // Update data to show count
                        dataset.data = {!! json_encode(collect($monthlyStatusTrends)->map(function($trends, $status) {
                            return collect($trends)->pluck('count')->toArray();
                        })->toArray()) !!}[status] || [];
                        statusTrendChart.options.scales.y.title.text = 'Number of Bookings';
                        statusTrendChart.options.scales.y.ticks.callback = function(value) {
                            return formatNumber(value);
                        };
                    }
                });
                
                statusTrendChart.update();
            });
        }

        // Initialize Monthly Trends Chart (Bookings & Revenue)
        const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        const monthlyTrendsChart = new Chart(monthlyTrendsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyBookings->pluck('month')->toArray()) !!},
                datasets: [
                    {
                        label: 'Bookings',
                        data: {!! json_encode($monthlyBookings->pluck('count')->toArray()) !!},
                        backgroundColor: chartColors.primary.replace('0.7', '0.7'),
                        borderColor: chartColors.primary,
                        borderWidth: 1,
                        yAxisID: 'y',
                        type: 'bar',
                        order: 1
                    },
                    {
                        label: 'Revenue',
                        data: {!! json_encode($monthlyBookings->pluck('revenue')->toArray()) !!},
                        borderColor: chartColors.success,
                        backgroundColor: chartColors.success.replace('0.7', '0.1'),
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y1',
                        type: 'line',
                        order: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    if (context.dataset.label === 'Revenue') {
                                        label += '$' + formatNumber(context.parsed.y);
                                    } else {
                                        label += formatNumber(context.parsed.y);
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Number of Bookings'
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Revenue ($)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + formatNumber(value);
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });

        // Handle monthly grouping toggle
        const monthlyGrouping = document.getElementById('monthlyGrouping');
        if (monthlyGrouping) {
            monthlyGrouping.addEventListener('change', function() {
                // In a real app, you would fetch new data based on the selected grouping
                // For now, we'll just update the chart title
                const isWeekly = this.value === 'week';
                monthlyTrendsChart.options.scales.x.title.text = isWeekly ? 'Week' : 'Month';
                monthlyTrendsChart.update();
            });
        }

        // Initialize Room Type Performance Chart
        const roomTypeCtx = document.getElementById('roomTypePerformanceChart').getContext('2d');
        const roomTypeChart = new Chart(roomTypeCtx, {
            type: 'radar',
            data: {
                labels: {!! json_encode(collect($roomTypeBookings)->keys()->toArray()) !!},
                datasets: [
                    {
                        label: 'Bookings',
                        data: {!! json_encode(collect($roomTypeBookings)->pluck('count')->toArray()) !!},
                        backgroundColor: chartColors.primary.replace('0.7', '0.2'),
                        borderColor: chartColors.primary,
                        borderWidth: 2,
                        pointBackgroundColor: chartColors.primary,
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: chartColors.primary
                    },
                    {
                        label: 'Revenue',
                        data: {!! json_encode(collect($roomTypeBookings)->map(function($item) use ($totalRevenue) {
                            return ($item['revenue'] / $totalRevenue) * 100;
                        })->toArray()) !!},
                        backgroundColor: chartColors.success.replace('0.7', '0.2'),
                        borderColor: chartColors.success,
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: chartColors.success,
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: chartColors.success
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    if (context.dataset.label === 'Revenue') {
                                        label += context.parsed.toFixed(1) + '% of total';
                                    } else {
                                        label += formatNumber(context.parsed);
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        ticks: {
                            display: false,
                            stepSize: 20
                        },
                        pointLabels: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                elements: {
                    line: {
                        borderWidth: 2
                    }
                }
            }
        });

        // Handle room type sorting
        const roomTypeSort = document.getElementById('roomTypeSort');
        if (roomTypeSort) {
            roomTypeSort.addEventListener('change', function() {
                // In a real app, you would re-fetch or re-sort the data
                // For now, we'll just update the chart with the same data
                roomTypeChart.update();
            });
        }

        // Handle filter form submission
        document.addEventListener('DOMContentLoaded', function() {
            // Format dates to YYYY-MM-DD when the form is submitted
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                const startDate = document.getElementById('start_date');
                const endDate = document.getElementById('end_date');
                
                // Format dates to YYYY-MM-DD
                if (startDate.value) {
                    const date = new Date(startDate.value);
                    startDate.value = date.toISOString().split('T')[0];
                }
                
                if (endDate.value) {
                    const date = new Date(endDate.value);
                    endDate.value = date.toISOString().split('T')[0];
                }
                
                // Let the form submit normally
                return true;
            });
            
            // Set default date range if not set
            if (!document.getElementById('start_date').value) {
                const startDate = new Date();
                startDate.setDate(1); // First day of current month
                document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
                
                const endDate = new Date();
                endDate.setMonth(endDate.getMonth() + 1);
                endDate.setDate(0); // Last day of current month
                document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
            }
        });
        
        // Handle form submission with AJAX
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading...`;
            
            // Get form data and convert to URL parameters
            const formData = new URLSearchParams(new FormData(this));
            const url = `${this.action}?${formData.toString()}`;
            
            // Update URL without page reload
            window.history.pushState({}, '', url);
            
            // Fetch updated data
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update charts with new data
                updateCharts(data);
                
                // Update summary cards
                updateSummaryCards(data);
                
                // Update room type analysis table
                updateRoomTypeAnalysis(data);
                
                // Show success message
                showToast('Report updated successfully', 'success');
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while updating the report', 'error');
            })
            .finally(() => {
                // Restore button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
            
            // Handle reset button
            const resetBtn = document.getElementById('resetFilters');
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    // Reset form
                    dateRangeForm.reset();
                    
                    // Submit the form to load default data
                    dateRangeForm.dispatchEvent(new Event('submit'));
                });
            }
        }
        
        // Function to update all charts with new data
        function updateCharts(data) {
            // Update Bookings by Status chart
            if (statusChart && data.statusCounts) {
                statusChart.data.labels = Object.keys(data.statusCounts).map(key => 
                    key.charAt(0).toUpperCase() + key.slice(1)
                );
                statusChart.data.datasets[0].data = Object.values(data.statusCounts).map(item => item.count);
                statusChart.update();
            }
            
            // Update Status Trend chart
            if (statusTrendChart && data.monthlyStatusTrends) {
                statusTrendChart.data.labels = data.monthlyStatusTrends.labels || [];
                
                // Update each dataset
                statusTrendChart.data.datasets.forEach(dataset => {
                    const status = dataset.label.toLowerCase().replace(' ', '_');
                    if (data.monthlyStatusTrends[status]) {
                        dataset.data = statusTrendFilter.value === 'revenue' 
                            ? data.monthlyStatusTrends[status].map(item => item.revenue)
                            : data.monthlyStatusTrends[status].map(item => item.count);
                    }
                });
                
                statusTrendChart.update();
            }
            
            // Update Monthly Trends chart
            if (monthlyTrendsChart && data.monthlyBookings) {
                monthlyTrendsChart.data.labels = data.monthlyBookings.map(item => item.month);
                monthlyTrendsChart.data.datasets[0].data = data.monthlyBookings.map(item => item.count);
                monthlyTrendsChart.data.datasets[1].data = data.monthlyBookings.map(item => item.revenue);
                monthlyTrendsChart.update();
            }
            
            // Update Room Type Performance chart
            if (roomTypeChart && data.roomTypeBookings) {
                const roomTypes = Object.keys(data.roomTypeBookings);
                roomTypeChart.data.labels = roomTypes;
                roomTypeChart.data.datasets[0].data = roomTypes.map(type => data.roomTypeBookings[type].count);
                roomTypeChart.data.datasets[1].data = roomTypes.map(type => 
                    (data.roomTypeBookings[type].revenue / data.totalRevenue) * 100
                );
                roomTypeChart.update();
            }
        }
        
        // Function to update summary cards
        function updateSummaryCards(data) {
            const cards = {
                'totalBookings': data.totalBookings,
                'totalRevenue': data.totalRevenue,
                'averageStay': data.averageStay,
                'averageDailyRate': data.averageDailyRate,
                'occupancyRate': data.occupancyRate
            };
            
            Object.entries(cards).forEach(([key, value]) => {
                const element = document.querySelector(`[data-metric="${key}"]`);
                if (element) {
                    // Simple animation for number changes
                    const currentValue = parseFloat(element.textContent.replace(/[^0-9.-]+/g, '')) || 0;
                    animateValue(element, currentValue, value, 500);
                }
            });
        }
        
        // Function to update room type analysis table
        function updateRoomTypeAnalysis(data) {
            const tbody = document.querySelector('#roomTypeAnalysisTable tbody');
            if (!tbody) return;
            
            // Clear existing rows
            tbody.innerHTML = '';
            
            // Add new rows
            Object.entries(data.roomTypeBookings || {}).forEach(([type, item]) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        ${type}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                        ${formatNumber(item.count)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                        $${formatNumber(item.revenue.toFixed(2))}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                        $${formatNumber(item.average_rate.toFixed(2))}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                        ${item.average_stay.toFixed(1)} nights
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                        ${((item.revenue / data.totalRevenue) * 100).toFixed(1)}%
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            // Add total row
            const totalRow = document.createElement('tr');
            totalRow.className = 'bg-gray-50 font-semibold';
            totalRow.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    Total
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                    ${formatNumber(data.totalBookings)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                    $${formatNumber(data.totalRevenue.toFixed(2))}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                    $${formatNumber(data.averageDailyRate.toFixed(2))}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                    ${data.averageStay.toFixed(1)} nights
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                    100.0%
                </td>
            `;
            tbody.appendChild(totalRow);
        }
        
        // Helper function to animate number changes
        function animateValue(element, start, end, duration) {
            // If no valid end value, don't animate
            if (end === null || end === undefined) return;
            
            const range = end - start;
            const minFrameTime = 30; // 30fps
            const totalFrames = Math.round(duration / minFrameTime);
            let frame = 0;
            
            const isCurrency = element.textContent.includes('$');
            const isPercentage = element.textContent.includes('%');
            const isDecimal = element.textContent.includes('.');
            
            const animate = () => {
                frame++;
                const progress = frame / totalFrames;
                const current = start + (range * progress);
                
                // Format the number based on its type
                let displayValue;
                if (isCurrency) {
                    displayValue = `$${formatNumber(Math.round(current))}`;
                } else if (isPercentage) {
                    displayValue = `${current.toFixed(1)}%`;
                } else if (isDecimal) {
                    displayValue = current.toFixed(1);
                    if (element.textContent.includes('nights')) {
                        displayValue += ' nights';
                    }
                } else {
                    displayValue = formatNumber(Math.round(current));
                }
                
                element.textContent = displayValue;
                
                if (frame < totalFrames) {
                    requestAnimationFrame(animate);
                } else {
                    // Ensure final value is exact
                    if (isCurrency) {
                        element.textContent = `$${formatNumber(Math.round(end))}`;
                    } else if (isPercentage) {
                        element.textContent = `${end.toFixed(1)}%`;
                    } else if (isDecimal) {
                        element.textContent = end.toFixed(1);
                        if (element.textContent.includes('nights')) {
                            element.textContent += ' nights';
                        }
                    } else {
                        element.textContent = formatNumber(Math.round(end));
                    }
                }
            };
            
            animate();
        }
        
        // Show toast notification
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-md shadow-lg text-white ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            // Auto-remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
        
        // Initialize with any URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('start_date') || urlParams.has('end_date') || urlParams.has('status')) {
            // If URL has parameters, submit the form to load data
            dateRangeForm.dispatchEvent(new Event('submit'));
        }
    });
</script>
@endsection
