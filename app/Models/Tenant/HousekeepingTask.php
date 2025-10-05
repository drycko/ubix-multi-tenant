<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsTenantUserActivity;

class HousekeepingTask extends Model
{
    use HasFactory, LogsTenantUserActivity;

    protected $fillable = [
        'room_id',
        'property_id',
        'assigned_to',
        'created_by',
        'booking_id',
        'task_type',
        'priority',
        'status',
        'title',
        'description',
        'instructions',
        'checklist_items',
        'completion_notes',
        'estimated_minutes',
        'actual_minutes',
        'scheduled_for',
        'assigned_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'checklist_items' => 'array',
        'scheduled_for' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_minutes' => 'integer',
        'actual_minutes' => 'integer',
    ];

    public const TYPE_CLEANING = 'cleaning';
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_INSPECTION = 'inspection';
    public const TYPE_DEEP_CLEAN = 'deep_clean';
    public const TYPE_SETUP = 'setup';

    public const TASK_TYPES = [
        self::TYPE_CLEANING,
        self::TYPE_MAINTENANCE,
        self::TYPE_INSPECTION,
        self::TYPE_DEEP_CLEAN,
        self::TYPE_SETUP,
    ];

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('task_type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopeScheduledToday($query)
    {
        return $query->whereDate('scheduled_for', today());
    }

    public function scopeOverdue($query)
    {
        return $query->where('scheduled_for', '<', now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    // Helper methods
    public function canStart(): bool
    {
        return $this->status === self::STATUS_ASSIGNED;
    }

    public function canComplete(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isOverdue(): bool
    {
        return $this->scheduled_for && 
               $this->scheduled_for->isPast() && 
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }
        return null;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'secondary',
            self::STATUS_ASSIGNED => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary'
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'success',
            self::PRIORITY_NORMAL => 'primary',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_URGENT => 'danger',
            default => 'secondary'
        };
    }

    public function getCompletionPercentageAttribute(): int
    {
        if (!$this->checklist_items || empty($this->checklist_items)) {
            return 0;
        }

        $total = count($this->checklist_items);
        $completed = collect($this->checklist_items)->where('completed', true)->count();

        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }
}
