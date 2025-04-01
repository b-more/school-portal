<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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
            // Member Management
            'view members',
            'create members',
            'edit members',
            'delete members',
            
            // Cell Group Management
            'view cell groups',
            'create cell groups',
            'edit cell groups',
            'delete cell groups',
            
            // Department Management
            'view departments',
            'create departments',
            'edit departments',
            'delete departments',
            
            // Branch Management
            'view branches',
            'create branches',
            'edit branches',
            'delete branches',
            
            // Service Management
            'view services',
            'create services',
            'edit services',
            'delete services',
            
            // Financial Management
            'view finances',
            'create finances',
            'edit finances',
            'delete finances',
            
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',
            'manage permissions'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        Role::create(['name' => 'super-admin'])
            ->givePermissionTo(Permission::all());

        Role::create(['name' => 'branch-admin'])
            ->givePermissionTo([
                'view members', 'create members', 'edit members',
                'view cell groups', 'create cell groups', 'edit cell groups',
                'view departments', 'create departments', 'edit departments',
                'view services', 'create services', 'edit services',
                'view finances', 'create finances', 'edit finances',
                'view users'
            ]);

        Role::create(['name' => 'cell-leader'])
            ->givePermissionTo([
                'view members',
                'view cell groups', 'edit cell groups',
                'view services'
            ]);

        Role::create(['name' => 'department-head'])
            ->givePermissionTo([
                'view members',
                'view departments', 'edit departments',
                'view services'
            ]);

        Role::create(['name' => 'finance-admin'])
            ->givePermissionTo([
                'view finances', 'create finances', 'edit finances'
            ]);

        // Create default super admin user
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@hkc.org',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('super-admin');
    }
}