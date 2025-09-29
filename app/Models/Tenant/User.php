<?php
namespace App\App\Models\Tenant\Tenant\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;, SoftDeletes

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
        return $this->hasMany(\App\App\Models\Tenant\Tenant\Booking::class, 'created_by');
    }

    /**
     * Get all room changes made by this user.
     */
    public function roomChanges()
    {
        return $this->hasMany(\App\App\Models\Tenant\Tenant\RoomChange::class, 'changed_by');
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