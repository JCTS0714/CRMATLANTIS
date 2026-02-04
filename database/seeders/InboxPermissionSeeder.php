<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InboxPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear el permiso si no existe
        $permission = Permission::firstOrCreate([
            'name' => 'menu.inbox',
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);

        // Asignar al rol admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$adminRole->hasPermissionTo('menu.inbox')) {
            $adminRole->givePermissionTo('menu.inbox');
        }

        // Asignar al rol dev
        $devRole = Role::where('name', 'dev')->first();
        if ($devRole && !$devRole->hasPermissionTo('menu.inbox')) {
            $devRole->givePermissionTo('menu.inbox');
        }

        echo "Permiso 'menu.inbox' creado y asignado exitosamente.\n";
    }
}