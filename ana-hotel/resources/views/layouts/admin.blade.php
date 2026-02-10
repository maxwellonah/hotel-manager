<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Admin</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
        
        <!-- Scripts -->
        <link rel="stylesheet" href="{{ asset(mix('css/app.css')) }}">
        <script src="{{ asset(mix('js/app.js')) }}" defer></script>
        
        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Alpine.js -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        
        @stack('styles')
    </head>
    <body class="bg-gray-100 font-sans leading-normal tracking-normal">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col">
            <!-- Mobile header -->
            <header class="bg-white shadow-sm md:hidden sticky top-0 z-20">
                <div class="flex items-center justify-between px-4 py-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-700 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="text-lg font-semibold">ANA Hotel Admin</span>
                    <div class="w-6"></div> <!-- Spacer for alignment -->
                </div>
            </header>

            <div class="flex flex-1">
                <!-- Desktop Sidebar -->
                <div class="hidden md:flex md:flex-shrink-0">
                    <div class="w-64 bg-gray-800 text-white h-screen sticky top-0 overflow-y-auto">
                        <div class="flex items-center justify-center h-16 bg-gray-900">
                            <span class="text-xl font-semibold">ANA Hotel Admin</span>
                        </div>
                        <nav class="mt-5 px-4 space-y-1">
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-700">Dashboard</a>
                            <a href="{{ route('admin.rooms.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700">Rooms</a>
                            <a href="{{ route('admin.bookings.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700">Bookings</a>
                            <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded bg-gray-700">Reports</a>
                        </nav>
                    </div>
                </div>

                <!-- Mobile Sidebar -->
                <div class="fixed inset-0 z-30 md:hidden" x-show="sidebarOpen" @click.away="sidebarOpen = false">
                    <div class="absolute inset-0 bg-gray-600 opacity-75"></div>
                    <div class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white shadow-lg">
                        <div class="flex items-center justify-between h-16 px-4 bg-gray-900">
                            <span class="text-xl font-semibold">Menu</span>
                            <button @click="sidebarOpen = false" class="text-white focus:outline-none">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <nav class="px-4 py-4 space-y-2">
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-700">Dashboard</a>
                            <a href="{{ route('admin.rooms.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700">Rooms</a>
                            <a href="{{ route('admin.bookings.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700">Bookings</a>
                            <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded bg-gray-700">Reports</a>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 flex flex-col min-w-0">
                    <!-- Desktop Top Bar -->
                    <header class="bg-white shadow-sm hidden md:block sticky top-0 z-10">
                        <div class="flex items-center justify-between px-6 py-4">
                            <h1 class="text-xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
                            <div class="flex items-center">
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                                        <span>{{ Auth::user()->name }}</span>
                                        <svg class="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <div class="py-1">
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    {{ __('Log Out') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    <!-- Page Content -->
                    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 md:p-6">
                        @yield('content')
                    </main>
                </div>
            </div>

            @stack('scripts')
        </body>
    </html>
