<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsTenantUserActivity;

class RoomStatus extends Model
{
    use HasFactory, LogsTenantUserActivity;

    protected $fillable = [
        'room_id',
        'property_id',
        'status',
        'housekeeping_status',
        'assigned_to',
        'inspected_by',
        'notes',
        'status_changed_at',
        'assigned_at',
        'started_at',
        'completed_at',
        'inspected_at',
    ];

    protected $casts = [
        'status_changed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'inspected_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_DIRTY = 'dirty';
    public const STATUS_CLEAN = 'clean';
    public const STATUS_INSPECTED = 'inspected';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_OUT_OF_ORDER = 'out_of_order';

    public const HOUSEKEEPING_PENDING = 'pending';
    public const HOUSEKEEPING_IN_PROGRESS = 'in_progress';
    public const HOUSEKEEPING_COMPLETED = 'completed';
    public const HOUSEKEEPING_INSPECTED = 'inspected';

    // Relationships
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByHousekeepingStatus($query, $status)
    {
        return $query->where('housekeeping_status', $status);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopePendingWork($query)
    {
        return $query->whereIn('housekeeping_status', [
            self::HOUSEKEEPING_PENDING,
            self::HOUSEKEEPING_IN_PROGRESS
        ]);
    }

    // Helper methods
    public function canBeAssigned(): bool
    {
        return $this->housekeeping_status === self::HOUSEKEEPING_PENDING;
    }

    public function canStart(): bool
    {
        return $this->housekeeping_status === self::HOUSEKEEPING_PENDING && 
               $this->assigned_to !== null;
    }

    public function canComplete(): bool
    {
        return $this->housekeeping_status === self::HOUSEKEEPING_IN_PROGRESS;
    }

    public function isAvailableForGuests(): bool
    {
        return $this->status === self::STATUS_CLEAN || 
               $this->status === self::STATUS_INSPECTED;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DIRTY => 'danger',
            self::STATUS_CLEAN => 'success',
            self::STATUS_INSPECTED => 'primary',
            self::STATUS_MAINTENANCE => 'warning',
            self::STATUS_OUT_OF_ORDER => 'dark',
            default => 'secondary'
        };
    }

    public function getHousekeepingStatusColorAttribute(): string
    {
        return match($this->housekeeping_status) {
            self::HOUSEKEEPING_PENDING => 'warning',
            self::HOUSEKEEPING_IN_PROGRESS => 'info',
            self::HOUSEKEEPING_COMPLETED => 'success',
            self::HOUSEKEEPING_INSPECTED => 'primary',
            default => 'secondary'
        };
    }
}
