@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Occupancy Report</h2>
                    <a href="{{ route('admin.reports.index') }}" class="text-gray-600 hover:text-gray-900">
                        &larr; Back to Reports
                    </a>
                </div>

                <!-- Date Range Filter -->
                <div class="bg-gray-50 px-4 py-5 sm:px-6 rounded-lg mb-6">
                    <form id="dateRangeForm" class="flex flex-col sm:flex-row items-end space-y-4 sm:space-y-0 sm:space-x-4">
                        <div class="w-full sm:w-auto">
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" id="start_date" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   value="{{ $startDate }}">
                        </div>
                        <div class="w-full sm:w-auto">
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" id="end_date" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   value="{{ $endDate }}">
                        </div>
                        <div class="w-full sm:w-auto">
                            <button type="submit" 
                                    class="inline-flex justify-center w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Apply Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Rooms
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $totalRooms }}</div>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Avg. Occupancy Rate
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900" id="avgOccupancyRate">{{ number_format($occupancyRate, 2) }}%</div>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Peak Occupancy Day
                                    </dt>
                                    <dd class="flex items-baseline">
                                        @php
                                            $peakDay = '';
                                            $maxRate = 0;
                                            foreach ($chartData as $index => $rate) {
                                                if ($rate > $maxRate) {
                                                    $maxRate = $rate;
                                                    $peakDay = $chartLabels[$index];
                                                }
                                            }
                                        @endphp
                                        <div class="text-2xl font-semibold text-gray-900" id="peakOccupancyDay">{{ $peakDay ?: 'N/A' }}</div>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Occupancy Chart -->
                <div class="bg-white shadow rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Occupancy Rate Over Time</h3>
                    <div class="h-80">
                        <canvas id="occupancyChart"></canvas>
                    </div>
                </div>

                <!-- Room Type Breakdown -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Occupancy by Room Type</h3>
                    <div class="h-80">
                        <canvas id="roomTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle date range form submission
        const dateRangeForm = document.getElementById('dateRangeForm');
        if (dateRangeForm) {
            dateRangeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                window.location.href = `{{ route('admin.reports.occupancy') }}?start_date=${startDate}&end_date=${endDate}`;
            });
        }

        // Initialize charts
        initOccupancyChart();
        initRoomTypeChart();
    });

    function initOccupancyChart() {
        const ctx = document.getElementById('occupancyChart').getContext('2d');
        const dates = @json($dates);
        const occupancyRates = @json($occupancyRates);
        
        // Calculate average occupancy
        const avgOccupancy = occupancyRates.length > 0 ? 
            (occupancyRates.reduce((a, b) => a + b, 0) / occupancyRates.length).toFixed(2) : 0;
        document.getElementById('avgOccupancyRate').textContent = `${avgOccupancy}%`;
        
        // Find peak occupancy day
        if (occupancyRates.length > 0) {
            const maxOccupancy = Math.max(...occupancyRates);
            const maxIndex = occupancyRates.indexOf(maxOccupancy);
            document.getElementById('peakOccupancyDay').textContent = dates[maxIndex];
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Occupancy Rate (%)',
                    data: occupancyRates,
                    borderColor: 'rgba(79, 70, 229, 1)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Occupancy: ${context.parsed.y}%`;
                            }
                        }
                    }
                }
            }
        });
    }

    function initRoomTypeChart() {
        const ctx = document.getElementById('roomTypeChart').getContext('2d');
        
        // Get data from the controller
        const roomTypeData = @json($roomTypeOccupancy);
        const roomTypes = Object.keys(roomTypeData);
        const occupancyData = Object.values(roomTypeData).map(room => room.occupancy_rate);
        
        // Generate colors dynamically based on number of room types
        const backgroundColors = [
            'rgba(79, 70, 229, 0.7)',
            'rgba(59, 130, 246, 0.7)',
            'rgba(16, 185, 129, 0.7)',
            'rgba(245, 158, 11, 0.7)',
            'rgba(139, 92, 246, 0.7)',
            'rgba(236, 72, 153, 0.7)',
            'rgba(20, 184, 166, 0.7)',
            'rgba(249, 115, 22, 0.7)'
        ].slice(0, roomTypes.length);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: roomTypes,
                datasets: [{
                    label: 'Average Occupancy Rate (%)',
                    data: occupancyData,
                    backgroundColor: backgroundColors,
                    borderColor: backgroundColors.map(color => color.replace('0.7', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Occupancy: ${context.parsed.y}%`;
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection
