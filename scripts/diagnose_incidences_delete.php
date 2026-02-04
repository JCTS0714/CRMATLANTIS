<?php

// Script de diagnóstico para verificar rutas y permisos de incidencias
echo "=== DIAGNÓSTICO DE ELIMINACIÓN DE INCIDENCIAS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Verificar si la ruta existe
echo "1. VERIFICANDO RUTAS:\n";
$routes = collect(Route::getRoutes())->filter(function($route) {
    return str_contains($route->uri(), 'incidencias');
})->map(function($route) {
    return [
        'method' => implode('|', $route->methods()),
        'uri' => $route->uri(),
        'name' => $route->getName(),
        'action' => $route->getActionName()
    ];
})->values()->all();

foreach ($routes as $route) {
    echo "   {$route['method']} /{$route['uri']} -> {$route['action']}\n";
}

echo "\n2. VERIFICANDO PERMISOS:\n";
// 2. Verificar permisos existentes
$permissions = DB::table('permissions')
    ->where('name', 'like', '%incidencias%')
    ->pluck('name')
    ->all();

echo "   Permisos encontrados:\n";
foreach ($permissions as $permission) {
    echo "   - {$permission}\n";
}

// 3. Verificar si usuario actual tiene permisos
if (auth()->check()) {
    $user = auth()->user();
    echo "\n3. VERIFICANDO PERMISOS DEL USUARIO ACTUAL:\n";
    echo "   Usuario: {$user->name} (ID: {$user->id})\n";
    echo "   Email: {$user->email}\n";
    
    $userPermissions = $user->getAllPermissions()->pluck('name')->all();
    $incidencePermissions = array_filter($userPermissions, function($perm) {
        return str_contains($perm, 'incidencias');
    });
    
    echo "   Permisos de incidencias:\n";
    foreach ($incidencePermissions as $perm) {
        echo "   - {$perm}\n";
    }
    
    $hasDeletePerm = $user->can('incidencias.delete');
    echo "   ¿Puede eliminar incidencias? " . ($hasDeletePerm ? "SÍ" : "NO") . "\n";
} else {
    echo "\n3. No hay usuario autenticado\n";
}

echo "\n4. VERIFICANDO MIDDLEWARE:\n";
$deleteRoute = Route::getRoutes()->getByName('incidencias.destroy');
if ($deleteRoute) {
    echo "   Ruta 'incidencias.destroy' ENCONTRADA\n";
    echo "   Middleware: " . implode(', ', $deleteRoute->middleware()) . "\n";
} else {
    echo "   ❌ Ruta 'incidencias.destroy' NO ENCONTRADA\n";
}

echo "\n=== RECOMENDACIONES ===\n";
echo "Si la ruta no existe:\n";
echo "   php artisan route:clear\n";
echo "   php artisan cache:clear\n";
echo "\nSi faltan permisos:\n";
echo "   php artisan permissions:sync\n";
echo "   Asignar permisos al rol del usuario\n";