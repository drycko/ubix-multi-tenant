<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    // Model properties and relationships can be defined here
    protected $fillable = [
        'subscription_id',
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'notes',
        'transaction_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class, 'invoice_id');
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->save();
    }

    public function markAsFailed()
    {
        $this->status = 'failed';
        $this->save();
    }

    public function markAsPending()
    {
        $this->status = 'pending';
        $this->save();
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
