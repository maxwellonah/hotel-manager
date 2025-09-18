@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Complete Guest Check-out</h2>
                    <a href="{{ route('check-out.index') }}" class="text-blue-600 hover:text-blue-800">
                        &larr; Back to Check-outs
                    </a>
                </div>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Guest & Stay Information
                        </h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                        <dl class="sm:divide-y sm:divide-gray-200">
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Guest Name
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $booking->user->name }}
                                </dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Room
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $booking->room->room_number }} ({{ $booking->room->roomType->name }})
                                </dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Stay Duration
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ \Carbon\Carbon::parse($booking->check_in)->format('M j, Y') }} to 
                                    {{ \Carbon\Carbon::parse($booking->check_out)->format('M j, Y') }}
                                    ({{ \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }} nights)
                                </dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Check-in Time
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ \Carbon\Carbon::parse($booking->check_in)->format('M j, Y g:i A') }}
                                </dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Check-out Time
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ now()->format('M j, Y g:i A') }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <form action="{{ route('check-out.complete', $booking) }}" method="POST" id="checkoutForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Billing Summary
                            </h3>
                        </div>
                        <div class="border-t border-gray-200">
                            <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Description
                                </dt>
                                <dt class="text-sm font-medium text-gray-500 text-right">
                                    Amount
                                </dt>
                                <dt class="hidden"><!-- Empty for grid layout --></dt>
                            </div>
                            
                            <!-- Room Charges -->
                            <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-900">
                                    Room Charges
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:text-right">
                                    ₦{{ number_format($booking->total_price, 2) }}
                                </dd>
                                <dd class="hidden"><!-- Empty for grid layout --></dd>
                            </div>
                            
                            <!-- Additional Charges -->
                            <div id="additionalCharges">
                                @foreach($additionalCharges as $index => $charge)
                                    <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 charge-row">
                                        <div>
                                            <input type="text" name="additional_charges[{{ $index }}][description]" 
                                                value="{{ $charge['description'] }}" 
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Description">
                                        </div>
                                        <div class="mt-2 sm:mt-0 sm:flex sm:justify-end">
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                    <span class="text-gray-500 sm:text-sm">$</span>
                                                </div>
                                                <input type="number" step="0.01" 
                                                    name="additional_charges[{{ $index }}][amount]" 
                                                    value="{{ number_format($charge['amount'], 2) }}" 
                                                    class="block w-full rounded-md border-gray-300 pl-7 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="mt-2 sm:mt-0 sm:flex sm:items-center sm:justify-end">
                                            <button type="button" class="text-red-600 hover:text-red-900 remove-charge">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Add Charge Button -->
                            <div class="bg-gray-50 px-4 py-3 sm:px-6">
                                <button type="button" id="addCharge" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                    + Add Additional Charge
                                </button>
                            </div>
                            
                            <!-- Subtotal -->
                            <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-t border-gray-200">
                                <dt class="text-sm font-medium text-gray-900">
                                    Subtotal
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:text-right font-medium" id="subtotal">
                                    ₦{{ number_format($booking->total_price, 2) }}
                                </dd>
                                <dd class="hidden"><!-- Empty for grid layout --></dd>
                            </div>
                            
                            <!-- Total -->
                            <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-t border-gray-200 bg-gray-50">
                                <dt class="text-base font-medium text-gray-900">
                                    Total Amount Due
                                </dt>
                                <dd class="mt-1 text-base text-gray-900 sm:mt-0 sm:text-right font-bold" id="total">
                                    ₦{{ number_format($booking->total_price, 2) }}
                                </dd>
                                <dd class="hidden"><!-- Empty for grid layout --></dd>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Additional Notes
                            </h3>
                        </div>
                        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <div class="mt-1">
                                    <textarea id="notes" name="notes" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"></textarea>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">
                                    Any additional notes about the guest's stay or check-out.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex justify-end">
                        <a href="{{ route('check-out.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Complete Check-out
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add new charge row
        document.getElementById('addCharge').addEventListener('click', function() {
            const container = document.getElementById('additionalCharges');
            const index = document.querySelectorAll('.charge-row').length;
            
            const newRow = `
                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 charge-row border-t border-gray-200">
                    <div>
                        <input type="text" name="additional_charges[${index}][description]" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Description">
                    </div>
                    <div class="mt-2 sm:mt-0 sm:flex sm:justify-end">
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" step="0.01" 
                                name="additional_charges[${index}][amount]" 
                                class="block w-full rounded-md border-gray-300 pl-7 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="0.00">
                        </div>
                    </div>
                    <div class="mt-2 sm:mt-0 sm:flex sm:items-center sm:justify-end">
                        <button type="button" class="text-red-600 hover:text-red-900 remove-charge">
                            Remove
                        </button>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', newRow);
            updateTotals();
        });
        
        // Remove charge row
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-charge')) {
                e.target.closest('.charge-row').remove();
                updateTotals();
            }
        });
        
        // Update totals when charge amounts change
        document.addEventListener('input', function(e) {
            if (e.target && e.target.name && e.target.name.includes('additional_charges') && e.target.name.includes('amount')) {
                updateTotals();
            }
        });
        
        // Calculate and update totals (no tax)
        function updateTotals() {
            let subtotal = {{ $booking->total_price }};
            
            // Add up additional charges
            document.querySelectorAll('input[name^="additional_charges["][name$="[amount]"]').forEach(input => {
                const amount = parseFloat(input.value) || 0;
                subtotal += amount;
            });
            
            const total = subtotal;
            
            // Update the display
            document.getElementById('subtotal').textContent = '₦' + subtotal.toFixed(2);
            document.getElementById('total').textContent = '₦' + total.toFixed(2);
        }
    });
</script>
@endpush
@endsection
