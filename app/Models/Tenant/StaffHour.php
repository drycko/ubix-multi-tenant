<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsTenantUserActivity;

class StaffHour extends Model
{
    use HasFactory, LogsTenantUserActivity;

    protected $fillable = [
        'user_id',
        'property_id',
        'task_type',
        'task_id',
        'work_type',
        'description',
        'hours_worked',
        'hourly_rate',
        'total_amount',
        'work_date',
        'start_time',
        'end_time',
        'is_overtime',
        'is_approved',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'hours_worked' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'work_date' => 'date',
        'is_overtime' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Constants
    public const WORK_TYPE_MAINTENANCE = 'maintenance';
    public const WORK_TYPE_HOUSEKEEPING = 'housekeeping';
    public const WORK_TYPE_INSPECTION = 'inspection';
    public const WORK_TYPE_ADMINISTRATIVE = 'administrative';
    public const WORK_TYPE_OTHER = 'other';

    const WORK_TYPES = [
        self::WORK_TYPE_MAINTENANCE,
        self::WORK_TYPE_HOUSEKEEPING,
        self::WORK_TYPE_INSPECTION,
        self::WORK_TYPE_ADMINISTRATIVE,
        self::WORK_TYPE_OTHER,
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function task(): MorphTo
    {
        return $this->morphTo();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopeByWorkType($query, $workType)
    {
        return $query->where('work_type', $workType);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeOvertime($query)
    {
        return $query->where('is_overtime', true);
    }

    // Helper methods
    public function calculateTotal(): void
    {
        if ($this->hours_worked && $this->hourly_rate) {
            $this->total_amount = $this->hours_worked * $this->hourly_rate;
            $this->save();
        }
    }

    public function approve($approvedBy): void
    {
        $this->update([
            'is_approved' => true,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    // Boot method to auto-calculate total
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($staffHour) {
            if ($staffHour->hours_worked && $staffHour->hourly_rate) {
                $staffHour->total_amount = $staffHour->hours_worked * $staffHour->hourly_rate;
            }
        });
    }
}