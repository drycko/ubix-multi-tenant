<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Room extends Model
{
    use HasFactory, SoftDeletes;

    // Define fillable attributes
    protected $fillable = [
        'room_type_id',
        'property_id',
        'legacy_room_code',
        'number',
        'name',
        'short_code',
        'floor',
        'description',
        'web_description',
        'web_image',
        'display_order',
        'notes',
        'is_enabled',
        'is_featured',
    ];

    // Cast attributes to appropriate types
    protected $casts = [
        'is_enabled' => 'boolean',
        'floor' => 'integer',
        'display_order' => 'integer',
        'legacy_room_code' => 'integer'
    ];

    // Relationships
    public function type(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class);
    }

    // some rooms belong to a packages and some don't
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'room_packages', 'room_id', 'package_id');
    }
}
