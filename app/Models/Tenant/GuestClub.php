<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Scopes\PropertyScope;

class GuestClub extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'name',
        'description',
        'is_active',
        'benefits',
        'tier_level',
        'tier_priority',
        'min_bookings',
        'min_spend',
        'badge_color',
        'icon',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'benefits' => 'array',
        'tier_priority' => 'integer',
        'min_bookings' => 'integer',
        'min_spend' => 'decimal:2',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTierPriority($query)
    {
        return $query->orderBy('tier_priority', 'desc');
    }

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(GuestClubMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(GuestClubMember::class)->whereHas('guest', function($q) {
            $q->where('is_active', true);
        });
    }

    // Accessors
    public function getBenefitsListAttribute()
    {
        if (!$this->benefits) {
            return collect();
        }
        
        return collect($this->benefits)->map(function($value, $key) {
            return [
                'key' => $key,
                'value' => $value,
                'display' => $this->formatBenefit($key, $value)
            ];
        });
    }

    public function getMembersCountAttribute()
    {
        return $this->members()->count();
    }

    public function getActiveMembersCountAttribute()
    {
        return $this->activeMembers()->count();
    }

    // Helper methods
    public function formatBenefit($key, $value)
    {
        switch ($key) {
            case 'discount_percentage':
                return "{$value}% Discount on all bookings";
            case 'late_checkout':
                return $value ? 'Late checkout until 2 PM' : null;
            case 'early_checkin':
                return $value ? 'Early check-in from 12 PM' : null;
            case 'complimentary_wifi':
                return $value ? 'Complimentary WiFi' : null;
            case 'complimentary_breakfast':
                return $value ? 'Complimentary Breakfast' : null;
            case 'room_upgrade':
                return $value ? 'Priority Room Upgrades' : null;
            case 'airport_shuttle':
                return $value ? 'Complimentary Airport Shuttle' : null;
            case 'spa_discount':
                return is_numeric($value) ? "{$value}% Spa Discount" : ($value ? 'Spa Discounts Available' : null);
            case 'restaurant_discount':
                return is_numeric($value) ? "{$value}% Restaurant Discount" : ($value ? 'Restaurant Discounts Available' : null);
            case 'priority_booking':
                return $value ? 'Priority Booking Access' : null;
            case 'concierge_service':
                return $value ? 'Dedicated Concierge Service' : null;
            default:
                return is_bool($value) ? ($value ? ucwords(str_replace('_', ' ', $key)) : null) : "{$key}: {$value}";
        }
    }

    public function hasDiscount()
    {
        return isset($this->benefits['discount_percentage']) && $this->benefits['discount_percentage'] > 0;
    }

    public function getDiscountPercentage()
    {
        return $this->benefits['discount_percentage'] ?? 0;
    }

    public function qualifiesGuest(Guest $guest)
    {
        // Check minimum bookings
        if ($this->min_bookings > 0) {
            $bookingCount = $guest->bookings()->whereHas('booking', function($q) {
                $q->where('property_id', $this->property_id)
                  ->whereIn('status', ['confirmed', 'checked_in', 'checked_out']);
            })->count();
            
            if ($bookingCount < $this->min_bookings) {
                return false;
            }
        }

        // Check minimum spend (if needed, would require invoice total calculation)
        if ($this->min_spend > 0) {
            // TODO: Implement spend calculation based on invoices
        }

        return true;
    }

    protected static function booted()
    {
        static::addGlobalScope(new PropertyScope);
        
        static::creating(function ($guestClub) {
            if (!$guestClub->property_id) {
                $guestClub->property_id = selected_property_id();
            }
            if (!$guestClub->badge_color) {
                $guestClub->badge_color = '#3B82F6';
            }
        });
    }
}
