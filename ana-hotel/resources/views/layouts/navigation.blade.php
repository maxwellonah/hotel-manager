<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-10 w-auto fill-current text-gray-600" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <!-- Admin Menu Items -->
                            <x-nav-link :href="route('admin.users.index')" :active="request()->is('admin/users*')">
                                {{ __('Users') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.rooms.index')" :active="request()->is('admin/rooms*')">
                                {{ __('Rooms') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.bookings.index')" :active="request()->is('admin/bookings*')">
                                {{ __('Bookings') }}
                            </x-nav-link>
                            <x-nav-link :href="route('reports.index')" :active="request()->is('reports*')">
                                {{ __('Reports') }}
                            </x-nav-link>
                        @elseif(auth()->user()->role === 'receptionist')
                            <!-- Receptionist Menu Items -->
                            <x-nav-link :href="route('bookings.index')" :active="request()->is('bookings*')">
                                {{ __('Bookings') }}
                            </x-nav-link>
                            <x-nav-link :href="route('check-in.index')" :active="request()->is('check-in*')">
                                {{ __('Check-in') }}
                            </x-nav-link>
                            <x-nav-link :href="route('check-out.index')" :active="request()->is('check-out*')">
                                {{ __('Check-out') }}
                            </x-nav-link>
                        @elseif(auth()->user()->role === 'housekeeping')
                            <!-- Housekeeping Menu Items -->
                            <x-nav-link :href="route('housekeeping-tasks.index')" :active="request()->is('housekeeping/tasks*')">
                                {{ __('My Tasks') }}
                            </x-nav-link>
                            <x-nav-link :href="route('housekeeping.rooms')" :active="request()->is('housekeeping/rooms*')">
                                {{ __('Room Status') }}
                            </x-nav-link>
                        @elseif(auth()->user()->role === 'guest')
                            <!-- Guest Menu Items -->
                            <x-nav-link :href="route('profile.bookings')" :active="request()->is('profile/bookings*')">
                                {{ __('My Bookings') }}
                            </x-nav-link>
                            <x-nav-link :href="route('guest.services.index')" :active="request()->is('services*')">
                                {{ __('Services') }}
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <x-button type="submit" class="bg-red-600 hover:bg-red-700 focus:ring-red-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        {{ __('Log Out') }}
                    </x-button>
                </form>
                
                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- User Info -->
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm text-gray-700">Signed in as</p>
                            <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->email }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ ucfirst(Auth::user()->role) }}</p>
                        </div>
                        
                        <!-- Profile Link -->
                        <x-dropdown-link :href="route('profile.show')" class="flex items-center">
                            <svg class="mr-2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Settings Link -->
                        <x-dropdown-link :href="route('profile.edit')" class="flex items-center">
                            <svg class="mr-2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ __('Settings') }}
                        </x-dropdown-link>

                        <!-- Divider -->
                        <div class="border-t border-gray-100 my-1"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    class="flex items-center text-red-600 hover:bg-red-50"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <!-- Mobile Logout Button -->
        <div class="pt-2 pb-3 px-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            @auth
                @if(auth()->user()->role === 'admin')
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->is('admin/users*')">
                        {{ __('Users') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.rooms.index')" :active="request()->is('admin/rooms*')">
                        {{ __('Rooms') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.bookings.index')" :active="request()->is('admin/bookings*')">
                        {{ __('Bookings') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reports.index')" :active="request()->is('reports*')">
                        {{ __('Reports') }}
                    </x-responsive-nav-link>
                @elseif(auth()->user()->role === 'receptionist')
                    <x-responsive-nav-link :href="route('bookings.index')" :active="request()->is('bookings*')">
                        {{ __('Bookings') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('check-in.index')" :active="request()->is('check-in*')">
                        {{ __('Check-in') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('check-out.index')" :active="request()->is('check-out*')">
                        {{ __('Check-out') }}
                    </x-responsive-nav-link>
                @elseif(auth()->user()->role === 'housekeeping')
                    <x-responsive-nav-link :href="route('housekeeping-tasks.index')" :active="request()->is('housekeeping/tasks*')">
                        {{ __('My Tasks') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('housekeeping.rooms')" :active="request()->is('housekeeping/rooms*')">
                        {{ __('Room Status') }}
                    </x-responsive-nav-link>
                @elseif(auth()->user()->role === 'guest')
                    <x-responsive-nav-link :href="route('profile.bookings')" :active="request()->is('profile/bookings*')">
                        {{ __('My Bookings') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('guest.services.index')" :active="request()->is('services*')">
                        {{ __('Services') }}
                    </x-responsive-nav-link>
                @endif
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ ucfirst(Auth::user()->role) }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Profile Link -->
                <x-responsive-nav-link :href="route('profile.show')">
                    <div class="flex items-center">
                        <svg class="mr-2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ __('Profile') }}
                    </div>
                </x-responsive-nav-link>

                <!-- Settings Link -->
                <x-responsive-nav-link :href="route('profile.edit')">
                    <div class="flex items-center">
                        <svg class="mr-2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ __('Settings') }}
                    </div>
                </x-responsive-nav-link>

                <!-- Divider -->
                <div class="border-t border-gray-200 my-1"></div>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            class="text-red-600 hover:bg-red-50"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
