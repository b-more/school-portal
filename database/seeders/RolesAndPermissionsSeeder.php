<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view members',
            'add members',
            'edit members',
            'delete members',
            'view cell groups',
            'add cell groups',
            'edit cell groups',
            'delete cell groups',
            'manage roles',
            'manage permissions'
            // Add other permissions...
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        Role::create(['name' => 'super-admin'])
            ->givePermissionTo(Permission::all());

        Role::create(['name' => 'branch-admin'])
            ->givePermissionTo([
                'view members',
                'add members',
                'edit members'
            ]);

        Role::create(['name' => 'cell-leader'])
            ->givePermissionTo([
                'view members',
                'view cell groups'
            ]);

        Role::create(['name' => 'department-head']);
        Role::create(['name' => 'finance-admin']);
        Role::create(['name' => 'user']);
    }
}