<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles you want available in the app
        $roles = [
            'customer',
            'vendor',
            'admin',
            'super-admin',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Optional: create permissions and attach them
        // $permissions = ['manage users', 'manage products'];
        // foreach ($permissions as $perm) {
        //     Permission::firstOrCreate(['name' => $perm]);
        // }
        //
        // // Give all permissions to admin (example)
        // $admin = Role::firstOrCreate(['name' => 'admin']);
        // $admin->syncPermissions($permissions);
    }
}