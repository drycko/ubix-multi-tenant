<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionInvoice extends Model
{
    // must soft deleteable
    use SoftDeletes;
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

    // fillable properties
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'amount',
        'subtotal_amount',
        'tax_amount',
        'tax_rate',
        'tax_name',
        'tax_type',
        'tax_inclusive',
        'tax_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_inclusive' => 'boolean',
        'tax_id' => 'integer',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'invoice_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'invoice_id');
    }
    
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function markAsPaid()
    {
        $this->status = 'paid';
        $this->save();
    }

    public function markAsOverdue()
    {
        $this->status = 'overdue';
        $this->save();
    }

    public function markAsCancelled()
    {
        $this->status = 'cancelled';
        $this->save();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // delete invoice and its related payments
    public function deleteWithPayments()
    {
        $this->payments()->delete();
        $this->delete();
    }

    /**
     * Generate sequential invoice number.
     * 
     * @param int $firstInvoiceNumber Starting number for invoice sequence
     * @return string Formatted invoice number (e.g., INV-12000)
     */
    public static function generateInvoiceNumber($firstInvoiceNumber = 12000): string
    {
        // Get the last invoice ordered by invoice number (including soft deleted)
        $lastInvoice = self::withTrashed()
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        // If no invoices exist, start with the first number
        if (!$lastInvoice) {
            return 'INV-' . str_pad($firstInvoiceNumber, 5, '0', STR_PAD_LEFT);
        }
        
        // Extract numeric part from last invoice number
        $lastNumber = (int) str_replace('INV-', '', $lastInvoice->invoice_number);
        
        // Start from the higher of: last number or first invoice number
        $nextNumber = max($lastNumber, $firstInvoiceNumber - 1) + 1;
        
        // Ensure uniqueness (check both active and soft deleted invoices)
        $invoiceNumber = 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        while (self::withTrashed()->where('invoice_number', $invoiceNumber)->exists()) {
            $nextNumber++;
            $invoiceNumber = 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        }
        
        return $invoiceNumber;
    }

    // check if invoice 

    public function getPaidAmountAttribute(): float
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    // public function getRemainingBalanceAttribute(): float
    // {
    //     return max(0, $this->amount - $this->paid_amount);
    // }


    /**
     * Get total amount paid for this invoice.
     * Usage: $bookingInvoice->total_paid
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Get remaining balance for this invoice.
     * Usage: $bookingInvoice->remaining_balance
     */
    public function getRemainingBalanceAttribute()
    {
        // Calculate remaining balance (amount - total_paid - total_refunded)
        return $this->amount - $this->total_paid - $this->total_refunded;
    }

    /**
     * Get total amount refunded for this invoice.
     * Usage: $bookingInvoice->total_refunded
     */
    public function getTotalRefundedAttribute()
    {
        return $this->refunds()->where('status', 'approved')->sum('amount');
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
     * Usage: $bookingInvoice->tax_breakdown
     */
    public function getTaxBreakdownAttribute()
    {
        if (!$this->tax_id || !$this->tax_amount) {
            return null;
        }

        // calculate subtotal before tax if inclusive
        if ($this->tax_inclusive) {
            $this->subtotal_amount = $this->amount - $this->tax_amount;
        }

        // return breakdown as array
        return [
            'name' => $this->tax_name,
            'rate' => $this->tax_rate,
            'type' => $this->tax_type == 'percentage' ? 'percentage' : 'fixed',
            'amount' => $this->tax_amount,
            'inclusive' => $this->tax_inclusive,
            'subtotal' => $this->subtotal_amount,
            'total' => $this->amount, // total after tax
        ];
    }

    

}
