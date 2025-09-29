<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    // make it soft deletable
    use HasFactory, SoftDeletes;
    const BILLING_PERIODS = ['monthly', 'yearly'];
    const ADDITIONAL_FEATURES = [
        'basic_reporting' => 'Basic Reporting',
        'analytics' => 'Access to Analytics',
        'email_support' => 'Email Support',
        'advanced_reporting' => 'Advanced Reporting',
        'multi_property' => 'Multi-Property Support',
        'priority_support' => 'Priority Support',
        'full_analytics' => 'Full Analytics',
        '24_7_support' => '24/7 Support',
        'api_access' => 'API Access',
        'custom_features' => 'Custom Features',
        'dedicated_manager' => 'Dedicated Account Manager',
        'custom_integrations' => 'Custom Integrations',
        'dedicated_server' => 'Dedicated Server',
        'white_labeling' => 'White Labeling',
    ];
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isFree(): bool
    {
        return $this->monthly_price == 0 && $this->yearly_price == 0;
    }

    public function getPrice(string $period = 'monthly'): float
    {
        return $period === 'yearly' ? $this->yearly_price : $this->monthly_price;
    }

    public static function getBillingPeriods(): array
    {
        return self::BILLING_PERIODS;
    }
    public static function getAdditionalFeatures(): array
    {
        return self::ADDITIONAL_FEATURES;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
