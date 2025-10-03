<?php

namespace App\Models\Tenant;

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
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
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

    // scopes
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get sanitized web description
     */
    public function getSanitizedWebDescriptionAttribute(): string
    {
        return strip_tags($this->web_description);
    }
    public function outOfOrder(): HasMany
    {
        return $this->hasMany(RoomOutOfOrder::class, 'room_id');
    }

    public function scopeOutOfOrder($query)
    {
        return $query->whereHas('outOfOrder', function ($q) {
            $q->where('end_date', '>=', now());
        });
    }

    public function scopeAvailableForDates($query, $arrivalDate, $departureDate)
    {
        // rooms shoould be ordered by ordering
        return $query->orderBy('display_order', 'asc')->whereDoesntHave('bookings', function ($q) use ($arrivalDate, $departureDate) {
            $q->where('status', 'confirmed')
              ->where('arrival_date', '<', $departureDate)
              ->where('departure_date', '>', $arrivalDate);
        });
        // we need to also add type->rates to each room in the controller
    }

    /**
     * return available rooms without bookings in range.
     */
    public static function getAvailableRooms($arrivalDate = null, $departureDate = null)
    {
        // If no dates are provided, use today as the default arrival date
        if (!$arrivalDate) {
            $arrivalDate = now();
        }

        // If no departure date is provided, use 6 days from today as the default
        if (!$departureDate) {
            $departureDate = now()->addDays(6);
        }
        // let's use the scope to get available rooms
        return self::availableForDates($arrivalDate, $departureDate)->where('is_enabled', true)->get();
    }
}
