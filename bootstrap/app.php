<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Models\CalendarEvent;
use App\Models\User;
use App\Notifications\CalendarEventReminderNotification;
use Illuminate\Support\Facades\DB;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->call(function () {
            $now = now();

            // Find due reminders that haven't been sent yet
            $due = CalendarEvent::query()
                ->whereNotNull('reminder_at')
                ->whereNull('reminded_at')
                ->where('reminder_at', '<=', $now)
                ->where('start_at', '>=', $now->copy()->subDay())
                ->orderBy('reminder_at')
                ->limit(200)
                ->get();

            if ($due->isEmpty()) return;

            DB::transaction(function () use ($due, $now) {
                foreach ($due as $event) {
                    // Lock per event by updating reminded_at only once
                    $updated = CalendarEvent::query()
                        ->whereKey($event->id)
                        ->whereNull('reminded_at')
                        ->update(['reminded_at' => $now]);

                    if ($updated !== 1) continue;

                    $user = User::query()->find($event->assigned_to);
                    if (!$user) continue;

                    $minsUntil = $event->start_at ? $now->diffInMinutes($event->start_at, false) : null;
                    $minsUntil = is_int($minsUntil) ? $minsUntil : null;

                    $payload = [
                        'kind' => 'calendar.reminder',
                        'event_id' => $event->id,
                        'title' => $event->title,
                        'start_at' => optional($event->start_at)->toIso8601String(),
                        'minutes_until' => $minsUntil,
                        'url' => '/calendar',
                    ];

                    $user->notify(new CalendarEventReminderNotification($payload));
                }
            });
        })->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
