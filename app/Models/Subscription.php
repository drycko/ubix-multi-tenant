<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;
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
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
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
        // cancel all outstanding invoices
        $this->invoices()->whereIn('status', ['pending', 'partially_paid'])->each(function ($invoice) {
            $invoice->status = 'canceled';
            $invoice->save();
            // cancel all payments associated with the invoice
            $invoice->payments()->each(function ($payment) {
                $payment->status = 'canceled';
                $payment->save();
            });
            
            $this->logAdminActivity(
                "update",
                "subscription_invoices",
                $invoice->id,
                "Canceled invoice #{$invoice->invoice_number} associated with subscription #{$invoice->subscription_id}"
            );
            $this->createAdminNotification("Invoice #{$invoice->invoice_number} was canceled");
        });
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

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                     ->orWhere(function ($q) {
                         $q->whereNotNull('end_date')
                           ->where('end_date', '<=', now());
                     });
    }

    public function hasInvoice(): bool
    {
        return $this->invoices()->exists();
    }

    public function hasActiveInvoice(): bool
    {
        return $this->invoices()->where('status', 'paid')->exists();
    }

    public function latestInvoice()
    {
        return $this->invoices()->orderBy('created_at', 'desc')->first();
    }

    public function hasOutstandingInvoices(): bool
    {
        return $this->invoices()->where('status', 'pending')->exists();
    }
}
