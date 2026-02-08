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
                <form id="bookingForm" method="POST" action="{{ route('bookings.store') }}">
                    @csrf
                    <div id="formErrors" class="hidden mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <p class="font-bold">{{ __('Please fix the following errors:') }}</p>
                        <ul id="errorList" class="list-disc ml-5"></ul>
                    </div>

                    @if(in_array(auth()->user()->role, ['admin', 'receptionist']))
                        <input type="hidden" name="is_guest_booking" value="1">
                        <div class="mb-4">
                            <label for="guest_search" class="block text-gray-700 text-sm font-bold mb-2">
                                {{ __('Guest') }}
                            </label>
                            <div class="guest-search-container">
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
                                <div class="mt-2">
                                    <button type="button" id="openCreateGuestModal" class="text-sm text-indigo-600 hover:text-indigo-800">
                                        + {{ __('Create new guest') }}
                                    </button>
                                </div>
                            </div>
                            @error('user_id')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <!-- Identification (Shown if selected/current user has no ID saved) -->
                    <div id="identificationSection" class="mb-4 hidden">
                        <h3 class="text-md font-semibold text-gray-800 mb-2">Guest Identification</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="identification_type" class="block text-gray-700 text-sm font-bold mb-2">
                                    {{ __('ID Type') }} <span class="text-red-500">*</span>
                                </label>
                                <select id="identification_type" name="identification_type" class="form-select w-full">
                                    <option value="">{{ __('Select ID Type') }}</option>
                                    <option value="passport">{{ __('Passport') }}</option>
                                    <option value="id_card">{{ __('National ID Card') }}</option>
                                    <option value="national_id">{{ __('National ID') }}</option>
                                    <option value="driving_license">{{ __('Driver\'s License') }}</option>
                                </select>
                                @error('identification_type')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="identification_number" class="block text-gray-700 text-sm font-bold mb-2">
                                    {{ __('ID/Passport Number') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="identification_number" name="identification_number" class="form-input w-full" value="{{ old('identification_number') }}">
                                @error('identification_number')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">{{ __('We require a valid identification document for all bookings.') }}</p>
                    </div>

                    <div class="mb-4">
                        <label for="room_type_id" class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('Room Type') }}
                        </label>
                        <select id="room_type_id" class="form-select w-full @error('room_type_id') border-red-500 @enderror" 
                                name="room_type_id" required>
                            <option value="">{{ __('Select a room type') }}</option>
                            @foreach($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}" {{ old('room_type_id') == $roomType->id ? 'selected' : '' }}>
                                    {{ $roomType->name }} - ₦{{ number_format($roomType->price_per_night, 2) }}/night 
                                    ({{ $roomType->capacity }} {{ Str::plural('person', $roomType->capacity) }})
                                </option>
                            @endforeach
                        </select>
                        @error('room_type_id')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4 md:flex md:space-x-4">
                        <div class="mb-4">
                            <label for="check_in" class="block text-gray-700 text-sm font-bold mb-2">
                                {{ __('Check-in Date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   id="check_in" 
                                   name="check_in" 
                                   class="form-input w-full @error('check_in') border-red-500 @enderror" 
                                   value="{{ old('check_in') }}" 
                                   required
                                   aria-required="true"
                                   aria-describedby="check_in_help">
                            @error('check_in')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                            <p id="check_in_help" class="text-xs text-gray-500 mt-1">
                                {{ __('Check-in time is from 2:00 PM') }}
                            </p>
                        </div>

                        <div class="mb-4">
                            <label for="check_out" class="block text-gray-700 text-sm font-bold mb-2">
                                {{ __('Check-out Date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   id="check_out" 
                                   name="check_out" 
                                   class="form-input w-full @error('check_out') border-red-500 @enderror" 
                                   value="{{ old('check_out') }}" 
                                   required
                                   aria-required="true"
                                   aria-describedby="check_out_help">
                            @error('check_out')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                            <p id="check_out_help" class="text-xs text-gray-500 mt-1">
                                {{ __('Check-out time is until 12:00 PM') }}
                            </p>
                        </div>
                    </div>

                    <!-- Early Check-in Option -->
                    <div class="mb-6">
                        <label for="adults" class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('Number of Adults') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="adults" 
                               name="adults" 
                               class="form-input w-full" 
                               value="{{ old('adults', 1) }}" 
                               min="1" 
                               required>
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
                                  name="special_requests" 
                                  class="form-textarea w-full @error('special_requests') border-red-500 @enderror" 
                                  rows="3"
                                  aria-describedby="special_requests_help"
                                  placeholder="{{ __('Any special requests or additional information?') }}">{{ old('special_requests') }}</textarea>
                        @error('special_requests')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                        <p id="special_requests_help" class="text-xs text-gray-500 mt-1">
                            {{ __('Please let us know if you have any special requirements') }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                aria-label="{{ __('Create new booking') }}">
                            <span class="flex items-center">
                                <i class="fas fa-calendar-plus mr-2"></i>
                                {{ __('Create Booking') }}
                            </span>
                        </button>
                        <a href="{{ route('bookings.index') }}" 
                           class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800"
                           aria-label="{{ __('Cancel and return to bookings list') }}">
                            <i class="fas fa-times mr-1"></i> {{ __('Cancel') }}
                        </a>
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
    
    // Function to handle guest selection
    function handleGuestSelection(guest) {
        console.log('Handling guest selection:', guest);
        
        if (!guest || !guest.id) {
            console.error('Invalid guest data provided to handleGuestSelection:', guest);
            return;
        }
        
        // Update the user ID input
        const userIdInput = document.getElementById('user_id');
        if (userIdInput) {
            userIdInput.value = guest.id;
            console.log('Set user_id to:', guest.id);
            
            // Dispatch a change event to trigger any listeners
            try {
                const changeEvent = new Event('change', { 
                    bubbles: true, 
                    cancelable: true 
                });
                userIdInput.dispatchEvent(changeEvent);
                console.log('Dispatched change event on user_id input');
            } catch (error) {
                console.error('Error dispatching change event:', error);
            }
        } else {
            console.error('Could not find user_id input element');
        }
        
        // Update the search input with the guest's name
        const searchInput = document.getElementById('guest_search');
        if (searchInput) {
            searchInput.value = guest.name || '';
            searchInput.setAttribute('aria-expanded', 'false');
            console.log('Updated search input with guest name:', guest.name);
        }
        
        // Hide search results
        const searchResults = document.getElementById('guest_search_results');
        if (searchResults) {
            searchResults.innerHTML = '';
            searchResults.classList.add('hidden');
            console.log('Cleared and hid search results');
        }
        
        // Check if ID is required for this guest
        console.log('Checking ID requirement for guest:', guest.id);
        checkGuestIdRequirement(guest.id);
        
        // Dispatch a custom event that the guest was selected
        const selectedEvent = new CustomEvent('guestSelected', {
            detail: { 
                guestId: guest.id,
                guestData: guest
            },
            bubbles: true
        });
        document.dispatchEvent(selectedEvent);
        
        console.log('Finished handling guest selection for:', guest.name || 'Unknown Guest');
    }
    
    // Function to check if a guest needs to provide ID
    async function checkGuestIdRequirement(guestId) {
        console.log('Checking ID requirement for guest:', guestId);
        
        // Check if this is a guest booking - only then check ID requirements
        const isGuestBooking = document.querySelector('input[name="is_guest_booking"]')?.value === '1';
        if (!isGuestBooking) {
            console.log('Not a guest booking, skipping ID requirement check');
            return false;
        }
        
        const idSection = document.getElementById('identificationSection');
        if (!idSection) {
            console.error('Identification section not found');
            return false;
        }

        // Clear any previous content and errors
        idSection.innerHTML = '';
        
        if (!guestId) {
            console.log('No guest ID provided, hiding ID section');
            idSection.classList.add('hidden');
            
            // Make sure ID fields are not required when no guest is selected
            const idType = document.getElementById('identification_type');
            const idNumber = document.getElementById('identification_number');
            if (idType) {
                idType.required = false;
                idType.removeAttribute('name'); // Remove name to exclude from form submission
            }
            if (idNumber) {
                idNumber.required = false;
                idNumber.removeAttribute('name'); // Remove name to exclude from form submission
            }
            
            return false;
        }
        
        // Show loading state
        idSection.innerHTML = `
            <div class="flex items-center justify-center p-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-3"></div>
                <span class="text-gray-600">Loading guest information...</span>
            </div>
        `;
        idSection.classList.remove('hidden');
        
        try {
            // Add a timeout to handle cases where the request hangs
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
            
            console.log('Fetching guest data from API...');
            const response = await fetch(`/api/v1/guests/${guestId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                },
                credentials: 'same-origin',
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const guest = await response.json();
            console.log('Received guest data:', guest);
            
            if (!guest) {
                throw new Error('No guest data received from server');
            }
            
            // Debug: Log the exact ID values
            console.log('Guest ID type:', typeof guest.identification_type, 'value:', `'${guest.identification_type}'`);
            console.log('Guest ID number:', typeof guest.identification_number, 'value:', `'${guest.identification_number}'`);
            
            // Check if guest has valid ID information
            const hasValidId = guest.identification_type && 
                             guest.identification_type.toString().trim() !== '' && 
                             guest.identification_number && 
                             guest.identification_number.toString().trim() !== '';
            
            console.log('Has valid ID?', hasValidId);
            
            if (hasValidId) {
                // Hide the section if guest has valid ID
                console.log('Guest has valid ID, hiding ID section');
                idSection.innerHTML = '';
                idSection.classList.add('hidden');
                
                // Make sure ID fields are not required
                const idType = document.getElementById('identification_type');
                const idNumber = document.getElementById('identification_number');
                if (idType) idType.required = false;
                if (idNumber) idNumber.required = false;
                
                return false;
            } else {
                console.log('Guest does not have valid ID, showing ID section');
                
                // Show ID input fields if guest needs to provide ID
                idSection.innerHTML = `
                    <div class="mb-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">ID Verification Required</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="identification_type" class="block text-sm font-medium text-gray-700">
                                    ID Type <span class="text-red-500">*</span>
                                </label>
                                <select id="identification_type" name="identification_type" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        aria-required="true"
                                        aria-describedby="id_type_help">
                                    <option value="">Select ID Type</option>
                                    <option value="passport" {{ old('identification_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                                    <option value="id_card" {{ old('identification_type') == 'id_card' ? 'selected' : '' }}>National ID Card</option>
                                    <option value="national_id" {{ old('identification_type') == 'national_id' ? 'selected' : '' }}>National ID</option>
                                    <option value="driving_license" {{ old('identification_type') == 'driving_license' ? 'selected' : '' }}>Driving License</option>
                                </select>
                                <p id="id_type_help" class="text-xs text-gray-500 mt-1">Select the type of identification</p>
                                @error('identification_type')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="identification_number" class="block text-sm font-medium text-gray-700">
                                    ID/Passport Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="identification_number" 
                                       name="identification_number" 
                                       value="{{ old('identification_number') }}"
                                       required
                                       aria-required="true"
                                       aria-describedby="id_number_help"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <p id="id_number_help" class="text-xs text-gray-500 mt-1">Enter the ID or passport number</p>
                                @error('identification_number')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Please provide valid identification for the guest.</p>
                    </div>
                `;
                
                idSection.classList.remove('hidden');
                
                // Make sure ID fields are required
                const idType = document.getElementById('identification_type');
                const idNumber = document.getElementById('identification_number');
                if (idType) idType.required = true;
                if (idNumber) idNumber.required = true;
                
                return true;
            }
            
        } catch (error) {
            console.error('Error checking guest ID requirement:', error);
            
            // Show error message but still show the ID section to be safe
            idSection.innerHTML = `
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                    <p class="font-bold">Error</p>
                    <p>Could not verify guest ID information. Please enter ID details below.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="identification_type" class="block text-sm font-medium text-gray-700">
                            ID Type <span class="text-red-500">*</span>
                        </label>
                        <select id="identification_type" name="identification_type" required
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select ID Type</option>
                            <option value="passport" {{ old('identification_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                            <option value="national_id" {{ old('identification_type') == 'national_id' ? 'selected' : '' }}>National ID</option>
                            <option value="driving_license" {{ old('identification_type') == 'driving_license' ? 'selected' : '' }}>Driving License</option>
                        </select>
                        @error('identification_type')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="identification_number" class="block text-sm font-medium text-gray-700">
                            ID/Passport Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="identification_number" 
                               name="identification_number" 
                               value="{{ old('identification_number') }}"
                               required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        @error('identification_number')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">We require a valid identification document for all bookings.</p>
            `;
            
            idSection.classList.remove('hidden');
            
            // Make sure ID fields are required when showing error
            const idType = document.getElementById('identification_type');
            const idNumber = document.getElementById('identification_number');
            if (idType) idType.required = true;
            if (idNumber) idNumber.required = true;
            
            return true;
        }
    }
    
    // Function to set ID fields as required or not
    function setIdRequired(required) {
        console.log('Setting ID required:', required);
        
        const identificationSection = document.getElementById('identificationSection');
        if (!identificationSection) {
            console.error('Identification section not found');
            return false;
        }
        
        const idType = document.getElementById('identification_type');
        const idNumber = document.getElementById('identification_number');
        
        if (required) {
            // Show the section
            identificationSection.classList.remove('hidden');
            
            // Set required attributes
            if (idType) {
                idType.required = true;
                idType.setAttribute('aria-required', 'true');
                idType.setAttribute('name', 'identification_type'); // Restore name attribute
            }
            if (idNumber) {
                idNumber.required = true;
                idNumber.setAttribute('aria-required', 'true');
                idNumber.setAttribute('name', 'identification_number'); // Restore name attribute
            }
            
            // Ensure the section is visible
            identificationSection.classList.remove('hidden');
            
            console.log('ID fields are now required');
        } else {
            // Hide the section
            identificationSection.classList.add('hidden');
            
            // Clear required attributes
            if (idType) {
                idType.required = false;
                idType.removeAttribute('aria-required');
            }
            if (idNumber) {
                idNumber.required = false;
                idNumber.removeAttribute('aria-required');
            }
            
            // Clear any validation messages
            const errorMessages = identificationSection.querySelectorAll('.text-red-500');
            errorMessages.forEach(msg => msg.remove());
            
            // Clear any validation states
            if (idType) idType.classList.remove('border-red-500');
            if (idNumber) idNumber.classList.remove('border-red-500');
            
            console.log('ID fields are now optional');
        }
        
        // Trigger a custom event that other parts of the code can listen to
        const event = new CustomEvent('idRequirementChange', { 
            detail: { required } 
        });
        document.dispatchEvent(event);
    }
    
    // Function to select an option from search results
    function selectOption(option) {
        console.log('Selecting option:', option);
        
        // Get the guest data from the option
        const guestId = option.getAttribute('data-id');
        let guestData;
        
        try {
            guestData = JSON.parse(option.getAttribute('data-guest'));
        } catch (e) {
            console.error('Error parsing guest data:', e);
            return;
        }
        
        if (!guestData) {
            console.error('No guest data found in option');
            return;
        }
        
        console.log('Selected guest data:', guestData);
        
        // Get all necessary DOM elements
        const userIdInput = document.getElementById('user_id');
        const searchInput = document.getElementById('guest_search');
        const searchResults = document.getElementById('guest_search_results');
        
        // Update the hidden input with the guest ID
        if (userIdInput) {
            userIdInput.value = guestId;
            console.log('Set user_id to:', guestId);
            
            // Trigger change event to update any listeners
            try {
                const changeEvent = new Event('change', { bubbles: true, cancelable: true });
                userIdInput.dispatchEvent(changeEvent);
                console.log('Dispatched change event on user_id input');
            } catch (error) {
                console.error('Error dispatching change event:', error);
            }
        }
        
        // Update the search input with the guest's name and email
        if (searchInput) {
            const displayValue = guestData.name && guestData.email ? `${guestData.name} (${guestData.email})` : (guestData.name || '');
            searchInput.value = displayValue;
            searchInput.setAttribute('aria-expanded', 'false');
            console.log('Set search input to:', displayValue);
        }
        
        // Pre-fill identification fields if guest has valid ID data
        if (guestData.identification_type && guestData.identification_number) {
            const idTypeField = document.getElementById('identification_type');
            const idNumberField = document.getElementById('identification_number');
            
            if (idTypeField) {
                idTypeField.value = guestData.identification_type;
                console.log('Set ID type to:', guestData.identification_type);
            }
            if (idNumberField) {
                idNumberField.value = guestData.identification_number;
                console.log('Set ID number to:', guestData.identification_number);
            }
        }
        
        // Hide search results
        if (searchResults) {
            searchResults.innerHTML = '';
            searchResults.classList.add('hidden');
        }
        
        // Focus the search input
        if (searchInput) {
            searchInput.focus();
        }
        
        // Check if ID is required for this guest
        console.log('Checking ID requirement for selected guest:', guestId);
        checkGuestIdRequirement(guestId);
        
        // Dispatch a custom event that the guest was selected
        const selectedEvent = new CustomEvent('guestSelected', {
            detail: { 
                guestId: guestId,
                guestData: guestData
            },
            bubbles: true
        });
        document.dispatchEvent(selectedEvent);
    }
    
    // Function to add error messages to the form
    function addError(message) {
        const errorList = document.getElementById('errorList');
        if (!errorList) return;
        
        const li = document.createElement('li');
        li.textContent = message;
        errorList.appendChild(li);
    }
    
    // Function to validate the form
    async function validateForm() {
        console.log('Validating form...');
        const errorList = document.getElementById('errorList');
        const formErrors = document.getElementById('formErrors');
        
        if (errorList) errorList.innerHTML = '';
        if (formErrors) formErrors.classList.add('hidden');
        
        let isValid = true;
        const errors = [];
        
        // Check if user is selected (for admin/receptionist)
        const userIdInput = document.getElementById('user_id');
        if (userIdInput && userIdInput.required && !userIdInput.value) {
            errors.push('Please select a guest');
            isValid = false;
        }
        
        // Check room type selection
        const roomTypeSelect = document.getElementById('room_type_id');
        if (roomTypeSelect && !roomTypeSelect.value) {
            errors.push('Please select a room type');
            isValid = false;
        }
        
        // Check dates
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        
        if (checkInInput && !checkInInput.value) {
            errors.push('Please select a check-in date');
            isValid = false;
        }
        
        if (checkOutInput && !checkOutInput.value) {
            errors.push('Please select a check-out date');
            isValid = false;
        }
        
        // Check ID requirements if section is visible
        const idSection = document.getElementById('identificationSection');
        if (idSection && !idSection.classList.contains('hidden')) {
            const idType = document.getElementById('identification_type');
            const idNumber = document.getElementById('identification_number');
            
            if (idType && !idType.value) {
                errors.push('Please select an ID type');
                isValid = false;
            }
            
            if (idNumber && !idNumber.value) {
                errors.push('Please enter an ID number');
                isValid = false;
            }
        }
        
        // Display errors if any
        if (errors.length > 0) {
            errors.forEach(error => addError(error));
            if (formErrors) formErrors.classList.remove('hidden');
            
            // Scroll to the top of the form to show errors
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        return isValid;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Add form submission handler
        const form = document.getElementById('bookingForm');
        if (form) {
            form.addEventListener('submit', async function(e) {
                console.log('Form submission started');
                
                // Check if this is a guest booking
                const isGuestBooking = document.querySelector('input[name="is_guest_booking"]')?.value === '1';
                const userIdInput = document.getElementById('user_id');
                const guestId = userIdInput ? userIdInput.value : null;
                
                // If it's a guest booking and no user is selected, show error
                if (isGuestBooking && !guestId) {
                    e.preventDefault();
                    addError('Please select a guest');
                    const formErrors = document.getElementById('formErrors');
                    if (formErrors) formErrors.classList.remove('hidden');
                    return false;
                }
                
                // For guest bookings, check if ID is required
                if (isGuestBooking && guestId) {
                    try {
                        // Get the ID section and fields
                        const idSection = document.getElementById('identificationSection');
                        const idType = document.getElementById('identification_type');
                        const idNumber = document.getElementById('identification_number');
                        
                        // If ID section is visible, validate the fields
                        if (idSection && !idSection.classList.contains('hidden')) {
                            console.log('ID section is visible, validating fields...');
                            
                            // Check if either field is empty
                            if (!idType?.value || !idNumber?.value) {
                                e.preventDefault();
                                addError('Please provide both ID type and ID number');
                                const formErrors = document.getElementById('formErrors');
                                if (formErrors) formErrors.classList.remove('hidden');
                                
                                // Highlight the missing fields
                                if (!idType?.value) {
                                    idType.classList.add('border-red-500');
                                }
                                if (!idNumber?.value) {
                                    idNumber.classList.add('border-red-500');
                                }
                                
                                // Scroll to the ID section
                                if (idSection) {
                                    idSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    idSection.classList.add('border-2', 'border-red-500', 'p-2', 'rounded');
                                }
                                
                                return false;
                            }
                            
                            // If we get here, the ID fields are filled, so we can proceed
                            console.log('ID fields are valid, proceeding with submission');
                        } else {
                            console.log('ID section is hidden, no ID validation needed');
                        }
                    } catch (error) {
                        console.error('Error during form validation:', error);
                        // Continue with form submission if there's an error
                    }
                }
                
                // Validate the form
                const isValid = await validateForm();
                if (!isValid) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                
                // Show loading state
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    const originalText = submitButton.textContent;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                    
                    // Re-enable the button after 10 seconds in case submission fails silently
                    setTimeout(() => {
                        if (submitButton.disabled) {
                            submitButton.disabled = false;
                            submitButton.textContent = originalText;
                            const formErrors = document.getElementById('formErrors');
                            if (formErrors) formErrors.classList.remove('hidden');
                            addError('Submission timed out. Please try again.');
                        }
                    }, 10000);
                }
                
                // If we got here, the form is valid and can be submitted
                return true;
            });
        }

        // Initialize variables
        const userIdInput = document.getElementById('user_id');
        const searchInput = document.getElementById('guest_search');
        const searchResults = document.getElementById('guest_search_results');
        
        console.log('Search input element:', searchInput);
        console.log('User ID input element:', userIdInput);
        console.log('Search results element:', searchResults);
        
        // Check-in date handling
        const checkInInput = document.getElementById('check_in');
        if (checkInInput) {
            checkInInput.addEventListener('change', function() {
                const checkInDate = new Date(this.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                // If check-in date is today, show early check-in options
                if (checkInDate.getTime() === today.getTime()) {
                    document.getElementById('early_checkin_fields').classList.remove('hidden');
                } else {
                    document.getElementById('early_checkin_fields').classList.add('hidden');
                    document.getElementById('early_checkin').checked = false;
                }
            });
        }
        
        // --- ID Requirement Logic ---
        const identificationSection = document.getElementById('identificationSection');
        const idType = document.getElementById('identification_type');
        const idNumber = document.getElementById('identification_number');
        
        // Function to show/hide ID fields based on requirement
        function setIdRequired(required) {
            if (!idType || !idNumber || !identificationSection) return;
            
            if (required) {
                identificationSection.classList.remove('hidden');
                idType.required = true;
                idNumber.required = true;
            } else {
                identificationSection.classList.add('hidden');
                idType.required = false;
                idNumber.required = false;
                
                // Clear validation messages when hiding
                const errorElements = identificationSection.querySelectorAll('.text-red-500');
                errorElements.forEach(el => el.remove());
            }
            
            idType.required = required;
            idNumber.required = required;
            identificationSection.classList.toggle('hidden', !required);
            
            // Clear validation messages when hiding
            if (!required) {
                const errorElements = identificationSection.querySelectorAll('.text-red-500');
                errorElements.forEach(el => el.remove());
            }
        }

        // Check if this is a self-booking (non-guest booking)
        const isSelfBooking = @json(!old('is_guest_booking') && empty(request()->input('is_guest_booking')));
        
        // If self-booking, check if current user needs to provide ID
        if (isSelfBooking) {
            const currentUserHasId = @json(isset($currentUserHasId) ? $currentUserHasId : false);
            setIdRequired(!currentUserHasId);
        }

        // When a guest is selected from search
        if (userIdInput) {
            // Handle changes to the user selection
            const handleUserChange = () => {
                const guestId = userIdInput.value;
                console.log('User ID changed:', guestId);
                if (guestId) {
                    checkGuestIdRequirement(guestId);
                } else {
                    setIdRequired(false);
                }
            };
            
            // Set up observer for changes to the user ID input
            const observer = new MutationObserver(handleUserChange);
            observer.observe(userIdInput, { 
                attributes: true, 
                attributeFilter: ['value'] 
            });
            
            // Also check on page load if a guest is already selected
            if (userIdInput.value) {
                console.log('Initial user ID found:', userIdInput.value);
                handleUserChange();
            }
            
            // Handle search input and results
            if (searchInput && searchResults) {
                console.log('Setting up search result click handler');
                // Handle click on search results
                searchResults.addEventListener('click', function(e) {
                    console.log('Search results clicked');
                    const resultItem = e.target.closest('.search-result-item');
                    if (resultItem) {
                        console.log('Search result item clicked');
                        selectOption(resultItem);
                    }
                });
                
                // Handle keyboard navigation in search results
                searchResults.addEventListener('keydown', function(e) {
                    const active = document.activeElement;
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        if (active.classList.contains('search-result-item')) {
                            console.log('Search result item selected via keyboard');
                            selectOption(active);
                        }
                    }
                });
            }
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
                const url = `/api/v1/guests/search?search=${encodeURIComponent(searchTerm)}`;
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
                            searchResults.innerHTML = `
                                <div class="p-2 text-gray-500">No guests found</div>
                                <div class="p-2">
                                    <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800" id="createGuestFromResults">
                                        + {{ __('Create new guest') }}
                                    </button>
                                </div>
                            `;
                            showSearchResults();
                            const createBtn = document.getElementById('createGuestFromResults');
                            if (createBtn) {
                                createBtn.addEventListener('click', function() {
                                    openGuestModal();
                                });
                            }
                            return;
                        }

                        // Clear previous results
                        searchResults.innerHTML = '';
                        
                        // Create result items
                        guests.forEach(guest => {
                            const resultItem = document.createElement('div');
                            resultItem.className = 'search-result-item p-2 hover:bg-gray-100 cursor-pointer';
                            resultItem.textContent = `${guest.name} (${guest.email})`;
                            resultItem.setAttribute('data-id', guest.id);
                            // Store the full guest data in a data attribute
                            resultItem.setAttribute('data-guest', JSON.stringify(guest));
                            searchResults.appendChild(resultItem);
                        });
                        currentFocus = -1;
                        showSearchResults();
                        
                        // Set up keyboard navigation
                        const options = searchResults.querySelectorAll('.search-result-item');
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
            fetch(`/api/v1/guests/${userIdInput.value}`)
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
        // Create Guest modal handlers
        const openCreateGuestModalBtn = document.getElementById('openCreateGuestModal');
        if (openCreateGuestModalBtn) {
            openCreateGuestModalBtn.addEventListener('click', openGuestModal);
        }

        function openGuestModal() {
            const modal = document.getElementById('createGuestModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeGuestModal() {
            const modal = document.getElementById('createGuestModal');
            if (modal) modal.classList.add('hidden');
        }

        window.openGuestModal = openGuestModal;
        window.closeGuestModal = closeGuestModal;

        const createGuestForm = document.getElementById('createGuestForm');
        if (createGuestForm) {
            createGuestForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const name = (document.getElementById('new_guest_name') || {}).value || '';
                const email = (document.getElementById('new_guest_email') || {}).value || '';
                const phone = (document.getElementById('new_guest_phone') || {}).value || '';

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                try {
                    const res = await fetch('{{ route('guests.quick-create') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ name, email, phone })
                    });
                    if (!res.ok) {
                        const text = await res.text();
                        throw new Error(text || 'Failed to create guest');
                    }
                    const guest = await res.json();
                    // Auto-select created guest
                    userIdInput.value = guest.id;
                    searchInput.value = `${guest.name} (${guest.email})${guest.phone ? ' - ' + guest.phone : ''}`;
                    hideSearchResults();
                    closeGuestModal();
                } catch (err) {
                    console.error('Create guest error:', err);
                    alert('Failed to create guest. Please check the details and try again.');
                }
            });
        }
    });
</script>

<!-- Inline Create Guest Modal -->
<div id="createGuestModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden" aria-hidden="true">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold">{{ __('Create New Guest') }}</h3>
            <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeGuestModal()" aria-label="Close">✕</button>
        </div>
        <form id="createGuestForm" class="px-6 py-4">
            <div class="mb-4">
                <label for="new_guest_name" class="block text-sm font-medium text-gray-700">{{ __('Full Name') }}</label>
                <input type="text" id="new_guest_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required>
            </div>
            <div class="mb-4">
                <label for="new_guest_email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                <input type="email" id="new_guest_email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required>
            </div>
            <div class="mb-6">
                <label for="new_guest_phone" class="block text-sm font-medium text-gray-700">{{ __('Phone (optional)') }}</label>
                <input type="tel" id="new_guest_phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
            </div>
            <div class="flex items-center justify-end space-x-3">
                <button type="button" class="px-4 py-2 rounded border border-gray-300 bg-white text-gray-700" onclick="closeGuestModal()">{{ __('Cancel') }}</button>
                <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">{{ __('Create & Select') }}</button>
            </div>
        </form>
    </div>
</div>
@endpush
@endsection
