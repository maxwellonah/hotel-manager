<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'user_agent',
        'ip_address',
        'url',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model instance.
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include logs for a specific model.
     */
    public function scopeForModel($query, $model)
    {
        return $query->where('model_type', get_class($model))
                    ->where('model_id', $model->getKey());
    }

    /**
     * Scope a query to only include logs for a specific action.
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get the old value of a specific attribute.
     */
    public function getOldValue(string $key, $default = null)
    {
        return $this->old_values[$key] ?? $default;
    }

    /**
     * Get the new value of a specific attribute.
     */
    public function getNewValue(string $key, $default = null)
    {
        return $this->new_values[$key] ?? $default;
    }
}
