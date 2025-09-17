<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HousekeepingTask extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'room_id',
        'assigned_to',
        'assigned_by',
        'task_type',
        'status',
        'priority',
        'description',
        'due_date',
        'completed_at',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * The possible task statuses.
     *
     * @var array
     */
    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ];

    /**
     * The possible task priorities.
     *
     * @var array
     */
    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent'
    ];

    /**
     * The possible task types.
     *
     * @var array
     */
    public const TYPES = [
        'cleaning' => 'Room Cleaning',
        'inspection' => 'Room Inspection',
        'maintenance' => 'Maintenance',
        'deep_cleaning' => 'Deep Cleaning',
        'other' => 'Other'
    ];

    /**
     * Get the room that the task is for.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the staff member assigned to the task.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the staff member who assigned the task.
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope a query to only include pending tasks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed tasks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include high priority tasks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    /**
     * Check if the task is overdue.
     *
     * @return bool
     */
    public function isOverdue()
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    /**
     * Mark the task as completed.
     *
     * @param  string  $notes
     * @return bool
     */
    public function markAsCompleted($notes = null)
    {
        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $notes ?: $this->notes
        ]);
    }
}