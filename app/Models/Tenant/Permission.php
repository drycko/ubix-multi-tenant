<?php

namespace App\App\Models\Tenant\Tenant\Tenant;

use Spatie\Permission\App\Models\Tenant\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $connection = 'tenant';
}
