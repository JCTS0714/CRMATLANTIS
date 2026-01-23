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
            $appLogoMark = file_exists(public_path('storage/settings/logo_mark.png'))
                ? '/storage/settings/logo_mark.png'
                : '/images/logo_alta_calidad.png';

            $appLogoFull = file_exists(public_path('storage/settings/logo_full.png'))
                ? '/storage/settings/logo_full.png'
                : '';
            $authPayload = $user
                ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_path' => $user->profile_photo_path,
                    'profile_photo_url' => $user->profile_photo_path
                        ? '/storage/' . ltrim($user->profile_photo_path, '/')
                        : null,
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
            window.__APP_LOGO_MARK__ = @json($appLogoMark);
            window.__APP_LOGO_FULL__ = @json($appLogoFull);
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
