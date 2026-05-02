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
            $logoMarkManifest = storage_path('app/public/settings/logo_mark.json');
            $logoFullManifest = storage_path('app/public/settings/logo_full.json');

            $appLogoMark = null;
            if (file_exists($logoMarkManifest)) {
                $payload = json_decode(file_get_contents($logoMarkManifest), true);
                if (is_array($payload) && !empty($payload['path']) && file_exists(public_path($payload['path']))) {
                    $appLogoMark = '/' . ltrim($payload['path'], '/');
                }
            }

            $appLogoFull = null;
            if (file_exists($logoFullManifest)) {
                $payload = json_decode(file_get_contents($logoFullManifest), true);
                if (is_array($payload) && !empty($payload['path']) && file_exists(public_path($payload['path']))) {
                    $appLogoFull = '/' . ltrim($payload['path'], '/');
                }
            }

            $appLogoMark = $appLogoMark
                ?: (file_exists(public_path('storage/settings/logo_mark.png'))
                    ? '/storage/settings/logo_mark.png'
                    : '/images/logo_alta_calidad.png');

            $appLogoFull = $appLogoFull
                ?: (file_exists(public_path('storage/settings/logo_full.png'))
                    ? '/storage/settings/logo_full.png'
                    : '');
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

            $modulesPayload = [
                'dynamic' => collect(config('modules.dynamic', []))
                    ->filter(function ($module) {
                        return is_array($module)
                            && !empty($module['enabled'])
                            && !empty($module['path'])
                            && !empty($module['component'])
                            && !empty($module['label']);
                    })
                    ->map(function ($module) {
                        return [
                            'key' => (string) ($module['key'] ?? ''),
                            'label' => (string) ($module['label'] ?? ''),
                            'subtitle' => (string) ($module['subtitle'] ?? ''),
                            'path' => (string) ($module['path'] ?? ''),
                            'component' => (string) ($module['component'] ?? ''),
                            'menu_permission' => (string) ($module['menu_permission'] ?? ''),
                            'view_permission' => (string) ($module['view_permission'] ?? ''),
                            'auto_menu' => (bool) ($module['auto_menu'] ?? true),
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        @endphp

        <script>
            window.__AUTH_USER__ = @json($authPayload);
            window.__APP_MODULES__ = @json($modulesPayload);
            window.__APP_LOGO_MARK__ = @json($appLogoMark);
            window.__APP_LOGO_FULL__ = @json($appLogoFull);
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/notifications.js', 'resources/js/calendar-notifications.js'])
    </head>
    <body>
        <div id="app"></div>
        @include('partials.chat-widget')
    </body>
</html>
