<?php

namespace App\App\Models\Tenant\Tenant\Tenant;

use Spatie\Permission\App\Models\Tenant\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $connection = 'tenant';
}
