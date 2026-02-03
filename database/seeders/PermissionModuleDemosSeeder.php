<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class {$className} extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        $perms = [
            '{$resource}.view',
            '{$resource}.create',
            '{$resource}.update',
            '{$resource}.delete',
            'menu.{$resource}',
        ];

        foreach ($perms as $p) {
            Permission::query()->updateOrCreate(['name' => $p, 'guard_name' => $guard], []);
        }
    }
}