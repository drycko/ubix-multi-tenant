<?php
namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant\Booking;
use App\Models\Tenant\RoomChange;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    const SUPPORTED_ROLES = [
        'super-user',
        'super-manager',
        'property-admin',
        'support',
        'manager',
        'receptionist',
        'housekeeping',
        'accountant',
        'guest'
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'property_id',
        'position',
        'role',
        'profile_photo_path',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $guard_name = 'tenant';

    /**
     * Get all bookings created by this user.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'created_by');
    }

    /**
     * Get all room changes made by this user.
     */
    public function roomChanges()
    {
        return $this->hasMany(RoomChange::class, 'changed_by');
    }

    /**
     * Get the property this user belongs to.
     */
    public function property()
    {
        return $this->belongsTo(\App\Models\Tenant\Property::class);
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->name}";
    }

    /**
     * Get the user's profile photo url.
     */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
            ? Storage::disk('public')->url($this->profile_photo_path)
            : $this->defaultProfilePhotoUrl();
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     */
    protected function defaultProfilePhotoUrl()
    {
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }
}