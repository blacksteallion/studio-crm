<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define the Permissions Matrix
        $permissions = [
            // Dashboard
            'view dashboard',
            
            // Customers
            'view customers', 'create customers', 'edit customers', 'delete customers', 'export customers', 'toggle customer status',
            
            // Staff
            'view staff', 'create staff', 'edit staff', 'delete staff', 'export staff', 'toggle staff status',
            
            // Inquiries
            'view inquiries', 'create inquiries', 'edit inquiries', 'delete inquiries', 'export inquiries', 'manage inquiry logs', 'convert inquiries',
            
            // Bookings
            'view bookings', 'create bookings', 'edit bookings', 'delete bookings', 'export bookings', 'view booking calendar',
            
            // Orders
            'view orders', 'create orders', 'edit orders', 'delete orders', 'export orders', 'download order pdf',
            
            // Payments
            'view payments', 'add payments', 'delete payments', 'export payments',
            
            // Expenses
            'view expenses', 'create expenses', 'edit expenses', 'delete expenses', 'export expenses',
            
            // Products & Services
            'view products', 'create products', 'edit products', 'delete products',
            
            // Reports
            'view reports', 'export reports',
            
            // System Admin
            'manage settings', 'manage integrations', 'manage roles'
        ];

        // 2. Create Permissions in the Database
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. Create 'Super Admin' role and assign ALL permissions to it
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Note: Additional roles (Sales, Accounts, etc.) can be created via the UI later!
    }
}