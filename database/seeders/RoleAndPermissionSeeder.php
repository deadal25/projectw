<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat Role
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $ketua = Role::firstOrCreate(['name' => 'ketua']);

        // 2. Buat Permission spesifik
        $permissions = [
            'manage-admins',
            'manage-ketuas',
            'manage-groups',
            'manage-employees',
            'manage-jobs',
            'view-all-groups',
            'view-own-group',
            'do-assessment',
            'export-reports',
            'view-activity-logs',
            'delete-activity-logs',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 3. Assign Permission ke Role
        // Superadmin memiliki semua akses
        $superadmin->givePermissionTo(Permission::all());

        // Admin
        $admin->givePermissionTo([
            'manage-ketuas',
            'manage-groups',
            'manage-employees',
            'manage-jobs',
            'view-all-groups',
            'export-reports',
            'view-activity-logs',
        ]);

        // Ketua
        $ketua->givePermissionTo([
            'view-own-group',
            'manage-jobs',
            'do-assessment',
        ]);
    }
}
