<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use SoftDeletes;

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
    public function invoice() { return $this->belongsTo(BookingInvoice::class); }
    public function payment() { return $this->belongsTo(InvoicePayment::class); }
    public function user() { return $this->belongsTo(User::class); }
}
