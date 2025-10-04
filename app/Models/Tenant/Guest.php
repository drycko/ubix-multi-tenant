<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Tenant\Scopes\PropertyScope;

class Guest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'title',
        'first_name',
        'last_name',
        'id_number',
        'nationality',
        'country_name',
        'email',
        'phone',
        'emergency_contact',
        'emergency_contact_phone',
        'physical_address',
        'residential_address',
        'medical_notes',
        'dietary_preferences',
        'gown_size',
        'car_registration',
        'is_active',
        'legacy_meta'
    ];

    protected $casts = [
        'legacy_meta' => 'array'
    ];

    
    public function forProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(BookingGuest::class);
    }

    public function guestClubMembers(): HasMany
    {
        return $this->hasMany(GuestClubMember::class);
    }

    public function guestClubs(): BelongsToMany
    {
        return $this->belongsToMany(GuestClub::class, 'guest_club_members');
    }

    public function invoicePayments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(BookingInvoice::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->title} {$this->first_name} {$this->last_name}");
    }

    public function scopeWithEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeWithPhone($query, $phone)
    {
        return $query->where('phone', $phone);
    }

    public static function firstOrCreateFromData(array $data, $propertyId)
    {   
        // by email or phone
        if (!empty($data['email']) || !empty($data['phone'])) {
            $guest = self::where('property_id', $propertyId)
                        ->where('email', $data['email'])
                        ->first();
            if ($guest) {
                return $guest;
            }

            // by phone
            if (!empty($data['phone'])) {
                $guest = self::where('property_id', $propertyId)
                            ->where('phone', $data['phone'])
                            ->first();
            }
            if ($guest) {
                return $guest;
            }
            // create new
            return self::create([
                'property_id' => $propertyId,
                'title' => $data['title'] ?? 'Mr/Ms',
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'nationality' => $data['nationality'] ?? '',
                'country_name' => $data['country_name'] ?? '',
                'id_number' => $data['id_number'] ?? '',
                'emergency_contact' => $data['emergency_contact'] ?? '',
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? '',
                'physical_address' => $data['physical_address'] ?? '',
                'medical_notes' => $data['medical_notes'] ?? '',
                'dietary_preferences' => $data['dietary_preferences'] ?? '',
                'gown_size' => $data['gown_size'] ?? '',
                'car_registration' => $data['car_registration'] ?? '',
                'is_active' => true,
            ]);
        }
        else {
            throw new \Exception('Cannot create guest without email or phone');
        }
    }

    // Get total amount spent by a guest across all invoice payments
    public function totalAmountSpent()
    {
        return $this->invoicePayments()
                    ->where('status', 'completed')
                    ->sum('amount');
    }

    // Get total confirmed bookings count
    public function confirmedBookingsCount()
    {
        return $this->bookings()
                    ->join('bookings', 'booking_guests.booking_id', '=', 'bookings.id')
                    ->where('bookings.status', 'confirmed')
                    ->count();
    }

    // Get total bookings count
    public function totalBookingsCount()
    {
        return $this->bookings()->count();
    }

    // Check if guest qualifies for a specific club
    public function qualifiesForClub(GuestClub $club): bool
    {
        $totalSpent = $this->totalAmountSpent();
        $totalBookings = $this->confirmedBookingsCount();

        $meetsSpendRequirement = !$club->min_spend || $totalSpent >= $club->min_spend;
        $meetsBookingRequirement = !$club->min_bookings || $totalBookings >= $club->min_bookings;

        return $meetsSpendRequirement && $meetsBookingRequirement;
    }
}