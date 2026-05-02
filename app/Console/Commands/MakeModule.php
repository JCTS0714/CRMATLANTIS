<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModule extends Command
{
    protected $signature = 'make:module
        {name : Nombre del modulo}
        {--path= : Ruta base (ej: /operaciones/reportes)}
        {--prefix= : Prefijo de API (ej: operaciones/reportes)}
        {--menu= : Permiso de menu (ej: menu.reportes)}
        {--component= : Nombre de componente Vue (ej: ReportesModule.vue)}';

    protected $description = 'Genera un modulo auto-registrado en menu/vista/permisos sin cableado manual';

    public function handle(): int
    {
        $name = trim((string) $this->argument('name'));
        if ($name === '') {
            $this->error('Nombre de modulo invalido.');
            return self::FAILURE;
        }

        $studly = Str::studly($name);
        $slug = Str::slug($name);
        $resource = Str::snake(Str::pluralStudly($name));

        $path = $this->normalizeDashboardPath((string) ($this->option('path') ?: '/' . Str::kebab(Str::plural($name))));
        $prefix = trim((string) ($this->option('prefix') ?: ltrim($path, '/')));
        $menuPermission = trim((string) ($this->option('menu') ?: ('menu.' . Str::snake($name))));
        $viewPermission = $resource . '.view';

        $componentFile = trim((string) ($this->option('component') ?: ($studly . 'Module.vue')));
        if (!Str::endsWith(Str::lower($componentFile), '.vue')) {
            $componentFile .= '.vue';
        }

        $controllerClass = $studly . 'Controller';
        $controllerPath = base_path('app/Http/Controllers/' . $controllerClass . '.php');
        $componentPath = base_path('resources/js/components/' . $componentFile);

        $generatedRoutesDir = base_path('routes/modules/auth/generated');
        $generatedRoutesPath = $generatedRoutesDir . DIRECTORY_SEPARATOR . $slug . '.php';

        if (!File::exists($controllerPath)) {
            File::put($controllerPath, $this->controllerStub($controllerClass, $resource));
            $this->info("Controlador creado: app/Http/Controllers/{$controllerClass}.php");
        } else {
            $this->warn("Controlador ya existe: app/Http/Controllers/{$controllerClass}.php");
        }

        if (!File::exists($componentPath)) {
            File::put($componentPath, $this->componentStub($name));
            $this->info("Componente creado: resources/js/components/{$componentFile}");
        } else {
            $this->warn("Componente ya existe: resources/js/components/{$componentFile}");
        }

        if (!File::isDirectory($generatedRoutesDir)) {
            File::makeDirectory($generatedRoutesDir, 0755, true);
        }

        if (!File::exists($generatedRoutesPath)) {
            File::put($generatedRoutesPath, $this->generatedRoutesStub($controllerClass, $resource, $prefix));
            $this->info("Rutas API creadas: routes/modules/auth/generated/{$slug}.php");
        } else {
            $this->warn("Rutas API ya existen: routes/modules/auth/generated/{$slug}.php");
        }

        $registered = $this->registerDynamicModule([
            'key' => $slug,
            'label' => Str::headline($name),
            'subtitle' => 'Modulo generado automaticamente',
            'path' => $path,
            'component' => $componentFile,
            'menu_permission' => $menuPermission,
            'view_permission' => $viewPermission,
            'auto_menu' => true,
            'enabled' => true,
        ]);

        if ($registered) {
            $this->info('Modulo registrado en config/modules.php');
        } else {
            $this->warn('No se pudo registrar el modulo automaticamente en config/modules.php');
            $this->line('Registra manualmente la entrada en config/modules.php');
        }

        $this->call('permissions:sync');

        try {
            Artisan::call('optimize:clear');
            $this->info('Cache limpiada con optimize:clear');
        } catch (\Throwable $e) {
            $this->warn('No se pudo limpiar cache automaticamente: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('Modulo generado y sincronizado.');
        $this->line("Ruta SPA: {$path}");
        $this->line("Permisos base: {$resource}.view/create/update/delete + {$menuPermission}");

        return self::SUCCESS;
    }

    protected function normalizeDashboardPath(string $path): string
    {
        $path = '/' . ltrim(trim($path), '/');
        return rtrim($path, '/') ?: '/';
    }

    protected function registerDynamicModule(array $module): bool
    {
        $configPath = base_path('config/modules.php');
        if (!File::exists($configPath)) {
            return false;
        }

        $content = File::get($configPath);
        if (str_contains($content, "'key' => '{$module['key']}'")) {
            return true;
        }

        $entry = "        [\n"
            . "            'key' => '{$module['key']}',\n"
            . "            'label' => '{$module['label']}',\n"
            . "            'subtitle' => '{$module['subtitle']}',\n"
            . "            'path' => '{$module['path']}',\n"
            . "            'component' => '{$module['component']}',\n"
            . "            'menu_permission' => '{$module['menu_permission']}',\n"
            . "            'view_permission' => '{$module['view_permission']}',\n"
            . "            'auto_menu' => " . ($module['auto_menu'] ? 'true' : 'false') . ",\n"
            . "            'enabled' => " . ($module['enabled'] ? 'true' : 'false') . ",\n"
            . "        ],\n";

        $needle = "    ],\n];";
        $position = strrpos($content, $needle);
        if ($position === false) {
            return false;
        }

        $updated = substr($content, 0, $position) . $entry . substr($content, $position);
        File::put($configPath, $updated);

        return true;
    }

    protected function controllerStub(string $controllerClass, string $resource): string
    {
        return <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class {$controllerClass} extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }

    public function store(Request \$request)
    {
        return response()->json(['message' => '{$resource} creado'], 201);
    }

    public function update(Request \$request, \$id)
    {
        return response()->json(['message' => '{$resource} actualizado', 'id' => \$id]);
    }

    public function destroy(\$id)
    {
        return response()->json(['message' => '{$resource} eliminado', 'id' => \$id]);
    }
}
PHP;
    }

    protected function generatedRoutesStub(string $controllerClass, string $resource, string $prefix): string
    {
        $fqcn = "\\App\\Http\\Controllers\\{$controllerClass}";
        $prefix = trim($prefix, '/');

        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('permission:{$resource}.view')
    ->get('/api/{$prefix}', [{$fqcn}::class, 'index'])
    ->name('{$resource}.index');

Route::middleware('permission:{$resource}.create')
    ->post('/api/{$prefix}', [{$fqcn}::class, 'store'])
    ->name('{$resource}.store');

Route::middleware('permission:{$resource}.update')
    ->put('/api/{$prefix}/{id}', [{$fqcn}::class, 'update'])
    ->name('{$resource}.update');

Route::middleware('permission:{$resource}.delete')
    ->delete('/api/{$prefix}/{id}', [{$fqcn}::class, 'destroy'])
    ->name('{$resource}.destroy');
PHP;
    }

    protected function componentStub(string $name): string
    {
        $title = Str::headline($name);

        return <<<VUE
<template>
  <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{$title}</h2>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
      Modulo generado automaticamente. Reemplaza este contenido con tu implementacion.
    </p>
  </section>
</template>
VUE;
    }
}
