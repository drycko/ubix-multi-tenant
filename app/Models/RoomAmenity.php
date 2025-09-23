<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomAmenity extends Model
{
    use HasFactory;
    // The RoomAmenity model represents global amenities that can be assigned to room types.
    protected $fillable = ['property_id', 'name', 'slug', 'icon', 'description'];

    // You can add relationships here if needed in the future
    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
