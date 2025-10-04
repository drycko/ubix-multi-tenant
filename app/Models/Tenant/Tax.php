<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsTenantUserActivity;

class Tax extends Model
{
    use HasFactory, LogsTenantUserActivity;

    protected $fillable = [
        'property_id',
        'name',
        'rate',
        'type',
        'is_inclusive',
        'is_active',
        'description',
        'display_order',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_inclusive' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the property that owns this tax.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Scope to get only active taxes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get taxes ordered by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Calculate tax amount for a given subtotal.
     */
    public function calculateTaxAmount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            return round($subtotal * ($this->rate / 100), 2);
        } elseif ($this->type === 'fixed') {
            return $this->rate;
        }

        return 0;
    }

    /**
     * Calculate inclusive tax amount (tax already included in price).
     */
    public function calculateInclusiveTaxAmount(float $totalAmount): float
    {
        if ($this->type === 'percentage' && $this->is_inclusive) {
            return round($totalAmount - ($totalAmount / (1 + ($this->rate / 100))), 2);
        }

        return 0;
    }

    /**
     * Get formatted rate display.
     */
    public function getFormattedRateAttribute(): string
    {
        if ($this->type === 'percentage') {
            return number_format($this->rate, 2) . '%';
        } elseif ($this->type === 'fixed') {
            return '$' . number_format($this->rate, 2);
        }

        return '';
    }

    /**
     * Get display name with rate.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->formatted_rate . ')';
    }
}
