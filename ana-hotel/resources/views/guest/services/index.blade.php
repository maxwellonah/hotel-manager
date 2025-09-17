@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Hotel Services') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Spa Services -->
                    <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold mb-2">Spa & Wellness</h3>
                            <p class="text-gray-600 text-sm mb-4">Relax and rejuvenate with our premium spa services including massages, facials, and body treatments.</p>
                            <div class="flex justify-between items-center">
                                <span class="text-blue-600 font-medium">From $50</span>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                    Book Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Restaurant -->
                    <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold mb-2">Restaurant</h3>
                            <p class="text-gray-600 text-sm mb-4">Enjoy exquisite dining with our chef's special menu featuring local and international cuisine.</p>
                            <div class="flex justify-between items-center">
                                <span class="text-blue-600 font-medium">Open 7:00 AM - 11:00 PM</span>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                    View Menu
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Room Service -->
                    <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold mb-2">24/7 Room Service</h3>
                            <p class="text-gray-600 text-sm mb-4">Enjoy delicious meals and drinks delivered to your room at any time of the day or night.</p>
                            <div class="flex justify-between items-center">
                                <span class="text-blue-600 font-medium">Available 24/7</span>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                    Order Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Laundry -->
                    <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold mb-2">Laundry Service</h3>
                            <p class="text-gray-600 text-sm mb-4">Same-day laundry and dry cleaning services available for your convenience.</p>
                            <div class="flex justify-between items-center">
                                <span class="text-blue-600 font-medium">Same day service</span>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                    Request Pickup
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Concierge -->
                    <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                            </svg>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold mb-2">Concierge</h3>
                            <p class="text-gray-600 text-sm mb-4">Let our concierge assist you with reservations, tours, and local recommendations.</p>
                            <div class="flex justify-between items-center">
                                <span class="text-blue-600 font-medium">Available 24/7</span>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                    Contact Concierge
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Business Center -->
                    <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold mb-2">Business Center</h3>
                            <p class="text-gray-600 text-sm mb-4">Fully equipped business center with printing, copying, and secretarial services.</p>
                            <div class="flex justify-between items-center">
                                <span class="text-blue-600 font-medium">Open 8:00 AM - 8:00 PM</span>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                    More Info
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Need something else?</h3>
                    <p class="text-gray-600 mb-4">Our staff is available 24/7 to assist you with any additional requests or special arrangements.</p>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition duration-300">
                        Contact Front Desk
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
