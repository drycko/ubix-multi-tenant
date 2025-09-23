<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Room;
use App\Models\Property;
use App\Models\User;
use App\Models\Scopes\PropertyScope; // ← Add this import

class RoomOutOfOrder extends Model
{
    use HasFactory;
    // Mass assignable attributes
    protected $fillable = [
        'property_id',
        'room_id',
        'start_date',
        'end_date',
        'reason',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

   public function forProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    // Relationships
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    // Scopes
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    // Methods
    public function isCurrentlyOutOfOrder()
    {
        $today = now()->toDateString();
        return $this->start_date <= $today && $this->end_date >= $today;
    }
    

    // Accessors & Mutators can be added here if needed
}
