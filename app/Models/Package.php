<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tonysm\RichTextLaravel\Models\Traits\HasRichText;

use App\Models\Scopes\PropertyScope;

class Package extends Model
{
    use HasFactory, SoftDeletes, HasRichText;  

    /**
     * The dynamic rich text attributes.
     *
     * @var array<int|string, string>
     */
    protected $richTextAttributes = [
        'pkg_description',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'property_id',
        'pkg_id',
        'pkg_name',
        'pkg_sub_title',
        'pkg_description',
        'pkg_number_of_nights',
        'pkg_checkin_days',
        'pkg_status',
        'pkg_enterby',
        'pkg_image',
        'pkg_base_price',
        'pkg_inclusions',
        'pkg_exclusions',
        'pkg_min_guests',
        'pkg_max_guests',
        'pkg_valid_from',
        'pkg_valid_to',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deleted' => 'boolean',
        'pkg_inclusions' => 'array',
        'pkg_exclusions' => 'array',
        'pkg_valid_from' => 'date',
        'pkg_valid_to' => 'date',
        'pkg_base_price' => 'decimal:2',
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

    /**
     * Get the user who created the package.
     */
    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pkg_enterby');
    }

    /**
     * Get the rooms associated with the package.
     */
    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_packages');
    }

    /**
     * Scope a query to only include active packages.
     */
    public function scopeActive($query)
    {
        return $query->where('pkg_status', 'active')->where('deleted', false);
    }

    /**
     * Get the check-in days as an array.
     */
    public function getCheckinDaysArrayAttribute(): array
    {
        return $this->pkg_checkin_days ? explode(',', $this->pkg_checkin_days) : [];
    }

    /**
     * Check if package is valid for a specific day of week.
     */
    public function isValidForDay(int $dayOfWeek): bool
    {
        if (empty($this->pkg_checkin_days)) {
            return true;
        }

        return in_array($dayOfWeek, $this->checkin_days_array);
    }
}