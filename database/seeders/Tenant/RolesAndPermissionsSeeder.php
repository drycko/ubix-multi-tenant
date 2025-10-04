<?php
// database/seeders/tenant/RolesAndPermissionsSeeder.php
namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant\Role;
use App\Models\Tenant\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure the models are using the tenant connection
        $permissionRegistrar = app(\Spatie\Permission\PermissionRegistrar::class);
        $originalRoleClass = $permissionRegistrar->getRoleClass();
        $originalPermissionClass = $permissionRegistrar->getPermissionClass();

        $permissionRegistrar->setRoleClass(Role::class);
        $permissionRegistrar->setPermissionClass(Permission::class);

        // Reset cached roles/permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Wipe old tenant data to avoid FK mismatch
        // \DB::connection('tenant')->table('role_has_permissions')->truncate();
        // \DB::connection('tenant')->table('permissions')->truncate();
        // \DB::connection('tenant')->table('roles')->truncate();

        // Create permissions
        $permissions = [
            // Bookings
            'view bookings', 'create bookings', 'edit bookings', 'delete bookings', 
            'checkin bookings', 'checkout bookings', 'cancel bookings',
            
            // Rooms
            'view rooms', 'create rooms', 'edit rooms', 'delete rooms',
            'view room availability', 'manage room maintenance',

            // Room Types
            'view room types', 'create room types', 'edit room types', 'delete room types',

            // Rates
            'view room rates', 'create room rates', 'edit room rates', 'delete room rates',

            // packages
            'view packages', 'create packages', 'edit packages', 'delete packages',

            // room amenities
            'view room amenities', 'create room amenities', 'edit room amenities', 'delete room amenities',

            // Guests (and all related to guest tables)
            'view guests', 'create guests', 'edit guests', 'delete guests', 'view guest history',
            'manage guest profiles',
            
            // Reports
            'view reports', 'export reports', 'view financial reports', 'view occupancy reports',
            
            // Settings
            'manage property settings', 'manage users', 'manage rates', 'manage packages',
            'manage taxes', 'manage discounts',
            
            // Financials
            'view invoices', 'create invoices', 'edit invoices', 'process payments',
            'issue refunds', 'view payment history',

            // tax rates
            // 'view tax rates', 'create tax rates', 'edit tax rates', 'delete tax rates',

            // admin single property
            'manage property integrations', 'manage property api keys', 'view property api usage',
            'manage property roles', 'manage property permissions', 'assign property roles',

            
            // Admin (cross-property)
            'manage properties', 'view all properties', 'impersonate users', 'manage system settings',
            'view audit logs', 'manage subscription plans', 'manage subscriptions', 'view system reports',
            'delete users', 'delete properties', 'manage integrations', 'manage api keys', 'view api usage',
            'manage roles', 'assign roles', 'view roles', 'manage permissions', 'view permissions', 'view users', 'create users', 'edit users',

            // activity logs
            'view activity logs', 'clear activity logs',

            // notifications
            'view notifications', 'send notifications', 'manage notification settings',

            // guest clubs
            'manage guest clubs', 'view guest clubs', 'create guest clubs', 'edit guest clubs', 'delete guest clubs',
            'manage guest club members', 'view guest club members', 'add guest club members', 'edit guest club members', 'remove guest club members'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'tenant']);
        }
        
        // Create roles and assign permissions
        $this->createTenantUserRoles();    // Creates super-user and other supported tenant roles
        $this->createPropertyAdminRole();   // Creates property admin role with property-level permissions
        $this->createManagerRole();         // Creates manager role with limited permissions
        $this->createStaffRole();          // Creates staff role with basic permissions
        $this->command->info('Roles and permissions seeded successfully.');
    }

    protected function createTenantUserRoles(): void
    {
        $supportedRoles = \App\Models\Tenant\User::SUPPORTED_ROLES;
        foreach ($supportedRoles as $roleName) {
            // if the role does not exist, create it and assign all permissions
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'tenant']);
            // only assign all permissions to super-user
            if ($role->name === 'super-user') {
                $role->syncPermissions(Permission::all());
            }
        }
    }
	// this will have all the permissions only for 
    protected function createPropertyAdminRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'property-admin', 'guard_name' => 'tenant']);
        $role->syncPermissions([
            'view bookings', 'create bookings', 'edit bookings', 'delete bookings',
            'checkin bookings', 'checkout bookings', 'cancel bookings',
            'view rooms', 'create rooms', 'edit rooms', 'delete rooms', 'manage room types',
            'view room availability', 'manage room maintenance',
            'view guests', 'create guests', 'edit guests', 'delete guests', 'view guest history',
            'manage guest profiles',
            'view reports', 'export reports', 'view financial reports', 'view occupancy reports',
            'manage property settings', 'manage users', 'manage rates', 'manage packages',
            'manage taxes', 'manage discounts',
            'view invoices', 'create invoices', 'edit invoices', 'process payments',
            'issue refunds', 'view payment history',
            'manage guest clubs', 'view guest clubs', 'create guest clubs', 
            'edit guest clubs', 'delete guest clubs',
            'manage guest club members', 'view guest club members',
            'view activity logs',
            'view notifications', 'manage notification settings'
        ]);
    }

    protected function createManagerRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'tenant']);
        $role->syncPermissions([
            'view bookings', 'create bookings', 'edit bookings', 'checkin bookings',
            'checkout bookings', 'cancel bookings',
            'view rooms', 'view room availability',
            'view guests', 'create guests', 'edit guests', 'view guest history',
            'view reports', 'view financial reports', 'view occupancy reports',
            'view invoices', 'process payments', 'issue refunds'
        ]);
    }

    protected function createStaffRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'tenant']);
        $role->syncPermissions([
            'view bookings', 'create bookings', 'checkin bookings', 'checkout bookings',
            'view rooms', 'view room availability',
            'view guests', 'create guests',
            'view invoices', 'process payments'
        ]);
    }
}
