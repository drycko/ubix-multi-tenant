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
        'status',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'invoice_id');
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

    // generate sequential invoice number
    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = self::orderBy('id', 'desc')->first();
        if (!$lastInvoice) {
            return 'INV-00001';
        }
        $lastNumber = (int) str_replace('INV-', '', $lastInvoice->invoice_number);
        $newNumber = $lastNumber + 1;
        return 'INV-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

}
