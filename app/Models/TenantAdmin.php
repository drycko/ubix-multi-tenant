<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TenantAdmin extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'phone',
        'company_name',
        'address',
        'can_manage_billing',
        'can_manage_users',
        'can_manage_settings',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'can_manage_billing' => 'boolean',
            'can_manage_users' => 'boolean',
            'can_manage_settings' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the tenant that owns the admin.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Check if admin can manage billing
     */
    public function canManageBilling(): bool
    {
        return $this->is_active && $this->can_manage_billing;
    }

    /**
     * Check if admin can manage users
     */
    public function canManageUsers(): bool
    {
        return $this->is_active && $this->can_manage_users;
    }

    /**
     * Check if admin can manage settings
     */
    public function canManageSettings(): bool
    {
        return $this->is_active && $this->can_manage_settings;
    }

    /**
     * Update last login information
     */
    public function updateLastLogin()
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);
    }

    /**
     * Get the guard name for this model
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\TenantAdminResetPassword($token));
    }
}
