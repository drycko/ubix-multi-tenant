<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsTenantUserActivity;

class MaintenanceTask extends Model
{
    use HasFactory, LogsTenantUserActivity;

    protected $fillable = [
        'maintenance_request_id',
        'assigned_to',
        'created_by',
        'task_type',
        'priority',
        'status',
        'title',
        'description',
        'instructions',
        'tools_required',
        'materials_used',
        'completion_notes',
        'estimated_cost',
        'actual_cost',
        'estimated_minutes',
        'actual_minutes',
        'scheduled_for',
        'assigned_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'tools_required' => 'array',
        'materials_used' => 'array',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'scheduled_for' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Constants
    public const TASK_TYPE_DIAGNOSIS = 'diagnosis';
    public const TASK_TYPE_REPAIR = 'repair';
    public const TASK_TYPE_REPLACEMENT = 'replacement';
    public const TASK_TYPE_TESTING = 'testing';
    public const TASK_TYPE_CLEANUP = 'cleanup';
    public const TASK_TYPE_DOCUMENTATION = 'documentation';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ON_HOLD = 'on_hold';

    // Supported options
    const TASK_TYPES = [
        self::TASK_TYPE_DIAGNOSIS,
        self::TASK_TYPE_REPAIR,
        self::TASK_TYPE_REPLACEMENT,
        self::TASK_TYPE_TESTING,
        self::TASK_TYPE_CLEANUP,
        self::TASK_TYPE_DOCUMENTATION,
    ];

    const PRIORITY_OPTIONS = [
        self::PRIORITY_LOW,
        self::PRIORITY_NORMAL,
        self::PRIORITY_HIGH,
        self::PRIORITY_URGENT,
    ];

    const STATUS_OPTIONS = [
        self::STATUS_PENDING,
        self::STATUS_ASSIGNED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_ON_HOLD,
    ];

    // Relationships
    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function staffHours(): MorphMany
    {
        return $this->morphMany(StaffHour::class, 'task');
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

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getTotalHoursAttribute(): float
    {
        return $this->staffHours->sum('hours_worked');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'secondary',
            self::STATUS_ASSIGNED => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_ON_HOLD => 'dark',
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
}