<?php
// database/seeders/tenant/RolesAndPermissionsSeeder.php
namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Config;
use App\Models\Role;
use App\Models\Permission;

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
            'view rooms', 'create rooms', 'edit rooms', 'delete rooms', 'manage room types',
            'view room availability', 'manage room maintenance',
            
            // Guests
            'view guests', 'create guests', 'edit guests', 'delete guests', 'view guest history',
            'manage guest profiles',
            
            // Reports
            'view reports', 'export reports', 'view financial reports', 'view occupancy reports',
            
            // Settings
            'manage company settings', 'manage users', 'manage rates', 'manage packages',
            'manage taxes', 'manage discounts',
            
            // Financials
            'view invoices', 'create invoices', 'edit invoices', 'process payments',
            'issue refunds', 'view payment history',
            
            // Admin (cross-company)
            'manage companies', 'view all companies', 'impersonate users', 'manage system settings',
            'view audit logs', 'manage subscription plans', 'manage subscriptions', 'view system reports',
            'delete users', 'delete companies', 'manage integrations', 'manage api keys', 'view api usage',
            'manage roles', 'manage permissions', 'assign roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'tenant']);
        }
        
        // Create roles and assign permissions
        $this->createTenantUserRoles();
        // $this->createCompanyAdminRole();
        // $this->createManagerRole();
        // $this->createStaffRole();
        $this->command->info('Roles and permissions seeded successfully.');
    }

    protected function createTenantUserRoles(): void
    {
        $supportedRoles = \App\Models\User::SUPPORTED_TENANT_ROLES;
        foreach ($supportedRoles as $role) {
            // if the role does not exist, create it and assign all permissions
            // will the create in the tenant database table or globally? How does this work with multi-tenancy? -
            $role = Role::firstOrCreate(['name' => $role, 'guard_name' => 'tenant']);
            // only assign all permissions to super-user
            if ($role->name === 'super-user') {
                $role->syncPermissions(Permission::all());
            }
            else {
                // give them limited permissions for now, can be customized later
                $role->syncPermissions([
                    'view bookings', 'create bookings', 'edit bookings', 'checkin bookings',
                    'checkout bookings', 'cancel bookings',
                    'view rooms', 'view room availability',
                    'view guests', 'create guests', 'edit guests', 'view guest history',
                    'view reports', 'view financial reports', 'view occupancy reports',
                    'view invoices', 'process payments', 'issue refunds'
                ]);
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
            'manage company settings', 'manage users', 'manage rates', 'manage packages',
            'manage taxes', 'manage discounts',
            'view invoices', 'create invoices', 'edit invoices', 'process payments',
            'issue refunds', 'view payment history'
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
