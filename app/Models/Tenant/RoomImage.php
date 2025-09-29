<?php

namespace App\App\Models\Tenant\Tenant\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\App\Models\Tenant\Tenant\Scopes\PropertyScope;

class RoomImage extends Model
{
    // Mass assignable attributes
    protected $fillable = [
        'room_id',
        'property_id',
        'image_path',
        'caption',
        'display_order',
    ];

    // Relationships
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function forProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }
}
