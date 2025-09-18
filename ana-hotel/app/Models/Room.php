<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'room_number',
        'room_type_id',
        'housekeeping_user_id',
        'floor',
        'status',
        'description',
        'amenities',
        'image_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price_per_night' => 'decimal:2',
        'capacity' => 'integer',
    ];

    /**
     * Get the room type that owns the room.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
    
    /**
     * Get the bookings for the room.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    
    /**
     * Get the housekeeping tasks for the room.
     */
    public function housekeepingTasks()
    {
        return $this->hasMany(HousekeepingTask::class);
    }

    /**
     * Get the assigned housekeeper (user) for this room.
     */
    public function assignedHousekeeper()
    {
        return $this->belongsTo(User::class, 'housekeeping_user_id');
    }

    /**
     * Scope a query to only include available rooms.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Check if the room is currently available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }
    
    /**
     * Check if the room is available for the given date range.
     *
     * @param  string|\Carbon\Carbon  $checkIn
     * @param  string|\Carbon\Carbon  $checkOut
     * @param  int|null  $ignoreBookingId  Booking ID to exclude from the check
     * @return bool
     */
    public function isAvailableForDates($checkIn, $checkOut, $ignoreBookingId = null)
    {
        return !$this->hasActiveBookings($checkIn, $checkOut, $ignoreBookingId);
    }
    
    /**
     * Check if the room has any active bookings for the given date range.
     *
     * @param  string|\Carbon\Carbon  $checkIn
     * @param  string|\Carbon\Carbon  $checkOut
     * @param  int|null  $ignoreBookingId  Booking ID to exclude from the check
     * @return bool
     */
    public function hasActiveBookings($checkIn, $checkOut, $ignoreBookingId = null)
    {
        // Ensure dates are Carbon instances
        $checkIn = $checkIn instanceof \Carbon\Carbon ? $checkIn : \Carbon\Carbon::parse($checkIn);
        $checkOut = $checkOut instanceof \Carbon\Carbon ? $checkOut : \Carbon\Carbon::parse($checkOut);
        
        return $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->when($ignoreBookingId, function($query) use ($ignoreBookingId) {
                $query->where('id', '!=', $ignoreBookingId);
            })
            ->where(function($query) use ($checkIn, $checkOut) {
                $query->where(function($q) use ($checkIn, $checkOut) {
                    // Check for date overlap
                    $q->where('check_in', '<', $checkOut->format('Y-m-d'))
                      ->where('check_out', '>', $checkIn->format('Y-m-d'));
                });
            })
            ->exists();
    }
    
    /**
     * Get all active bookings for the room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activeBookings()
    {
        return $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'checked_out')
            ->where('check_out', '>=', now()->format('Y-m-d'))
            ->orderBy('check_in');
    }
    
    /**
     * Get the current booking for the room if it's occupied.
     *
     * @return \App\Models\Booking|null
     */
    public function getCurrentBooking()
    {
        return $this->bookings()
            ->where('status', 'checked_in')
            ->where('check_out', '>=', now()->format('Y-m-d'))
            ->orderBy('check_in', 'desc')
            ->first();
    }
}
