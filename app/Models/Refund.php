<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
      'invoice_id',
      'payment_id',
      'user_id',
      'amount',
      'reason',
      'status',
      'gateway_response',
  ];

  // Relationships
  public function invoice(): BelongsTo
  {
    return $this->belongsTo(SubscriptionInvoice::class, 'invoice_id');
  }
  public function payment(): BelongsTo
  {
    return $this->belongsTo(SubscriptionPayment::class, 'payment_id');
  }
  public function tenant(): BelongsTo
  {
    return $this->belongsTo(Tenant::class, 'tenant_id');
  }
}