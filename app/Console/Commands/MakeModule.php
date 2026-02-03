<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModule extends Command
{
    protected $signature = 'make:module {name : Nombre del módulo (singular, en español idealmente)} {--prefix= : Ruta base (ej: postventa/certificados)}';

    protected $description = 'Genera controlador, rutas y seeder de permisos para un nuevo módulo';

    public function handle(): int
    {
        $name = trim($this->argument('name'));
        if ($name === '') {
            $this->error('Nombre de módulo inválido.');
            return 1;
        }

        $studly = Str::studly($name);
        $resource = Str::lower(Str::plural($name)); // e.g. certificados
        $prefix = $this->option('prefix') ?: $resource;

        // Paths
        $controllerDir = base_path('app/Http/Controllers');
        $controllerPath = $controllerDir . DIRECTORY_SEPARATOR . $studly . 'Controller.php';

        $routesDir = base_path('routes/modules');
        $routesPath = $routesDir . DIRECTORY_SEPARATOR . $resource . '.php';

        $seederDir = base_path('database/seeders');
        $seederClass = 'PermissionModule' . Str::studly($resource) . 'Seeder';
        $seederPath = $seederDir . DIRECTORY_SEPARATOR . $seederClass . '.php';

        // Create controller
        if (! File::exists($controllerPath)) {
            $controllerContent = $this->controllerStub($studly, $resource);
            File::put($controllerPath, $controllerContent);
            $this->info("Controlador creado: app/Http/Controllers/{$studly}Controller.php");
        } else {
            $this->warn("Controlador ya existe: app/Http/Controllers/{$studly}Controller.php");
        }

        // Create routes dir and file
        if (! File::isDirectory($routesDir)) {
            File::makeDirectory($routesDir, 0755, true);
        }

        if (! File::exists($routesPath)) {
            $routesContent = $this->routesStub($studly, $resource, $prefix);
            File::put($routesPath, $routesContent);
            $this->info("Archivo de rutas creado: routes/modules/{$resource}.php");
        } else {
            $this->warn("Archivo de rutas ya existe: routes/modules/{$resource}.php");
        }

        // Ensure routes/web.php includes the modules file
        $webRoutes = base_path('routes/web.php');
        $requireLine = "require __DIR__.'/modules/{$resource}.php';";
        $webContent = File::get($webRoutes);
        if (strpos($webContent, $requireLine) === false) {
            // Insert before the existing require __DIR__.'/auth.php'; or append at end
            $authRequire = "require __DIR__.'/auth.php';";
            if (strpos($webContent, $authRequire) !== false) {
                $webContent = str_replace($authRequire, $requireLine . "\n\n" . $authRequire, $webContent);
            } else {
                $webContent .= "\n" . $requireLine . "\n";
            }
            File::put($webRoutes, $webContent);
            $this->info("Se añadió inclusión de rutas en routes/web.php");
        } else {
            $this->info('La inclusión de rutas ya existe en routes/web.php');
        }

        // Create seeder
        if (! File::exists($seederPath)) {
            $seederContent = $this->seederStub($seederClass, $resource);
            File::put($seederPath, $seederContent);
            $this->info("Seeder de permisos creado: database/seeders/{$seederClass}.php");
        } else {
            $this->warn("Seeder ya existe: database/seeders/{$seederClass}.php");
        }

        $this->info('Generación completada.');
        $this->line('Pasos sugeridos:');
        $this->line("- Revisa/ajusta routes/modules/{$resource}.php para adaptarlo a tus endpoints.");
        $this->line("- Ejecuta: php artisan db:seed --class={$seederClass} para crear los permisos del módulo.");
        $this->line("- Si usas control de versiones, añade los archivos generados al commit.");

        return 0;
    }

    protected function controllerStub(string $studly, string $resource): string
    {
        return <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class {$studly}Controller extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function data(Request \$request)
    {
        return response()->json(['data' => []]);
    }

    public function store(Request \$request)
    {
        return response()->json(['message' => 'Creado'], 201);
    }

    public function update(Request \$request, \$id)
    {
        return response()->json(['message' => 'Actualizado']);
    }

    public function destroy(\$id)
    {
        return response()->json(['message' => 'Eliminado']);
    }
}
PHP;
    }

    protected function routesStub(string $studly, string $resource, string $prefix): string
    {
        $controllerFq = "\\App\\Http\\Controllers\\{$studly}Controller";
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Rutas del módulo {$resource}
    Route::middleware('permission:{$resource}.view')->group(function () {
        Route::get('/{$prefix}', function () { return view('dashboard'); })->name('{$prefix}.index');
        Route::get('/{$prefix}/data', [{$controllerFq}::class, 'data'])->name('{$prefix}.data');
    });

    Route::post('/{$prefix}', [{$controllerFq}::class, 'store'])->middleware('permission:{$resource}.create')->name('{$prefix}.store');
    Route::put('/{$prefix}/{id}', [{$controllerFq}::class, 'update'])->middleware('permission:{$resource}.update')->name('{$prefix}.update');
    Route::delete('/{$prefix}/{id}', [{$controllerFq}::class, 'destroy'])->middleware('permission:{$resource}.delete')->name('{$prefix}.destroy');
});

PHP;
    }

    protected function seederStub(string $className, string $resource): string
    {
        $guard = config('auth.defaults.guard', 'web');
        return <<<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class {$className} extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        $perms = [
            '{$resource}.view',
            '{$resource}.create',
            '{$resource}.update',
            '{$resource}.delete',
            'menu.{$resource}',
        ];

        foreach ($perms as $p) {
            Permission::query()->updateOrCreate(['name' => $p, 'guard_name' => $guard], []);
        }
    }
}
PHP;
    }
}
