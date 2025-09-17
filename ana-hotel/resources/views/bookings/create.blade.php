@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Create New Booking') }}
    </h2>
@endsection

@push('styles')
    <link href="{{ asset('css/booking-form.css') }}?v={{ filemtime(public_path('css/booking-form.css')) }}" rel="stylesheet">
@endpush

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <form method="POST" action="{{ route('bookings.store') }}">
                    @csrf

                    @if(in_array(auth()->user()->role, ['admin', 'receptionist']))
                        <input type="hidden" name="is_guest_booking" value="1">
                        <div class="mb-4">
                            <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">
                                {{ __('Guest') }}
                            </label>
                            <div class="guest-search-container">
                                <label for="guest_search" class="sr-only">{{ __('Search guest') }}</label>
                                <input type="text" 
                                       id="guest_search" 
                                       class="guest-search-input @error('user_id') form-input-error @enderror" 
                                       placeholder="{{ __('Search guest by name, email, or phone') }}"
                                       autocomplete="off"
                                       aria-describedby="guestSearchHint">
                                <input type="hidden" name="user_id" id="user_id" value="{{ old('user_id') }}" required>
                                <div id="guestSearchHint" class="guest-search-hint">{{ __('Start typing to search for a guest') }}</div>
                                <div id="guest_search_results" role="listbox" aria-label="Search results">
                                    <!-- Search results will be populated here -->
                                </div>
                            </div>
                            @error('user_id')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="mb-4">
                        <label for="room_type_id" class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('Room Type') }}
                        </label>
                        <select id="room_type_id" class="form-select w-full @error('room_type_id') border-red-500 @enderror" 
                                name="room_type_id" required>
                            <option value="">{{ __('Select a room type') }}</option>
                            @foreach($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}" {{ old('room_type_id') == $roomType->id ? 'selected' : '' }}>
                                    {{ $roomType->name }} - ${{ number_format($roomType->price_per_night, 2) }}/night 
                                    ({{ $roomType->capacity }} {{ Str::plural('person', $roomType->capacity) }})
                                </option>
                            @endforeach
                        </select>
                        @error('room_type_id')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4 md:flex md:space-x-4">
                        <div class="w-full md:w-1/2 mb-4 md:mb-0">
                            <label for="check_in" class="block text-gray-700 text-sm font-bold mb-2">
                                {{ __('Check-in Date') }}
                            </label>
                            <input id="check_in" type="date" 
                                   class="form-input w-full @error('check_in') border-red-500 @enderror" 
                                   name="check_in" 
                                   value="{{ old('check_in') }}" 
                                   min="{{ now()->format('Y-m-d') }}" 
                                   required>
                            @error('check_in')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="w-full md:w-1/2">
                            <label for="check_out" class="block text-gray-700 text-sm font-bold mb-2">
                                {{ __('Check-out Date') }}
                            </label>
                            <input id="check_out" type="date" 
                                   class="form-input w-full @error('check_out') border-red-500 @enderror" 
                                   name="check_out" 
                                   value="{{ old('check_out') }}"
                                   min="{{ now()->addDay()->format('Y-m-d') }}" 
                                   required>
                            @error('check_out')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Early Check-in Option -->
                    <div class="mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="is_early_checkin" 
                                   name="is_early_checkin" 
                                   value="1"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                   onchange="toggleEarlyCheckinFields()">
                            <label for="is_early_checkin" class="ml-2 block text-gray-700 text-sm font-bold">
                                {{ __('Early Check-in') }}
                            </label>
                        </div>
                        <p class="text-gray-600 text-xs mt-1">{{ __('Check this if the guest wants to check in before the scheduled date.') }}</p>
                    </div>

                    <div class="mb-6">
                        <label for="special_requests" class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('Special Requests') }}
                        </label>
                        <textarea id="special_requests" 
                                  class="form-textarea w-full @error('special_requests') border-red-500 @enderror" 
                                  name="special_requests" 
                                  rows="3"
                                  placeholder="{{ __('Any special requests or requirements?') }}">{{ old('special_requests') }}</textarea>
                        @error('special_requests')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ url()->previous() }}" class="text-gray-600 hover:text-gray-800 mr-4">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary" aria-label="{{ __('Complete booking') }}">
                            {{ __('Book Now') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Function to toggle early check-in fields
    function toggleEarlyCheckinFields() {
        const checkInDate = new Date(document.getElementById('check_in').value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const earlyCheckinCheckbox = document.getElementById('is_early_checkin');
        
        // Only enable early check-in if check-in date is in the future
        if (checkInDate > today) {
            earlyCheckinCheckbox.disabled = false;
            
            // If early check-in is checked, show a confirmation
            if (earlyCheckinCheckbox.checked) {
                return confirm('Are you sure you want to allow early check-in? This will mark the room as occupied immediately.');
            }
        } else {
            earlyCheckinCheckbox.checked = false;
            earlyCheckinCheckbox.disabled = true;
        }
        
        return true;
    }
    
    // Add event listener to check-in date field
    document.addEventListener('DOMContentLoaded', function() {
        const checkInInput = document.getElementById('check_in');
        if (checkInInput) {
            checkInInput.addEventListener('change', function() {
                const checkInDate = new Date(this.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                const earlyCheckinCheckbox = document.getElementById('is_early_checkin');
                
                // Enable/disable early check-in based on check-in date
                if (checkInDate > today) {
                    earlyCheckinCheckbox.disabled = false;
                } else {
                    earlyCheckinCheckbox.checked = false;
                    earlyCheckinCheckbox.disabled = true;
                }
            });
        }
    });
</script>

<div id="js-test" style="position: fixed; bottom: 20px; right: 20px; background: #f0f0f0; padding: 10px; border: 1px solid #ccc; z-index: 9999;">
    <p>JS Test: <span id="js-test-result">Not working</span></p>
    <button id="test-js" class="bg-blue-500 text-white px-4 py-2 rounded">Test JavaScript</button>
</div>

<script>
    // Test JavaScript functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Test button functionality
        const testButton = document.getElementById('test-js');
        const testResult = document.getElementById('js-test-result');
        
        if (testButton && testResult) {
            testButton.addEventListener('click', function() {
                testResult.textContent = 'JavaScript is working!';
                testResult.style.color = 'green';
                console.log('Test button clicked - JavaScript is working');
            });
            console.log('Test button initialized');
        } else {
            console.error('Test elements not found');
        }
        
        // Original code continues...
        console.log('DOM fully loaded');
        
        const searchInput = document.getElementById('guest_search');
        const userIdInput = document.getElementById('user_id');
        const searchResults = document.getElementById('guest_search_results');
        
        console.log('Search input element:', searchInput);
        console.log('User ID input element:', userIdInput);
        console.log('Search results element:', searchResults);
        
        if (!searchInput) {
            console.error('Search input not found! Check the ID matches the input field.');
            return;
        }
        
        let searchTimeout;
        let currentFocus = -1;
        
        // Ensure elements exist
        if (!searchInput || !userIdInput || !searchResults) {
            console.error('Required elements not found');
            return;
        }

        // Function to show search results
        function showSearchResults() {
            searchResults.style.display = 'block';
            console.log('Showing search results');
        }

        // Function to hide search results
        function hideSearchResults() {
            searchResults.style.display = 'none';
            console.log('Hiding search results');
        }

        // Debug: Check if event listener is added
        console.log('Adding input event listener to search input');
        
        // Handle search input
        searchInput.addEventListener('input', function(e) {
            console.log('Input event triggered, value:', e.target.value);
            clearTimeout(searchTimeout);
            const searchTerm = e.target.value.trim();
            console.log('Search term:', searchTerm);
            
            if (searchTerm.length < 2) {
                console.log('Search term too short, hiding results');
                hideSearchResults();
                return;
            }

            searchTimeout = setTimeout(() => {
                const url = `/api/guests/search?search=${encodeURIComponent(searchTerm)}`;
                console.log('Making API request to:', url);
                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log('API response status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('API error response:', text);
                            throw new Error(`HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(guests => {
                        console.log('API response data:', guests);
                        if (!Array.isArray(guests)) {
                            console.error('Invalid response format:', guests);
                            throw new Error('Invalid response format from server');
                        }
                        console.log('Number of guests found:', guests.length);

                        if (guests.length === 0) {
                            console.log('No guests found');
                            searchResults.innerHTML = '<div class="p-2 text-gray-500">No guests found</div>';
                            showSearchResults();
                            return;
                        }

                        let resultsHtml = '';
                        guests.forEach((guest, index) => {
                            const displayText = `${guest.name} (${guest.email})${guest.phone ? ' - ' + guest.phone : ''}`;
                            const isSelected = index === 0 ? 'true' : 'false';
                            resultsHtml += `
                                <div role="option" 
                                     id="guest-option-${index}"
                                     tabindex="-1"
                                     aria-selected="${isSelected}"
                                     data-id="${guest.id}"
                                     data-display="${displayText.replace(/"/g, '&quot;')}"
                                     class="border-b border-gray-100">
                                    ${displayText}
                                </div>
                            `;
                        });
                        
                        searchResults.innerHTML = resultsHtml;
                        currentFocus = -1;
                        showSearchResults();
                        
                        // Set up keyboard navigation
                        const options = searchResults.querySelectorAll('[role="option"]');
                        options.forEach((option, index) => {
                            option.addEventListener('click', function() {
                                selectOption(this);
                            });
                            
                            option.addEventListener('keydown', function(e) {
                                if (e.key === 'Enter' || e.key === ' ') {
                                    e.preventDefault();
                                    selectOption(this);
                                }
                            });
                        });
                        
                        // Set first option as active
                        if (options.length > 0) {
                            options[0].setAttribute('aria-selected', 'true');
                            options[0].classList.add('bg-gray-100');
                            currentFocus = 0;
                        }
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        searchResults.innerHTML = `<div class="p-2 text-red-500">Error: ${error.message}</div>`;
                        showSearchResults();
                    });
            }, 300);
        });

        // Function to select an option
        function selectOption(option) {
            const guestId = option.getAttribute('data-id');
            const displayText = option.getAttribute('data-display');
            
            userIdInput.value = guestId;
            searchInput.value = displayText;
            hideSearchResults();
            searchInput.focus();
        }
        
        // Handle click on search result
        searchResults.addEventListener('click', function(e) {
            const result = e.target.closest('[role="option"]');
            if (result) {
                selectOption(result);
            }
        });
        
        // Handle keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            const options = searchResults.querySelectorAll('[role="option"]');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (currentFocus < options.length - 1) {
                    if (currentFocus >= 0) {
                        options[currentFocus].setAttribute('aria-selected', 'false');
                        options[currentFocus].classList.remove('bg-gray-100');
                    }
                    currentFocus++;
                    options[currentFocus].setAttribute('aria-selected', 'true');
                    options[currentFocus].classList.add('bg-gray-100');
                    options[currentFocus].focus();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (currentFocus > 0) {
                    options[currentFocus].setAttribute('aria-selected', 'false');
                    options[currentFocus].classList.remove('bg-gray-100');
                    currentFocus--;
                    options[currentFocus].setAttribute('aria-selected', 'true');
                    options[currentFocus].classList.add('bg-gray-100');
                    options[currentFocus].focus();
                }
            } else if (e.key === 'Enter' && currentFocus > -1) {
                e.preventDefault();
                selectOption(options[currentFocus]);
            } else if (e.key === 'Escape') {
                hideSearchResults();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                hideSearchResults();
            }
        });

        // Show search results when input is focused
        searchInput.addEventListener('focus', function() {
            if (searchResults.children.length > 0) {
                showSearchResults();
            }
        });

        // If we have an existing user ID, fetch and display their info
        if (userIdInput.value) {
            fetch(`/api/guests/${userIdInput.value}`)
                .then(response => response.json())
                .then(guest => {
                    searchInput.value = `${guest.name} (${guest.email})`;
                })
                .catch(console.error);
        }

        // Date picker functionality
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');

        // Set minimum check-out date based on check-in date
        if (checkInInput) {
            // Set initial minimum dates
            const today = new Date().toISOString().split('T')[0];
            checkInInput.min = today;
            
            // If check-in has a value, set the minimum check-out date
            if (checkInInput.value) {
                const nextDay = new Date(checkInInput.value);
                nextDay.setDate(nextDay.getDate() + 1);
                const nextDayFormatted = nextDay.toISOString().split('T')[0];
                if (checkOutInput) {
                    checkOutInput.min = nextDayFormatted;
                }
            }
            
            checkInInput.addEventListener('change', function() {
                const checkInDate = new Date(this.value);
                const nextDay = new Date(checkInDate);
                nextDay.setDate(checkInDate.getDate() + 1);
                
                // Format date as YYYY-MM-DD
                const nextDayFormatted = nextDay.toISOString().split('T')[0];
                if (checkOutInput) {
                    checkOutInput.min = nextDayFormatted;
                    if (checkOutInput.value && new Date(checkOutInput.value) < nextDay) {
                        checkOutInput.value = nextDayFormatted;
                    }
                }
            });
            
            // Set initial minimum date for check-out
            if (checkOutInput) {
                checkOutInput.min = today;
            }
        }
    });
</script>
@endpush
@endsection
