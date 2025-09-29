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

    protected $fillable = [
        'property_id',
        'booking_id',
        'invoice_number',
        'amount',
        'status',
        'external_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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
}
