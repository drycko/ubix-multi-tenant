<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionInvoice extends Model
{
    CONST INVOICE_STATUSES = [
        'pending',
        'partially_paid',
        'paid',
        'overdue',
        'cancelled',
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
        return $this->hasMany(SubscriptionPayment::class);
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
        $nextId = $lastInvoice ? $lastInvoice->id + 1 : 1;
        return 'INV-' . strtoupper(dechex($nextId));
    }

}
