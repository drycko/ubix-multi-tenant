<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    const SUPPORTED_TENANT_ROLES = ['super-user', 'super-manager', 'property-admin', 'support', 'manager', 'receptionist', 'housekeeping', 'accountant', 'guest'];

    const SUPPORTED_ROLES = ['super-admin', 'super-manager', 'support'];

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    
    /**
     * Default guard is web (central).
     * Automatically switches to tenant when in tenant context.
     */
    protected $guard_name = 'web';

    public function getDefaultGuardName(): string
    {
        return tenant() ? 'tenant' : 'web';
    }

}
