<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>CRM Atlantis</title>

        @php
            $user = auth()->user();
            $guard = config('auth.defaults.guard', 'web');
            $authPayload = $user
                ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->values()->all(),
                    'permissions' => $user->hasRole('admin')
                        ? \Spatie\Permission\Models\Permission::query()
                            ->where('guard_name', $guard)
                            ->pluck('name')
                            ->values()
                            ->all()
                        : $user->getAllPermissions()->pluck('name')->values()->all(),
                ]
                : null;
        @endphp

        <script>
            window.__AUTH_USER__ = @json($authPayload);
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
