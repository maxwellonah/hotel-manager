@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-6">Admin Dashboard</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Quick Stats -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Rooms</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ \App\Models\Room::count() }}</p>
                    </div>
                    
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Active Bookings</h3>
                        <p class="text-3xl font-bold text-green-600">{{ \App\Models\Booking::where('status', 'confirmed')->count() }}</p>
                    </div>
                    
                    <div class="bg-yellow-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Revenue</h3>
                        <p class="text-3xl font-bold text-yellow-600">${{ number_format(\App\Models\Payment::sum('amount'), 2) }}</p>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="mt-8">
                    <h3 class="text-xl font-semibold mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="{{ route('rooms.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded text-center transition duration-300">
                            Add New Room
                        </a>
                        <a href="{{ route('bookings.create') }}" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded text-center transition duration-300">
                            New Booking
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-3 px-4 rounded text-center transition duration-300">
                            Manage Users
                        </a>
                        <a href="{{ route('reports.index') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-3 px-4 rounded text-center transition duration-300">
                            View Reports
                        </a>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="mt-8">
                    <h3 class="text-xl font-semibold mb-4">Recent Activities</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        @if(\App\Models\AuditLog::count() > 0)
                            <ul class="divide-y divide-gray-200">
                                @foreach(\App\Models\AuditLog::with('user')->latest()->take(5)->get() as $log)
                                    <li class="py-2">
                                        <div class="flex items-center">
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</p>
                                                <p class="text-sm text-gray-500">{{ $log->action }} - {{ $log->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="mt-4 text-right">
                                <a href="{{ route('audit-logs.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">View all activities</a>
                            </div>
                        @else
                            <p class="text-gray-500">No recent activities found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
