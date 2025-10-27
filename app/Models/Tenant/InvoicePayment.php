<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'booking_invoice_id',
        'guest_id',
        'payment_method',
        'amount',
        'payment_date',
        'reference_number',
        'notes',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->property_id) {
                $payment->property_id = selected_property_id();
            }
            if (!$payment->recorded_by) {
                $payment->recorded_by = auth()->id();
            }
            
            // Auto-populate guest_id from booking invoice if not set
            if (!$payment->guest_id && $payment->booking_invoice_id) {
                $invoice = BookingInvoice::find($payment->booking_invoice_id);
                if ($invoice && $invoice->booking) {
                    // Get the primary guest from the booking
                    $primaryGuest = $invoice->booking->bookingGuests()->first();
                    if ($primaryGuest) {
                        $payment->guest_id = $primaryGuest->guest_id;
                    }
                }
            }
        });

        static::created(function ($payment) {
            // Update invoice status after payment is recorded
            $payment->updateInvoiceStatus();
        });

        static::updated(function ($payment) {
            // Update invoice status after payment is updated
            $payment->updateInvoiceStatus();
        });

        static::deleted(function ($payment) {
            // Update invoice status after payment is deleted
            $payment->updateInvoiceStatus();
        });
    }

    /**
     * Get the booking invoice that owns the payment.
     */
    public function bookingInvoice(): BelongsTo
    {
        return $this->belongsTo(BookingInvoice::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'payment_id');
    }

    /**
     * Get refunded amount for this payment.
     */
    public function getRefundedAmountAttribute(): float
    {
        return $this->refunds()->sum('amount');
    }

    /**
     * Get the guest who made the payment.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the user who recorded the payment.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }

    /**
     * Update the invoice status based on total payments.
     */
    public function updateInvoiceStatus(): void
    {
        $invoice = $this->bookingInvoice;
        if (!$invoice) {
            $invoice = BookingInvoice::find($this->booking_invoice_id);
        }

        if ($invoice) {
            $totalPaid = $invoice->invoicePayments()->where('status', 'completed')->sum('amount');
            $invoiceAmount = $invoice->amount;

            if ($totalPaid >= $invoiceAmount) {
                $invoice->status = 'paid';
            } elseif ($totalPaid > 0) {
                $invoice->status = 'partially_paid';
            } else {
                $invoice->status = 'pending';
            }

            $invoice->save();
        }
    }

    /**
     * Get payment method options.
     */
    public static function getPaymentMethods(): array
    {
        return [
            'cash' => 'Cash',
            'card' => 'Credit/Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'check' => 'Check',
            'mobile_money' => 'Mobile Money',
            'paypal' => 'PayPal',
            'other' => 'Other',
        ];
    }

    /**
     * Get status options.
     */
    public static function getStatuses(): array
    {
        return [
            'completed' => 'Completed',
            'pending' => 'Pending',
            'failed' => 'Failed',
        ];
    }

    /**
     * Scope to filter by property.
     */
    public function scopeForProperty($query, $propertyId = null)
    {
        $propertyId = $propertyId ?? selected_property_id();
        return $query->where('property_id', $propertyId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return property_currency() . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::getPaymentMethods()[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }
}