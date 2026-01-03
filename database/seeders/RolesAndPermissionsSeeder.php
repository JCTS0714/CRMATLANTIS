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

        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            'leads.view',
            'leads.create',
            'leads.update',
            'leads.delete',

            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',

            'incidencias.view',
            'incidencias.create',
            'incidencias.update',
            'incidencias.delete',

            'contadores.view',
            'contadores.create',
            'contadores.update',
            'contadores.delete',

            'certificados.view',
            'certificados.create',
            'certificados.update',
            'certificados.delete',

            'calendar.view',
            'calendar.create',
            'calendar.update',
            'calendar.delete',

            // Menu visibility (frontend)
            'menu.users',
            'menu.roles',
            'menu.leads',
            'menu.customers',
            'menu.calendar',
            'menu.postventa',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['name' => $permission, 'guard_name' => $guard],
                []
            );
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

        $permissionModels = Permission::query()
            ->where('guard_name', $guard)
            ->whereIn('name', $permissions)
            ->get();

        // Admin: full access
        $adminRole->syncPermissions($permissionModels);

        // Dev: for now same as admin; we will add advanced permissions as requested
        $devRole->syncPermissions($permissionModels);

        // Employee: intentionally no Users permissions (per requirements)
        $employeeRole->syncPermissions([]);
    }
}
