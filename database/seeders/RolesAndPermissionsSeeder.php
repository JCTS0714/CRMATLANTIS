<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        // Use the new permissions sync command to create permissions discovered in routes
        try {
            // When running seeders via artisan, this will trigger the command
            \Artisan::call('permissions:sync');
        } catch (\Throwable $e) {
            // If calling artisan is not available in this context, fallback to no-op
        }

        $adminRole = Role::query()->updateOrCreate(
            ['name' => 'admin', 'guard_name' => $guard],
            []
        );
        $employeeRole = Role::query()->updateOrCreate(
            ['name' => 'employee', 'guard_name' => $guard],
            []
        );
        $devRole = Role::query()->updateOrCreate(
            ['name' => 'dev', 'guard_name' => $guard],
            []
        );

        // Assign to admin/dev all permissions for the current guard
        $permissionModels = Permission::query()
            ->where('guard_name', $guard)
            ->get();

        $adminRole->syncPermissions($permissionModels);
        $devRole->syncPermissions($permissionModels);

        // Employee: intentionally no Users permissions (per requirements)
        $employeeRole->syncPermissions([]);
    }
}
