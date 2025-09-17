@extends('layouts.app')

@push('scripts')
<script>
    function processEarlyCheckin(bookingId) {
        if (confirm('Are you sure you want to process early check-in for this booking?')) {
            const button = document.getElementById('early-checkin-btn');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            
            fetch(`/bookings/${bookingId}/early-checkin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success';
                    alert.role = 'alert';
                    alert.textContent = data.message;
                    document.querySelector('.card-body').prepend(alert);
                    
                    // Update status badge
                    const statusBadge = document.querySelector('.badge');
                    statusBadge.className = 'badge bg-success';
                    statusBadge.textContent = 'Checked In';
                    
                    // Remove the button
                    button.remove();
                    
                    // Reload the page after 2 seconds
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    throw new Error(data.message || 'Failed to process early check-in');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                button.disabled = false;
                button.innerHTML = 'Accept Early Check-in';
            });
        }
    }
</script>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ __('Booking Details') }}
                    <span class="float-end">
                        <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-sm btn-primary">
                            {{ __('Edit') }}
                        </a>
                    </span>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('Booking ID') }}:</div>
                        <div class="col-md-8">#{{ $booking->id }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('Guest Name') }}:</div>
                        <div class="col-md-8">{{ $booking->user->name }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('Room') }}:</div>
                        <div class="col-md-8">
                            {{ $booking->room->room_number }} ({{ $booking->room->roomType->name }})
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('Check-in Date') }}:</div>
                        <div class="col-md-8">{{ \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('Check-out Date') }}:</div>
                        <div class="col-md-8">{{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('Total Price') }}:</div>
                        <div class="col-md-8">${{ number_format($booking->total_price, 2) }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('Status') }}:</div>
                        <div class="col-md-8">
                            <span class="badge bg-{{ in_array($booking->status, ['confirmed', 'checked_in']) ? 'success' : 'warning' }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                            @if($booking->is_early_checkin)
                                <span class="badge bg-info ms-2">Early Check-in</span>
                            @endif
                        </div>
                    </div>

                    @if($booking->special_requests)
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">{{ __('Special Requests') }}:</div>
                            <div class="col-md-8">{{ $booking->special_requests }}</div>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-12 d-flex justify-content-between">
                            <a href="{{ route('bookings.index') }}" class="btn btn-secondary">
                                {{ __('Back to Bookings') }}
                            </a>
                            
                            @if($booking->canCheckInEarly())
                                <button type="button" 
                                        id="early-checkin-btn"
                                        class="btn btn-primary"
                                        onclick="processEarlyCheckin({{ $booking->id }})">
                                    <i class="fas fa-sign-in-alt me-1"></i> Accept Early Check-in
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
