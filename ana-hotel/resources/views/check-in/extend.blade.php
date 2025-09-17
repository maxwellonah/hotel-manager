@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Extend Guest Stay</h2>
                
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-medium text-gray-700">Current Booking Details</h3>
                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Guest Name</p>
                            <p class="font-medium">{{ $booking->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Room</p>
                            <p class="font-medium">{{ $booking->room->room_number }} ({{ $booking->room->roomType->name }})</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Current Check-out</p>
                            <p class="font-medium">{{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Price per Night</p>
                            <p class="font-medium">${{ number_format($booking->room->roomType->price_per_night, 2) }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('check-in.extend', $booking) }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="additional_nights" class="block text-sm font-medium text-gray-700 mb-1">
                            Additional Nights
                        </label>
                        <input type="number" 
                               name="additional_nights" 
                               id="additional_nights" 
                               min="1" 
                               max="30" 
                               value="1"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                               required>
                        @error('additional_nights')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Notes (Optional)
                        </label>
                        <textarea name="notes" 
                                 id="notes" 
                                 rows="3"
                                 class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('check-in.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            Extend Stay
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to calculate and display the new checkout date and additional cost -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const additionalNightsInput = document.getElementById('additional_nights');
    const currentCheckout = new Date('{{ $booking->check_out }}');
    const pricePerNight = {{ $booking->room->roomType->price_per_night }};
    
    function updateSummary() {
        const additionalNights = parseInt(additionalNightsInput.value) || 0;
        const newCheckout = new Date(currentCheckout);
        newCheckout.setDate(newCheckout.getDate() + additionalNights);
        
        const additionalCost = additionalNights * pricePerNight;
        
        // Update the summary
        document.getElementById('new-checkout').textContent = newCheckout.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        
        document.getElementById('additional-cost').textContent = additionalCost.toFixed(2);
        document.getElementById('new-total').textContent = ({{ $booking->total_price }} + additionalCost).toFixed(2);
    }
    
    additionalNightsInput.addEventListener('input', updateSummary);
    
    // Initial update
    updateSummary();
});
</script>
@endpush

<!-- Add a summary card to show the changes -->
@push('after-content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-gray-50 border-t border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Summary of Changes</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-white rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-500">New Check-out Date</p>
                    <p class="font-medium text-lg" id="new-checkout"></p>
                </div>
                <div class="p-4 bg-white rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-500">Additional Cost</p>
                    <p class="font-medium text-lg">$<span id="additional-cost">0.00</span></p>
                </div>
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm text-blue-600">New Total</p>
                    <p class="font-bold text-lg text-blue-800">$<span id="new-total">{{ number_format($booking->total_price, 2) }}</span></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush
@endsection
