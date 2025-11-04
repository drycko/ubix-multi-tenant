<?php
namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

// use App\Models\Tenant\Scopes\PropertyScope;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    const VALID_SOURCES = ['website','walk_in','phone','agent','legacy','inhouse','wordpress','email'];
    const STATUS_PENDING = 'pending';
    const VALID_STATUSES = ['pending','booked','confirmed','checked_in','checked_out','cancelled','no_show'];

    const TABLE = 'bookings';

    const ALLOWED_BOOKING_DAYS = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
    ];

    const DEFAULT_MAX_BOOKING_DAYS_AHEAD = 365;
    const DEFAULT_MIN_BOOKING_HOURS_AHEAD = 24;
    const DEFAULT_MIN_NIGHTS = 1;
    const DEFAULT_MAX_NIGHTS = 30;


    protected $fillable = [
        'bcode',
        'room_id',
        'package_id',
        'is_shared',
        'property_id',
        'status',
        'source',
        'arrival_date',
        'departure_date',
        'nights',
        'guest_count',
        'daily_rate',
        'total_amount',
        'deposit_amount',
        'deposit_due_date',
        'deposit_receipt_number',
        'currency',
        'invoice_number',
        'invoice_date',
        'invoice_amount',
        'legacy_tr_id',
        'legacy_group_id',
        'legacy_meta'
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'departure_date' => 'date',
        'deposit_due_date' => 'date',
        'invoice_date' => 'date',
        'daily_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'invoice_amount' => 'decimal:2',
        'nights' => 'integer',
        'guest_count' => 'integer',
        'legacy_meta' => 'array'
    ];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new PropertyScope);
    // }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bookingGuests(): HasMany
    {
        return $this->hasMany(BookingGuest::class, 'booking_id', 'id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function guests(): HasManyThrough
    {
        return $this->hasManyThrough(Guest::class, BookingGuest::class, 'booking_id', 'id', 'id', 'guest_id');
        // return $this->hasManyThrough(
        //     Guest::class, 
        //     BookingGuest::class,
        //     'booking_id', // Foreign key on booking_guests table
        //     'id', // Foreign key on guests table
        //     'id', // Local key on bookings table
        //     'guest_id' // Local key on booking_guests table
        // )
        // ->withoutGlobalScope(\App\App\Models\Tenant\Tenant\Scopes\PropertyScope::class)
        // ->where(function($query) {
        //     $query->where('guests.property_id', current_property()->id)
        //           ->orWhereNull('guests.property_id');
        // });
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(BookingInvoice::class, 'booking_id', 'id');
    }

    public function primaryGuest()
    {
        return $this->bookingGuests()->where('is_primary', true)->first();
    }

    public function getPrimaryGuestAttribute()
    {
        $primaryBookingGuest = $this->bookingGuests->where('is_primary', true)->first();
        return $primaryBookingGuest ? $primaryBookingGuest->guest : null;
    }

    public function adults()
    {
        return $this->bookingGuests()->where('is_adult', true);
    }

    public function children()
    {
        return $this->bookingGuests()->where('is_adult', false);
    }

    public function getNumberOfGuestsAttribute()
    {
        return $this->bookingGuests()->count();
    }

    /**
     * Guest feedbacks relationship
     */
    public function guestFeedbacks(): HasMany
    {
        return $this->hasMany(GuestFeedback::class, 'booking_id', 'id');
    }

    /*  SCOPES  */ 

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeForDates($query, $startDate, $endDate)
    {
        return $query->where('arrival_date', '<', $endDate)
                     ->where('departure_date', '>', $startDate);
    }

    // local scope to filter by property
    public function scopeForProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    // help API create a booking
    public static function createBooking($property, $package, $roomType, $availableRoom, $arrivalDate, $departureDate, $isSharedRoom, $primaryGuest, $additionalGuest, $bookingSource = 'website', $dailyRate = 0, $ipAddress = null)
    {
        // calculate nights
        $arrival = \Carbon\Carbon::parse($arrivalDate);
        $departure = \Carbon\Carbon::parse($departureDate);

        if ($departure->lte($arrival)) {
            throw new \Exception('Departure date must be after arrival date.');
        }

        $nights = $arrival->diffInDays($departure);
        $ipAddress = $ipAddress ?? request()->ip();

        try {
            // transaction
            \DB::beginTransaction();

            // calculate total amount (for simplicity, no discounts or taxes here)
            $dailyRate = $dailyRate ?? 1; // defaults to 1 if not provided (to avoid zero total) this is the best we can do here
            $totalAmount = $dailyRate * $nights;

            $bookingReference = Booking::generateBookingCode($arrivalDate, $availableRoom->room_number);
            

            $bookingStatus = 'pending';
            // booking source has to be one of VALID_SOURCES
            $validSources = self::VALID_SOURCES;
            $bookingSource = in_array($bookingSource, $validSources) ? $bookingSource : 'website';
            $isShared = $isSharedRoom ? true : false;
            // Create booking
            $booking = Booking::create([
                'package_id' => $package->id,
                'is_shared' => $isShared,
                'property_id' => $property->id,
                'room_id' => $availableRoom->id,
                'bcode' => $bookingReference,
                'arrival_date' => $arrivalDate,
                'departure_date' => $departureDate,
                'nights' => $nights,
                'daily_rate' => $dailyRate,
                'total_amount' => $totalAmount,
                'status' => $bookingStatus,
                'source' => $bookingSource,
                'ip_address' => $ipAddress,
                'currency' => $property->currency ?? 'USD',
            ]);

            $primaryMeta = [
                'preferred_language' => $primaryGuest['preferred_language'] ?? '',
                'preferred_name' => ($primaryGuest['first_name'] ?? '') . ' ' . ($primaryGuest['last_name'] ?? ''),
                'is_returning' => $primaryGuest['is_returning'] ?? false,
                'dietary_allergies' => $primaryGuest['dietary_allergies'] ?? '',
                'gown_size' => $primaryGuest['gown_size'] ?? '',
                'id_no' => $primaryGuest['id_no'] ?? '',
            ];
            $primaryGuest['legacy_meta'] = $primaryMeta;

            // create primary guest
            $booking->bookingGuests()->create([
                'guest_id' => Guest::firstOrCreateFromData($primaryGuest, $property->id)->id,
                'is_primary' => true,
                'is_adult' => true,
                'age' => null,
                'is_sharing' => $isShared,
                'special_requests' => $primaryGuest['special_requests'] ?? '',
                'arrival_time' => null,
                'property_id' => $property->id, // Make sure this is included!
                'legacy_meta' => $primaryGuest['legacy_meta'] ?? null,
            ]);

            // create additional guest if shared room
            if ($isSharedRoom && $additionalGuest) {
                $additionalMeta = [
                    'preferred_language' => $additionalGuest['preferred_language'] ?? '',
                    'preferred_name' => ($additionalGuest['first_name'] ?? '') . ' ' . ($additionalGuest['last_name'] ?? ''),
                    'is_returning' => $additionalGuest['is_returning'] ?? false,
                    'dietary_allergies' => $additionalGuest['dietary_allergies'] ?? '',
                    'gown_size' => $additionalGuest['gown_size'] ?? '',
                    'id_no' => $additionalGuest['id_no'] ?? '',
                ];
                $booking->bookingGuests()->create([
                    'guest_id' => Guest::firstOrCreateFromData($additionalGuest, $property->id)->id,
                    'is_primary' => false,
                    'is_adult' => true,
                    'age' => null,
                    'is_sharing' => $isShared,
                    'special_requests' => $additionalGuest['special_requests'] ?? '',
                    'arrival_time' => null,
                    'property_id' => $property->id, // Make sure this is included!
                    'legacy_meta' => $additionalGuest['legacy_meta'] ?? null,
                ]);
            }

            
            // create invoice (we are going to create the invoice in the controller after payment)
            // $invoice_number = self::generateUniqueInvoiceNumber('0000001');
            // $invoice_status = 'pending';

            // $booking_invoice = $booking->invoices()->create([
            //     'property_id' => current_property()->id,
            //     'invoice_number' => $invoice_number,
            //     'amount' => $totalAmount,
            //     'status' => $invoice_status,
            // ]);

            // mark room as booked for the dates (this could be more complex in real app)
            // $availableRoom->markAsBooked($arrivalDate, $departureDate);
            // commit
            \DB::commit();

            return $booking;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Booking@createBooking error: ' . $e->getMessage());
            throw new \Exception('Failed to create booking: ' . $e->getMessage());
        }
    }

    /**
     * generate a unique booking code based on arrival date, room number and a random number
     * Format: YYYYMMDDRNXXX where RN is room number (2 digits) and XXX is a random 3 digit number
     * Example: 20231015020123 (for room 2, arrival date 2023-10-15, random 123)
     */
    public static function generateBookingCode($arrivalDate, $roomNumber)
    {
        $datePart = date('Ymd', strtotime($arrivalDate));
        $roomPart = str_pad($roomNumber, 2, '0', STR_PAD_LEFT);
        $randomPart = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        $newBookingCode = $datePart . $roomPart . $randomPart;
        // we need to ensure the booking code is unique for the property
        // if not, we regenerate
        $existing = Booking::where('property_id', current_property()->id)
            ->where('bcode', $newBookingCode)
            ->first();

        if ($existing) {
            return self::generateBookingCode($arrivalDate, $roomNumber);
        }

        return $newBookingCode;
    }

    public static function generateUniqueInvoiceNumber($invoiceNumber = null)
    {
        if ($invoiceNumber) {
            $existingInvoice = BookingInvoice::where('invoice_number', $invoiceNumber)
                ->where('property_id', current_property()->id)
                ->first();
            if ($existingInvoice) {
                // Increment and try again
                $invoiceNumber = increment_unique_number($invoiceNumber);
                return self::generateUniqueInvoiceNumber($invoiceNumber);
            }
            // Unique, return it
            return $invoiceNumber;
        } else {
            // Get the last invoice number and increment
            $lastInvoice = BookingInvoice::where('property_id', current_property()->id)
                ->orderBy('invoice_number', 'desc')
                ->first();
            if ($lastInvoice) {
                $invoiceNumber = increment_unique_number($lastInvoice->invoice_number);
            } else {
                $invoiceNumber = '0000001';
            }
            return $invoiceNumber;
        }
    }

    public static function calculateDailyRate($isShared = false)
    {
        // room does not have standard_rate, we get it from room type rates
        $standard_rate = $this->type->rates->where('is_shared', $isShared)->first() ?? 0; // Default rate
        return $standard_rate;
    }

    public function getGuestCountAttribute()
    {
        return $this->bookingGuests()->count();
    }

    // public function getIsSharedAttribute()
    // {
    //     return $this->is_shared ? true : false;
    // }

    public function getRatingAttribute()
    {
        $feedbacks = $this->guestFeedbacks;
        if ($feedbacks->isEmpty()) {
            return null;
        }
        return round($feedbacks->avg('rating'), 2);
    }
    
}