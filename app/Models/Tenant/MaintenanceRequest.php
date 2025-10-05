<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsTenantUserActivity;

class MaintenanceRequest extends Model
{
    use HasFactory, LogsTenantUserActivity;

    protected $fillable = [
        'room_id',
        'property_id',
        'reported_by',
        'assigned_to',
        'request_number',
        'category',
        'priority',
        'status',
        'title',
        'description',
        'location_details',
        'images',
        'estimated_cost',
        'actual_cost',
        'resolution_notes',
        'parts_used',
        'requires_room_closure',
        'scheduled_for',
        'assigned_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'images' => 'array',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'requires_room_closure' => 'boolean',
        'scheduled_for' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Constants
    public const CATEGORY_PLUMBING = 'plumbing';
    public const CATEGORY_ELECTRICAL = 'electrical';
    public const CATEGORY_HVAC = 'hvac';
    public const CATEGORY_FURNITURE = 'furniture';
    public const CATEGORY_APPLIANCE = 'appliance';
    public const CATEGORY_STRUCTURAL = 'structural';
    public const CATEGORY_OTHER = 'other';

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

    // Relationships
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
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

    public function scopeRequiringClosure($query)
    {
        return $query->where('requires_room_closure', true);
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

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
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

    public function getCostVarianceAttribute(): ?float
    {
        if ($this->estimated_cost && $this->actual_cost) {
            return $this->actual_cost - $this->estimated_cost;
        }
        return null;
    }

    // Boot method for auto-generating request numbers
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->request_number)) {
                $request->request_number = static::generateRequestNumber();
            }
        });
    }

    private static function generateRequestNumber(): string
    {
        $prefix = 'MR';
        $date = now()->format('Ymd');
        $lastRequest = static::whereDate('created_at', today())
                           ->orderBy('id', 'desc')
                           ->first();
        
        $sequence = $lastRequest ? 
                   (int) substr($lastRequest->request_number, -3) + 1 : 
                   1;

        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
