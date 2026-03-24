<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles & permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions (Shield already generated most, but you can add custom)
        Permission::create(['name' => 'export_items']);
        Permission::create(['name' => 'manage_settings']);
        // ... add more custom ones

        // Create roles and assign existing permissions
        $superAdmin = Role::use(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all()); // everything

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view_any_item', 'create_item', 'update_item', 'delete_item',
            // add more...
            'view_any_sale', 'create_sale', /* etc */
        ]);

        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'view_any_item', 'view_any_sale', 'view_any_purchase',
            // limited create/delete
        ]);

        $cashier = Role::create(['name' => 'cashier']);
        $cashier->givePermissionTo([
            'view_any_sale', 'create_sale', // POS only
            // very limited
        ]);
    }
}
