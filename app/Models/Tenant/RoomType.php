<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use HasFactory, SoftDeletes;
    
    // Define fillable attributes
    protected $fillable = [
        'property_id',
        'legacy_code',
        'name',
        'code',
        'description',
        'base_capacity',
        'max_capacity',
        'amenities',
        'is_active'
    ];

    // Cast attributes to appropriate types
    protected $casts = [
        'is_active' => 'boolean',
        'base_capacity' => 'integer',
        'max_capacity' => 'integer',
        // Remove amenities cast to prevent JSON/array conflicts with controller
    ];

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(RoomRate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get rates based on the is_shared flag and date range
     * @param bool $isShared
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     * usage: $roomType->getRangeRates(true, '2024-01-01', '2024-01-10');
     */
    public function getRangeRates(bool $isShared, ?string $startDate = null, ?string $endDate = null)
    {
        $ratesQuery = $this->rates()->where('is_shared', $isShared);

        if ($startDate && $endDate) {
            $ratesQuery->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('effective_from', '<=', $startDate)
                    ->where('effective_until', '>=', $startDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('effective_from', '<=', $endDate)
                    ->where('effective_until', '>=', $endDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('effective_from', '>=', $startDate)
                    ->where('effective_until', '<=', $endDate);
                })
                // Add this for open-ended rates
                ->orWhere(function ($q) use ($endDate) {
                    $q->where('effective_from', '<=', $endDate)
                    ->whereNull('effective_until');
                });
            });
        }

        // order by effective_from date (latest first)
        $ratesQuery->orderBy('effective_from', 'desc');

        // Execute and return the rates
        return $ratesQuery->get();
    }

    /**
     * Get amenities as array
     * @return array
     */
    public function getAmenitiesArrayAttribute()
    {
        $decoded = json_decode($this->amenities, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get formatted amenities with details
     * @return \Illuminate\Support\Collection
     * usage: $roomType->amenities_with_details
     */
    public function getAmenitiesWithDetailsAttribute()
    {
        $slugs = $this->getAmenitiesArrayAttribute();
        if (empty($slugs)) return collect();
        
        return RoomAmenity::whereIn('slug', $slugs)
                          ->where('property_id', $this->property_id)
                          ->get();
    }

    /**
     * Count of amenities
     * @return int
     */
    public function getAmenitiesCountAttribute(): int
    {
        return count($this->amenities_array);
    }
}
