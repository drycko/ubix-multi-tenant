<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\PropertyScope; // ← Add this import

class GuestClub extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'name',
        'description',
        'is_active',
    ];

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(ClubMembershipBenefit::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(GuestClubMember::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(new PropertyScope);
    }

    
}
