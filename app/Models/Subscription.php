<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    // Model properties and relationships can be defined here
    protected $fillable = [
        'tenant_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'billing_cycle',
        'price',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->end_date === null || $this->end_date->isFuture());
    }

    public function cancel()
    {
        $this->status = 'canceled';
        $this->end_date = now();
        $this->save();
    }

    public function renew($newEndDate)
    {
        $this->end_date = $newEndDate;
        $this->status = 'active';
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where(function ($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>', now());
                     });
    }
}
