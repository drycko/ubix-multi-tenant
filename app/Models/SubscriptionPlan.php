<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class SubscriptionPlan extends Model
{
    // Model properties and relationships can be defined here
    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'yearly_price',
        'max_properties',
        'max_users',
        'max_rooms',
        'max_guests',
        'has_analytics',
        'has_support',
        'has_api_access',
        'sort_order',
        'is_active',
        'features',
        'limitations',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'max_properties' => 'integer',
        'max_users' => 'integer',
        'max_rooms' => 'integer',
        'max_guests' => 'integer',
        'has_analytics' => 'boolean',
        'has_support' => 'boolean',
        'has_api_access' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array',
        'limitations' => 'array',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function isFree(): bool
    {
        return $this->monthly_price == 0 && $this->yearly_price == 0;
    }

    public function getPrice(string $period = 'monthly'): float
    {
        return $period === 'yearly' ? $this->yearly_price : $this->monthly_price;
    }
}
