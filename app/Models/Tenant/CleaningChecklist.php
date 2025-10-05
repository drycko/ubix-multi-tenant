<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsTenantUserActivity;

class CleaningChecklist extends Model
{
    use HasFactory, LogsTenantUserActivity;

    protected $fillable = [
        'property_id',
        'room_type_id',
        'name',
        'description',
        'checklist_type',
        'items',
        'estimated_minutes',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'items' => 'array',
        'is_active' => 'boolean',
        'estimated_minutes' => 'integer',
        'display_order' => 'integer',
    ];

    // Constants
    public const TYPE_STANDARD = 'standard';
    public const TYPE_CHECKOUT = 'checkout';
    public const TYPE_DEEP_CLEAN = 'deep_clean';
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_INSPECTION = 'inspection';

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function housekeepingTasks(): HasMany
    {
        return $this->hasMany(HousekeepingTask::class, 'checklist_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('checklist_type', $type);
    }

    public function scopeByProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopeByRoomType($query, $roomTypeId)
    {
        return $query->where('room_type_id', $roomTypeId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    // Helper methods
    public function getItemsCountAttribute(): int
    {
        return count($this->items ?? []);
    }

    public function getRequiredItemsCountAttribute(): int
    {
        return collect($this->items)->where('required', true)->count();
    }

    public function createTaskInstance(): array
    {
        return collect($this->items)->map(function ($item) {
            return [
                'item' => $item['item'],
                'required' => $item['required'] ?? false,
                'completed' => false,
                'completed_at' => null,
                'notes' => null,
            ];
        })->toArray();
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->checklist_type) {
            self::TYPE_STANDARD => 'primary',
            self::TYPE_CHECKOUT => 'warning',
            self::TYPE_DEEP_CLEAN => 'info',
            self::TYPE_MAINTENANCE => 'danger',
            self::TYPE_INSPECTION => 'success',
            default => 'secondary'
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->checklist_type) {
            self::TYPE_STANDARD => 'Standard Cleaning',
            self::TYPE_CHECKOUT => 'Checkout Cleaning',
            self::TYPE_DEEP_CLEAN => 'Deep Clean',
            self::TYPE_MAINTENANCE => 'Maintenance Check',
            self::TYPE_INSPECTION => 'Quality Inspection',
            default => 'Unknown'
        };
    }

    // Static helper methods
    public static function getDefaultItems($type = self::TYPE_STANDARD): array
    {
        return match($type) {
            self::TYPE_STANDARD => [
                ['item' => 'Make bed with fresh linens', 'required' => true],
                ['item' => 'Vacuum carpet/mop floors', 'required' => true],
                ['item' => 'Clean bathroom thoroughly', 'required' => true],
                ['item' => 'Dust all surfaces', 'required' => true],
                ['item' => 'Empty trash bins', 'required' => true],
                ['item' => 'Restock amenities', 'required' => true],
                ['item' => 'Check all lights and fixtures', 'required' => false],
                ['item' => 'Arrange furniture properly', 'required' => false],
            ],
            self::TYPE_CHECKOUT => [
                ['item' => 'Strip and remove all linens', 'required' => true],
                ['item' => 'Check for damages', 'required' => true],
                ['item' => 'Clean bathroom completely', 'required' => true],
                ['item' => 'Vacuum thoroughly', 'required' => true],
                ['item' => 'Check minibar consumption', 'required' => false],
                ['item' => 'Inspect furniture condition', 'required' => true],
                ['item' => 'Check for lost items', 'required' => true],
            ],
            self::TYPE_DEEP_CLEAN => [
                ['item' => 'Deep clean carpet/floors', 'required' => true],
                ['item' => 'Wash windows and mirrors', 'required' => true],
                ['item' => 'Clean light fixtures', 'required' => true],
                ['item' => 'Wipe down all surfaces', 'required' => true],
                ['item' => 'Clean behind furniture', 'required' => true],
                ['item' => 'Sanitize all touch points', 'required' => true],
                ['item' => 'Check and clean vents', 'required' => false],
            ],
            default => []
        };
    }
}
