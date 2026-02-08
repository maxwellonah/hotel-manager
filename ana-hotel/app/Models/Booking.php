<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'room_id',
        'booking_reference',
        'check_in',
        'check_out',
        'adults',
        'children',
        'status',
        'special_requests',
        'payment_method',
        'transaction_id',
        'checked_in_at',
        'checked_out_at',
        'cancelled_at',
        'is_early_checkin',
        'payment_confirmed_at',
        'total_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'check_in'     => 'date',
        'check_out'    => 'date',
        'total_price'  => 'decimal:2',
        'adults'       => 'integer',
        'children'     => 'integer',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BOOK' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Dates that should be mutated to Carbon instances.
     *
     * @var array
     */
    protected $dates = [
        'check_in',
        'check_out',
        'created_at',
        'updated_at',
        'checked_in_at',
        'checked_out_at',
        'cancelled_at',
        'payment_confirmed_at',
    ];

    /**
     * The possible booking statuses.
     *
     * @var array
     */
    public const STATUSES = [
        'pending'     => 'Pending',
        'confirmed'   => 'Confirmed',
        'checked_in'  => 'Checked In',
        'checked_out' => 'Checked Out',
        'cancelled'   => 'Cancelled',
        'no_show'     => 'No Show',
    ];

    /**
     * The possible payment statuses.
     *
     * @var array
     */
    public const PAYMENT_STATUSES = [
        'pending'        => 'Pending',
        'paid'           => 'Paid',
        'partially_paid' => 'Partially Paid',
        'refunded'       => 'Refunded',
        'cancelled'      => 'Cancelled',
    ];

    /**
     * Get the user that owns the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the room that the booking is for.
     */
    public function room()
    {
        return $this->belongsTo(Room::class)->with('roomType');
    }
    
    /**
     * Get all payments for the booking.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the services for the booking.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_services')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include upcoming bookings.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('check_in', '>=', now())
                     ->whereIn('status', ['confirmed']);
    }

    /**
     * Scope a query to only include current bookings.
     */
    public function scopeCurrent($query)
    {
        $today = now()->format('Y-m-d');

        return $query->where(function($q) use ($today) {
                $q->where('check_in', '<=', $today)
                  ->where('check_out', '>=', $today);
            })
            ->whereIn('status', ['confirmed', 'checked_in']);
    }

    /**
     * Scope a query to only include active room bookings (current and future).
     */
    public function scopeActiveRoomBookings($query, $roomId = null)
    {
        $today = now()->format('Y-m-d');
        
        $query = $query->where('check_out', '>=', $today)
                      ->whereIn('status', ['confirmed', 'checked_in']);
        
        if ($roomId) {
            $query->where('room_id', $roomId);
        }
        
        return $query;
    }
    
    /**
     * Check if the booking is for a future date.
     */
    public function isFutureBooking(): bool
    {
        return now()->lt(Carbon::parse($this->check_in));
    }
    
    /**
     * Check if the booking can be checked in early.
     */
    public function canCheckInEarly(): bool
    {
        return $this->status === 'confirmed' && 
               $this->isFutureBooking() &&
               $this->payment_status === 'paid';
    }
    
    /**
     * Process early check-in for a future booking.
     */
    public function processEarlyCheckIn()
    {
        if (!$this->canCheckInEarly()) {
            return false;
        }
        
        $this->status = 'checked_in';
        $this->is_early_checkin = true;
        $this->checked_in_at = now();
        
        return $this->save();
    }

    /**
     * Check if the booking is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['confirmed', 'checked_in']);
    }

    /**
     * Calculate the number of nights for the booking.
     */
    public function getNightsAttribute(): int
    {
        if (empty($this->check_in) || empty($this->check_out)) {
            return 0;
        }

        return Carbon::parse($this->check_in)
                     ->diffInDays(Carbon::parse($this->check_out));
    }
}
