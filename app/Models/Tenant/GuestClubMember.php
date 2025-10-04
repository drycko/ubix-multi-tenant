<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Scopes\PropertyScope;

class GuestClubMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'guest_club_id',
        'guest_id',
        'joined_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    const STATUSES = [
        'active',
        'inactive',
        'suspended',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    // Relationships
    public function guestClub(): BelongsTo
    {
        return $this->belongsTo(GuestClub::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    // Accessors
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'active' => 'bg-success',
            'inactive' => 'bg-secondary',
            'suspended' => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    public function getMembershipDurationAttribute()
    {
        if (!$this->joined_at) {
            return null;
        }
        
        return $this->joined_at->diffForHumans(null, true);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function canReceiveBenefits()
    {
        return $this->status === 'active' && 
               $this->guest && 
               $this->guest->is_active && 
               $this->guestClub && 
               $this->guestClub->is_active;
    }

    protected static function booted()
    {
        static::creating(function ($member) {
            if (!$member->joined_at) {
                $member->joined_at = now();
            }
            if (!$member->status) {
                $member->status = 'active';
            }
        });
    }
}