<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\RequestMetricsMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            RequestMetricsMiddleware::class,
        ]);

        $middleware->alias([
            'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('calendar:send-reminders')
            ->everyMinute()
            ->withoutOverlapping(1)
            ->runInBackground();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/facturas/*') || $request->is('api/integraciones/kapso/*')) {
                $status = method_exists($e, 'getStatusCode') ? (int) $e->getStatusCode() : 500;
                if ($status < 400 || $status > 599) {
                    $status = 500;
                }

                if ($status >= 500) {
                    Log::error('API exception', [
                        'path' => $request->path(),
                        'method' => $request->method(),
                        'message' => $e->getMessage(),
                        'exception' => $e::class,
                    ]);
                }

                return response()->json([
                    'message' => $e->getMessage() !== '' ? $e->getMessage() : 'Error interno del servidor.',
                    'error_code' => 'API_UNHANDLED_EXCEPTION',
                    'details' => [
                        'exception' => $e::class,
                    ],
                ], $status);
            }

            return null;
        });
    })->create();
