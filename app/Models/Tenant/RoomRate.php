<?php

namespace App\App\Models\Tenant\Tenant\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\App\Models\Tenant\Tenant\Scopes\PropertyScope;

class RoomRate extends Model
{
    use HasFactory, SoftDeletes;, SoftDeletes

    protected $fillable = [
        'room_type_id',
        'property_id',
        'name',
        'rate_type',
        'effective_from',
        'effective_until',
        'amount',
        'min_nights',
        'max_nights',
        'is_shared',
        'is_active',
        'conditions'
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'amount' => 'decimal:2',
        'min_nights' => 'integer',
        'max_nights' => 'integer',
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
        'conditions' => 'array'
    ];

    // In Room, Booking, Guest, RoomType, Rate models:
    public function forProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValidForDate($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
                     ->where(function ($q) use ($date) {
                         $q->where('effective_until', '>=', $date)
                           ->orWhereNull('effective_until');
                     });
    }

    public function scopeForRoomType($query, $roomTypeId)
    {
        return $query->where('room_type_id', $roomTypeId);
    }
}