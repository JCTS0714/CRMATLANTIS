<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Sincroniza permisos desde las rutas y crea permisos estándar por recurso (view,create,update,delete) y menu.*';

    public function handle(): int
    {
        $guard = config('auth.defaults.guard', 'web');

        $this->info('Recolectando permisos desde las rutas...');

        $routes = Route::getRoutes()->getIterator();

        $found = [];

        foreach ($routes as $route) {
            $middleware = $route->gatherMiddleware();
            foreach ($middleware as $m) {
                if (str_starts_with($m, 'permission:')) {
                    $payload = substr($m, strlen('permission:'));
                    // permissions can be separated by | or ,
                    $parts = preg_split('/[|,]/', $payload);
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p === '') {
                            continue;
                        }
                        $found[] = $p;
                    }
                }
            }
        }

        // Include dynamic module permissions from central registry.
        $dynamicModules = collect(config('modules.dynamic', []))
            ->filter(fn ($module) => is_array($module) && ! empty($module['enabled']))
            ->values();

        foreach ($dynamicModules as $module) {
            $menuPermission = trim((string) ($module['menu_permission'] ?? ''));
            $viewPermission = trim((string) ($module['view_permission'] ?? ''));

            if ($menuPermission !== '') {
                $found[] = $menuPermission;
            }

            if ($viewPermission !== '') {
                $found[] = $viewPermission;
            }
        }

        $found = array_values(array_unique($found));

        $this->info('Permisos detectados: '.count($found));

        // Ensure each detected permission exists
        foreach ($found as $perm) {
            Permission::query()->updateOrCreate([
                'name' => $perm,
                'guard_name' => $guard,
            ], []);
        }

        // For each resource (prefix before dot) create standard actions and menu.*
        $resources = [];
        foreach ($found as $perm) {
            if (strpos($perm, '.') !== false) {
                [$resource] = explode('.', $perm, 2);
                $resources[$resource] = true;
            }
        }

        $standardActions = ['view', 'create', 'update', 'delete'];

        foreach (array_keys($resources) as $res) {
            foreach ($standardActions as $action) {
                $name = sprintf('%s.%s', $res, $action);
                Permission::query()->updateOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ], []);
            }

            // menu permission
            $menuName = 'menu.'.$res;
            Permission::query()->updateOrCreate([
                'name' => $menuName,
                'guard_name' => $guard,
            ], []);
        }

        // Group-level menus used by frontend navigation
        $menuGroups = [
            'menu.postventa',
        ];

        foreach ($menuGroups as $menuPermission) {
            Permission::query()->updateOrCreate([
                'name' => $menuPermission,
                'guard_name' => $guard,
            ], []);
        }

        // Keep admin/superadmin roles updated with newly created permissions.
        $rolesToSync = ['admin', 'administrador', 'super-admin', 'superadmin'];
        $allGuardPermissions = Permission::query()
            ->where('guard_name', $guard)
            ->pluck('name')
            ->all();

        foreach ($rolesToSync as $roleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', $guard)->first();
            if (! $role) {
                continue;
            }

            $role->syncPermissions($allGuardPermissions);
            $this->info("Rol '{$roleName}' actualizado con permisos sincronizados.");
        }

        $total = Permission::query()->where('guard_name', $guard)->count();
        $this->info("Permisos sincronizados. Total permisos para guard '{$guard}': {$total}");

        // Clear cache
        if (function_exists('\Spatie\Permission\PermissionRegistrar')) {
            try {
                app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return 0;
    }
}
