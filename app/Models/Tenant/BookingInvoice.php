<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\PropertyScope;

class BookingInvoice extends Model
{
    use HasFactory, SoftDeletes;

    const INVOICE_STATUSES = [
        'pending',
        'partially_paid',
        'paid',
        'overdue',
        'cancelled',
    ];
    
    const INVOICE_PAYMENT_STATUSES = [
        'pending',
        'completed',
        'failed',
    ];

    const INVOICE_PAYMENT_METHODS = [
        'credit_card',
        'paypal',
        'bank_transfer',
        'stripe',
        'cash_payment',
    ];

    protected $fillable = [
        'property_id',
        'booking_id',
        'invoice_number',
        'amount',
        'subtotal_amount',
        'tax_amount',
        'tax_rate',
        'tax_name',
        'tax_type',
        'tax_inclusive',
        'tax_id',
        'status',
        'external_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_inclusive' => 'boolean',
    ];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new PropertyScope);
    // }

    // local scopes
    public function forProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    // Relationships
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }
    public function scopeForproperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    /**
     * Get total amount paid for this invoice.
     */
    public function getTotalPaidAttribute()
    {
        return $this->invoicePayments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Get remaining balance for this invoice.
     */
    public function getRemainingBalanceAttribute()
    {
        return $this->amount - $this->total_paid;
    }

    /**
     * Check if invoice is fully paid.
     */
    public function getIsFullyPaidAttribute()
    {
        return $this->total_paid >= $this->amount;
    }

    /**
     * Check if invoice has any payments.
     */
    public function getHasPaymentsAttribute()
    {
        return $this->total_paid > 0;
    }

    /**
     * Get formatted subtotal amount.
     */
    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal_amount ?? $this->amount, 2);
    }

    /**
     * Get formatted tax amount.
     */
    public function getFormattedTaxAmountAttribute()
    {
        return number_format($this->tax_amount, 2);
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->amount, 2);
    }

    /**
     * Calculate tax breakdown for display.
     */
    public function getTaxBreakdownAttribute()
    {
        if (!$this->tax_id || !$this->tax_amount) {
            return null;
        }

        return [
            'name' => $this->tax_name,
            'rate' => $this->tax_rate,
            'type' => $this->tax_type,
            'amount' => $this->tax_amount,
            'inclusive' => $this->tax_inclusive,
            'subtotal' => $this->subtotal_amount ?? ($this->amount - $this->tax_amount),
        ];
    }
}
