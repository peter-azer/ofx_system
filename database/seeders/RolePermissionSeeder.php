<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = ['create', 'edit','assign', 'delete','view task', 'view all tasks','view teams tasks', 'view dashboard'];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $ownerRole = Role::create(['name' => 'owner']);
        $branchManagerRole = Role::create(['name' => 'branch_manager']);
        $managerRole = Role::create(['name' => 'manager']);
        $leaderRole = Role::create(['name' => 'leader']);
        $employeeRole = Role::create(['name' => 'employee']);
        $accountantRole = Role::create(['name' => 'accountant']);

        $ownerRole->givePermissionTo(Permission::all());
        $branchManagerRole->givePermissionTo(['create posts', 'edit posts']);
        $managerRole->givePermissionTo(['create posts', 'edit posts']);
        $leaderRole->givePermissionTo(['create posts', 'edit posts']);
        $employeeRole->givePermissionTo(['create posts', 'edit posts']);
        $accountantRole->givePermissionTo(['create posts', 'edit posts']);

        // Create test users
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123')
        ]);

        $editorUser = User::create([
            'name' => 'Editor User',
            'email' => 'editor@example.com',
            'password' => bcrypt('password123')
        ]);

        // Assign roles to users
        $adminUser->assignRole('admin');
        $editorUser->assignRole('editor');
}
    }
