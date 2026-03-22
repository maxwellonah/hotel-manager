<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'transaction_reference',
        'amount',
        'payment_method',
        'status',
        'notes',
        'payment_details',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_details' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent duplicate completed payments for same booking
        static::creating(function ($payment) {
            if ($payment->status === self::STATUS_COMPLETED) {
                $existingCompletedPayment = static::where('booking_id', $payment->booking_id)
                    ->where('status', self::STATUS_COMPLETED)
                    ->exists();

                if ($existingCompletedPayment) {
                    throw new \Exception('A completed payment already exists for this booking. Only one completed payment is allowed per booking.');
                }
            }
        });

        // Prevent modification of payment amounts for completed payments
        static::updating(function ($payment) {
            if ($payment->status === self::STATUS_COMPLETED && $payment->isDirty('amount')) {
                $originalAmount = $payment->getOriginal('amount');
                $newAmount = $payment->amount;
                
                \Log::warning('Attempted to modify completed payment amount', [
                    'payment_id' => $payment->id,
                    'booking_id' => $payment->booking_id,
                    'original_amount' => $originalAmount,
                    'attempted_amount' => $newAmount,
                    'user_id' => auth()->id() ?? 'system',
                ]);

                throw new \Exception("Cannot modify the amount of a completed payment. Original amount: ₦{$originalAmount}, Attempted: ₦{$newAmount}. Create a new payment or refund instead.");
            }
        });
    }

    /**
     * The possible payment statuses.
     *
     * @var array
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    /**
     * Get the booking that owns the payment.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
