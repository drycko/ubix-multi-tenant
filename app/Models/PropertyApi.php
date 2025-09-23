<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyApi extends Model
{
    // The table associated with the model.
    protected $table = 'property_apis';

    // The attributes that are mass assignable.
    protected $fillable = [
        'property_id',
        'api_name',
        'api_key',
        'api_secret',
        'is_active',
    ];

    // Relationships
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function apiActivities()
    {
        return $this->hasMany(ApiActivity::class);
    }
}
