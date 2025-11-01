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
        'manager',
        'accountant',
        'support',
        'receptionist',
        'cashier',
        'security',
        'waiter',
        'chef',
        'kitchen-staff',
        'butler',
        'maintenance',
        'housekeeping',
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
    protected $connection = 'tenant';

    protected $guard_name = 'tenant';

    /**
     * Get the count of bookings created by this user through activity logs.
     */
    public function bookingsCount()
    {
        return $this->activityLogs()->where('activity_type', 'create_booking')->count();
    }

    /**
     * Get all TenantUserActivity logs made by this user.
     */
    public function activityLogs()
    {
        return $this->hasMany(TenantUserActivity::class, 'tenant_user_id');
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
        if (!$this->profile_photo_path) {
            return $this->defaultProfilePhotoUrl();
        }

        // Handle different storage configurations
        if (config('app.env') === 'production') {
            // For production with GCS or other cloud storage
            $gcsConfig = config('filesystems.disks.gcs');
            $bucket = $gcsConfig['bucket'] ?? null;
            $path = ltrim($this->profile_photo_path, '/');
            return $bucket ? "https://storage.googleapis.com/{$bucket}/{$path}" : asset('storage/' . $this->profile_photo_path);
        } else {
            // For local development - just use asset helper
            return asset('storage/' . $this->profile_photo_path);
        }
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     */
    protected function defaultProfilePhotoUrl()
    {
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }
}