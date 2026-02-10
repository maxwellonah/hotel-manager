@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Guests Report</h2>
                    <a href="{{ route('admin.reports') }}" class="text-gray-600 hover:text-gray-900">
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
                        <div class="w-full sm:w-auto mt-6">
                            <button type="submit" 
                                    class="inline-flex justify-center w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Apply Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Guests
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $totalGuests }}
                                        </div>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        New Guests
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $newGuests }}
                                        </div>
                                        <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                            {{ $newGuestsPercentage }}%
                                        </div>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Repeat Guests
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $repeatGuests }}
                                        </div>
                                        <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                            {{ $repeatGuestsPercentage }}%
                                        </div>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Countries
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $totalCountries }}
                                        </div>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guests by Country -->
                <div class="bg-white shadow rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Guests by Country</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="h-80">
                            <canvas id="guestsByCountryChart"></canvas>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Country
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Guests
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Percentage
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($guestsByCountry as $country => $data)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $country }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                            {{ $data['count'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                            {{ number_format($data['percentage'], 1) }}%
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Guest Demographics -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Age Distribution -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Age Distribution</h3>
                        <div class="h-80">
                            <canvas id="ageDistributionChart"></canvas>
                        </div>
                    </div>

                    <!-- Gender Distribution -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Gender Distribution</h3>
                        <div class="h-80">
                            <canvas id="genderDistributionChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Repeat Guest Analysis -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Repeat Guest Analysis</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-md font-medium text-gray-700 mb-2">Repeat Guest Rate</h4>
                            <div class="h-64">
                                <canvas id="repeatGuestRateChart"></canvas>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-md font-medium text-gray-700 mb-2">Average Stays per Repeat Guest</h4>
                            <div class="h-64">
                                <canvas id="avgStaysPerGuestChart"></canvas>
                            </div>
                        </div>
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
                window.location.href = `{{ route('admin.reports.guests') }}?start_date=${startDate}&end_date=${endDate}`;
            });
        }

        // Initialize charts
        initGuestsByCountryChart();
        initAgeDistributionChart();
        initGenderDistributionChart();
        initRepeatGuestRateChart();
        initAvgStaysPerGuestChart();
    });

    function initGuestsByCountryChart() {
        const ctx = document.getElementById('guestsByCountryChart').getContext('2d');
        const countries = @json(collect($guestsByCountry)->keys());
        const counts = @json(collect($guestsByCountry)->pluck('count'));
        
        // Get top 5 countries and group the rest as 'Others'
        const topCountries = countries.slice(0, 5);
        const topCounts = counts.slice(0, 5);
        
        if (countries.length > 5) {
            const otherCount = counts.slice(5).reduce((a, b) => a + b, 0);
            topCountries.push('Others');
            topCounts.push(otherCount);
        }

        const backgroundColors = [
            'rgba(79, 70, 229, 0.7)',
            'rgba(59, 130, 246, 0.7)',
            'rgba(16, 185, 129, 0.7)',
            'rgba(245, 158, 11, 0.7)',
            'rgba(139, 92, 246, 0.7)',
            'rgba(156, 163, 175, 0.7)'
        ];

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: topCountries,
                datasets: [{
                    data: topCounts,
                    backgroundColor: backgroundColors,
                    borderColor: backgroundColors.map(color => color.replace('0.7', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function initAgeDistributionChart() {
        const ctx = document.getElementById('ageDistributionChart').getContext('2d');
        const ageGroups = ['18-24', '25-34', '35-44', '45-54', '55-64', '65+'];
        const counts = @json($ageDistribution);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ageGroups,
                datasets: [{
                    label: 'Number of Guests',
                    data: counts,
                    backgroundColor: 'rgba(79, 70, 229, 0.7)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    function initGenderDistributionChart() {
        const ctx = document.getElementById('genderDistributionChart').getContext('2d');
        const genders = @json(collect($genderDistribution)->keys()->map(fn($g) => ucfirst($g)));
        const counts = @json(collect($genderDistribution)->values());
        
        const backgroundColors = [
            'rgba(79, 70, 229, 0.7)',
            'rgba(236, 72, 153, 0.7)',
            'rgba(156, 163, 175, 0.7)'
        ];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: genders,
                datasets: [{
                    data: counts,
                    backgroundColor: backgroundColors,
                    borderColor: backgroundColors.map(color => color.replace('0.7', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function initRepeatGuestRateChart() {
        const ctx = document.getElementById('repeatGuestRateChart').getContext('2d');
        const repeatRate = {{ $repeatGuestRate }};
        const newGuestRate = 100 - repeatRate;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Repeat Guests', 'New Guests'],
                datasets: [{
                    data: [repeatRate, newGuestRate],
                    backgroundColor: ['rgba(16, 185, 129, 0.7)', 'rgba(99, 102, 241, 0.7)'],
                    borderColor: ['rgba(16, 185, 129, 1)', 'rgba(99, 102, 241, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                }
            }
        });
    }

    function initAvgStaysPerGuestChart() {
        const ctx = document.getElementById('avgStaysPerGuestChart').getContext('2d');
        const avgStays = {{ $avgStaysPerGuest }};
        const maxStays = Math.ceil(avgStays * 1.5); // For better visualization
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Average Stays per Repeat Guest'],
                datasets: [{
                    label: 'Average Stays',
                    data: [avgStays],
                    backgroundColor: 'rgba(245, 158, 11, 0.7)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 1,
                    barPercentage: 0.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        max: maxStays,
                        ticks: {
                            precision: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Average stays: ${context.raw.toFixed(1)}`;
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
