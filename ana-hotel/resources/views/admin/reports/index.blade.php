@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Reports Dashboard</h2>
                    <div class="flex items-center space-x-4">
                        <form id="dateRangeForm" class="flex items-center space-x-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">From</label>
                                <input type="date" name="start_date" id="start_date" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       value="{{ $startDate }}">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">To</label>
                                <input type="date" name="end_date" id="end_date" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       value="{{ $endDate }}">
                            </div>
                            <div class="mt-5">
                                <button type="submit" 
                                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Apply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Occupancy Report Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">Occupancy Report</h3>
                                    <p class="mt-1 text-sm text-gray-500">View room occupancy rates and statistics</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('reports.occupancy', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                    View occupancy report<span class="sr-only">Occupancy report</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Report Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">Revenue Report</h3>
                                    <p class="mt-1 text-sm text-gray-500">Analyze revenue and payment statistics</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('reports.revenue', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                    View revenue report<span class="sr-only">Revenue report</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Bookings Report Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">Bookings Report</h3>
                                    <p class="mt-1 text-sm text-gray-500">View booking statistics and trends</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('reports.bookings', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                    View bookings report<span class="sr-only">Bookings report</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Guests Report Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">Guests Report</h3>
                                    <p class="mt-1 text-sm text-gray-500">Analyze guest demographics and behavior</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('reports.guests', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                    View guests report<span class="sr-only">Guests report</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-gray-50 px-4 py-5 sm:px-6 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Stats</h3>
                    <dl class="grid grid-cols-1 gap-5 sm:grid-cols-4">
                        <div class="px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Bookings</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900" id="totalBookings">--</dd>
                        </div>
                        <div class="px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900" id="totalRevenue">--</dd>
                        </div>
                        <div class="px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Average Stay</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900" id="avgStay">--</dd>
                        </div>
                        <div class="px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Occupancy Rate</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900" id="occupancyRate">--%</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle date range form submission
        const dateRangeForm = document.getElementById('dateRangeForm');
        if (dateRangeForm) {
            dateRangeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                const url = new URL(window.location.href);
                url.searchParams.set('start_date', startDate);
                url.searchParams.set('end_date', endDate);
                window.location.href = url.toString();
            });
        }

        // Load quick stats
        loadQuickStats();
    });

    function loadQuickStats() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        // You can fetch real data from your API endpoints here
        // For now, we'll use placeholder data
        fetch(`/api/reports/quick-stats?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalBookings').textContent = data.total_bookings || '--';
                document.getElementById('totalRevenue').textContent = data.total_revenue ? `$${data.total_revenue.toLocaleString()}` : '--';
                document.getElementById('avgStay').textContent = data.avg_stay ? `${data.avg_stay} nights` : '--';
                document.getElementById('occupancyRate').textContent = data.occupancy_rate ? `${data.occupancy_rate}%` : '--%';
            })
            .catch(error => {
                console.error('Error loading quick stats:', error);
            });
    }
</script>
@endpush
@endsection
