@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit Booking') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('bookings.update', $booking->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group row mb-3">
                            <label for="room_id" class="col-md-4 col-form-label text-md-right">{{ __('Room') }}</label>
                            <div class="col-md-6">
                                <select id="room_id" class="form-control @error('room_id') is-invalid @enderror" name="room_id" required>
                                    <option value="">Select a room</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ $room->id == $booking->room_id ? 'selected' : '' }}>
                                            {{ $room->room_number }} - {{ $room->roomType->name }} ({{ $room->roomType->capacity }} persons)
                                        </option>
                                    @endforeach
                                </select>
                                @error('room_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="user_id" class="col-md-4 col-form-label text-md-right">{{ __('Guest') }}</label>
                            <div class="col-md-6">
                                <select id="user_id" class="form-control @error('user_id') is-invalid @enderror" name="user_id" required>
                                    <option value="">Select a guest</option>
                                    @foreach($guests as $guest)
                                        <option value="{{ $guest->id }}" {{ $guest->id == $booking->user_id ? 'selected' : '' }}>
                                            {{ $guest->name }} ({{ $guest->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="check_in" class="col-md-4 col-form-label text-md-right">{{ __('Check-in Date') }}</label>
                            <div class="col-md-6">
                                <input id="check_in" type="date" class="form-control @error('check_in') is-invalid @enderror" 
                                    name="check_in" value="{{ old('check_in', $booking->check_in) }}" required>
                                @error('check_in')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="check_out" class="col-md-4 col-form-label text-md-right">{{ __('Check-out Date') }}</label>
                            <div class="col-md-6">
                                <input id="check_out" type="date" class="form-control @error('check_out') is-invalid @enderror" 
                                    name="check_out" value="{{ old('check_out', $booking->check_out) }}" required>
                                @error('check_out')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Early Check-in Option -->
                        <div class="form-group row mb-3">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('Early Check-in') }}</label>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           id="is_early_checkin" 
                                           name="is_early_checkin" 
                                           value="1" 
                                           class="form-check-input"
                                           {{ $booking->is_early_checkin ? 'checked' : '' }}
                                           onchange="toggleEarlyCheckinFields()">
                                    <label class="form-check-label" for="is_early_checkin">
                                        {{ __('Allow early check-in') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-right">{{ __('Status') }}</label>
                            <div class="col-md-6">
                                <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
                                    <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="checked_in" {{ $booking->status == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                                    <option value="checked_out" {{ $booking->status == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                                    <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="special_requests" class="col-md-4 col-form-label text-md-right">{{ __('Special Requests') }}</label>
                            <div class="col-md-6">
                                <textarea id="special_requests" class="form-control @error('special_requests') is-invalid @enderror" 
                                    name="special_requests" rows="3">{{ old('special_requests', $booking->special_requests) }}</textarea>
                                @error('special_requests')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Update Booking') }}
                                </button>
                                <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-secondary">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Function to handle early check-in toggle
    function toggleEarlyCheckinFields() {
        const checkInDate = new Date(document.getElementById('check_in').value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const earlyCheckinCheckbox = document.getElementById('is_early_checkin');
        
        // Only allow early check-in if check-in date is in the future
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
        const earlyCheckinCheckbox = document.getElementById('is_early_checkin');
        
        if (checkInInput) {
            checkInInput.addEventListener('change', function() {
                const checkInDate = new Date(this.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                // Enable/disable early check-in based on check-in date
                if (checkInDate > today) {
                    earlyCheckinCheckbox.disabled = false;
                } else {
                    earlyCheckinCheckbox.checked = false;
                    earlyCheckinCheckbox.disabled = true;
                }
            });
            
            // Trigger change event on page load to set initial state
            checkInInput.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush
