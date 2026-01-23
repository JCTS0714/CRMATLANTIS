<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900 dark:text-slate-100">
        @php($loginBackground = asset('images/fondo_login.jpg'))

        <div id="animated-bg" class="fixed inset-0 z-0"></div>

        <div class="min-h-screen relative z-10 flex flex-col sm:justify-center items-center px-4 py-10">
            <div class="absolute inset-0 pointer-events-none bg-gradient-to-br from-slate-950/30 via-slate-900/20 to-slate-950/30"></div>

            <div class="relative w-full flex flex-col sm:justify-center items-center">
            <a href="/" class="mb-6 flex flex-col items-center gap-3">
                @php($loginLogo = file_exists(public_path('storage/settings/logo_mark.png')) ? asset('storage/settings/logo_mark.png') : asset('images/logo_alta_calidad.png'))
                <img src="{{ $loginLogo }}" alt="{{ config('app.name') }}" class="w-14 h-14 rounded-xl object-contain shadow-sm" />
                <div class="text-center">
                    <div class="text-white text-xl font-semibold leading-tight">CRM ATLANTIS</div>
                    <div class="text-slate-300 text-sm">Acceso al sistema interno</div>
                </div>
            </a>

            <div class="w-full sm:max-w-md bg-white/95 dark:bg-slate-950/70 backdrop-blur border border-slate-200/70 dark:border-white/10 shadow-sm overflow-hidden rounded-xl">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-white/10">
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Iniciar sesión</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Ingresa tus credenciales para continuar</p>
                </div>
                <div class="px-6 py-5">
                    {{ $slot }}
                </div>
            </div>
            </div>
        </div>
    </body>
</html>
