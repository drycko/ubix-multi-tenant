<?php

namespace App\App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\App\Models\Tenant\Tenant\Scopes\PropertyScope;

class BookingGuest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'booking_guests';
    
    protected $fillable = [
        'property_id',
        'booking_id',
        'guest_id',
        'is_primary',
        'is_adult',
        'age',
        'is_sharing',
        'special_requests',
        'payment_type',
        'payment_details',
        'arrival_time',
        'legacy_meta'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_adult' => 'boolean',
        'is_sharing' => 'boolean',
        'age' => 'integer',
        'arrival_time' => 'datetime',
        'legacy_meta' => 'array'
    ];

    // In Room, Booking, Guest, RoomType, Rate models:
    // protected static function booted()
    // {
    //     static::addGlobalScope(new PropertyScope);
    // }

    // local scopes
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function forProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}