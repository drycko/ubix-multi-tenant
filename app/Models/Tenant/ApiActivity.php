<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiActivity extends Model
{
    // The table associated with the model.
    protected $table = 'api_activities';

    // The attributes that are mass assignable.
    protected $fillable = [
        'property_id',
        'api_key',
        'endpoint',
        'method',
        'request_payload',
        'response_payload',
        'ip_address',
    ];

    // The attributes that should be cast to native types.
    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    // Relationships, scopes, and other model methods can be added here as needed.
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenantApi()
    {
        return $this->belongsTo(TenantApi::class);
    }
}
