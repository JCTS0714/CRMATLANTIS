<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function roleOptions(Request $request): JsonResponse
    {
        $guard = config('auth.defaults.guard', 'web');

        $roles = Role::query()
            ->where('guard_name', $guard)
            ->orderBy('name')
            ->pluck('name')
            ->values();

        return response()->json([
            'data' => $roles,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['id', 'name', 'email', 'created_at'])],
            'sort_by' => ['nullable', 'string', Rule::in(['id', 'name', 'email', 'created_at'])],
            'dir' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $search = trim((string) ($validated['search'] ?? $validated['q'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 10);
        $sort = (string) ($validated['sort'] ?? $validated['sort_by'] ?? 'id');
        $dir = (string) ($validated['dir'] ?? $validated['sort_direction'] ?? 'desc');

        $columns = ['id', 'name', 'email', 'created_at'];
        if (Schema::hasColumn('users', 'profile_photo_path')) {
            $columns[] = 'profile_photo_path';
        }

        $query = User::query()
            ->select($columns)
            ->with(['roles:id,name']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->orderBy($sort, $dir);

        if ($sort !== 'id') {
            $query->orderByDesc('id');
        }

        $paginator = $query->paginate($perPage)->appends($request->query());

        $data = collect($paginator->items())->map(function (User $user) {
            $photoUrl = $user->profile_photo_path
                ? '/storage/' . ltrim($user->profile_photo_path, '/')
                : null;

            $roles = $user->roles
                ->map(fn ($role) => ['id' => $role->id, 'name' => $role->name])
                ->values();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo_url' => $photoUrl,
                'created_at' => $user->created_at,
                'roles' => $roles,
                'role' => $roles->pluck('name')->first(),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $guard = config('auth.defaults.guard', 'web');

        $canStorePhotoPath = Schema::hasColumn('users', 'profile_photo_path');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'role' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('roles', 'name')->where(fn ($q) => $q->where('guard_name', $guard)),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $photoPath = null;
        if ($canStorePhotoPath && $request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('profile-photos', 'public');
        }

        $attributes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        if ($canStorePhotoPath) {
            $attributes['profile_photo_path'] = $photoPath;
        }

        $user = User::create($attributes);

        $role = $validated['role'] ?? 'employee';
        Role::findOrCreate('employee', $guard);
        $user->syncRoles([$role]);

        return response()->json([
            'message' => 'Usuario creado.',
            'data' => array_merge($user->only(['id', 'name', 'email', 'created_at']), [
                'roles' => [['id' => null, 'name' => $role]],
                'role' => $role,
            ]),
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $guard = config('auth.defaults.guard', 'web');

        $canStorePhotoPath = Schema::hasColumn('users', 'profile_photo_path');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user->id),
            ],
            'role' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('roles', 'name')->where(fn ($q) => $q->where('guard_name', $guard)),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($canStorePhotoPath && $request->hasFile('photo')) {
            $newPath = $request->file('photo')->store('profile-photos', 'public');
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = $newPath;
        }

        $user->save();

        if (array_key_exists('role', $validated)) {
            $role = $validated['role'] ?? 'employee';
            Role::findOrCreate('employee', $guard);
            $user->syncRoles([$role]);
        }

        return response()->json([
            'message' => 'Usuario actualizado.',
            'data' => array_merge($user->only(['id', 'name', 'email', 'created_at']), [
                'roles' => $user->roles()->get(['id', 'name'])->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->values(),
                'role' => $user->roles()->pluck('name')->first(),
            ]),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()?->id === $user->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propio usuario mientras estás autenticado.',
            ], 422);
        }

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado.',
        ]);
    }
}
