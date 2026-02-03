<?php

namespace App\Http\Controllers\Role;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $guard = config('auth.defaults.guard', 'web');

        $roles = Role::query()
            ->where('guard_name', $guard)
            ->with(['permissions:id,name'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $roles->map(function (Role $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->values(),
                ];
            })->values(),
        ]);
    }

    public function permissions(Request $request): JsonResponse
    {
        $guard = config('auth.defaults.guard', 'web');

        $permissions = Permission::query()
            ->where('guard_name', $guard)
            ->orderBy('name')
            ->pluck('name')
            ->values();

        return response()->json([
            'data' => $permissions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $guard = config('auth.defaults.guard', 'web');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where(fn ($q) => $q->where('guard_name', $guard)),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:255'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $guard,
        ]);

        $permissionNames = collect($validated['permissions'] ?? [])->filter()->values();

        if ($permissionNames->isNotEmpty()) {
            $permissionModels = Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', $permissionNames)
                ->get();

            if ($permissionModels->count() !== $permissionNames->count()) {
                return response()->json([
                    'message' => 'Uno o más permisos no son válidos.',
                ], 422);
            }

            $role->syncPermissions($permissionModels);
        } else {
            $role->syncPermissions([]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'message' => 'Rol creado.',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions()->pluck('name')->values(),
            ],
        ], 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $guard = config('auth.defaults.guard', 'web');

        if ($role->guard_name !== $guard) {
            return response()->json([
                'message' => 'Rol inválido.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where(fn ($q) => $q->where('guard_name', $guard))
                    ->ignore($role->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:255'],
        ]);

        $role->name = $validated['name'];
        $role->save();

        $permissionNames = collect($validated['permissions'] ?? [])->filter()->values();

        if ($permissionNames->isNotEmpty()) {
            $permissionModels = Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', $permissionNames)
                ->get();

            if ($permissionModels->count() !== $permissionNames->count()) {
                return response()->json([
                    'message' => 'Uno o más permisos no son válidos.',
                ], 422);
            }

            $role->syncPermissions($permissionModels);
        } else {
            $role->syncPermissions([]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'message' => 'Rol actualizado.',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions()->pluck('name')->values(),
            ],
        ]);
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        $guard = config('auth.defaults.guard', 'web');

        if ($role->guard_name !== $guard) {
            return response()->json([
                'message' => 'Rol inválido.',
            ], 404);
        }

        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'message' => 'Rol eliminado.',
        ]);
    }
}
