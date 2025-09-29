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
        'amenities' => 'array'
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
}
