<?php

use Illuminate\Support\Facades\Route;

$dynamicModules = collect(config('modules.dynamic', []))
    ->filter(function ($module) {
        return is_array($module)
            && ! empty($module['enabled'])
            && ! empty($module['path'])
            && str_starts_with((string) $module['path'], '/');
    })
    ->values();

foreach ($dynamicModules as $module) {
    $key = (string) ($module['key'] ?? ('module-'.md5((string) $module['path'])));
    $path = (string) $module['path'];
    $viewPermission = trim((string) ($module['view_permission'] ?? ''));

    $route = Route::get($path, function () {
        return view('dashboard');
    })->name('modules.'.$key);

    if ($viewPermission !== '') {
        $route->middleware('permission:'.$viewPermission);
    }
}
