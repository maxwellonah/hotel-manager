@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">My Profile</h2>
                    <a href="{{ route('profile.edit') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                        Edit Profile
                    </a>
                </div>

                @if (session('status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline">{{ session('status') }}</span>
                    </div>
                @endif

                <div class="bg-gray-50 p-6 rounded-lg">
                    <div class="md:flex md:space-x-8">
                        <!-- Profile Picture -->
                        <div class="md:w-1/4 mb-6 md:mb-0">
                            <div class="w-32 h-32 mx-auto rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                                @if($user->profile_photo_path)
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="h-20 w-20 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                        </div>

                        <!-- Profile Information -->
                        <div class="md:w-3/4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Full Name</h3>
                                    <p class="mt-1 text-lg text-gray-900">{{ $user->name }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Email Address</h3>
                                    <p class="mt-1 text-lg text-gray-900">{{ $user->email }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Phone Number</h3>
                                    <p class="mt-1 text-lg text-gray-900">{{ $user->phone ?? 'Not provided' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Account Role</h3>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <h3 class="text-sm font-medium text-gray-500">Address</h3>
                                    <p class="mt-1 text-lg text-gray-900">
                                        @if($user->address)
                                            {{ $user->address }},<br>
                                            @if($user->city){{ $user->city }}, @endif
                                            @if($user->state){{ $user->state }}, @endif
                                            @if($user->country){{ $user->country }} @endif
                                            @if($user->postal_code){{ $user->postal_code }} @endif
                                        @else
                                            Not provided
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password Section -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <form method="POST" action="{{ route('profile.password.update') }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                    <input type="password" name="current_password" id="current_password" autocomplete="current-password" 
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @error('current_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="md:col-span-2"></div>
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                    <input type="password" name="password" id="password" autocomplete="new-password" 
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" 
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="md:col-span-2">
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                        Update Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
