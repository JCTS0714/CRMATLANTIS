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
            'check.lead.permissions' => App\Http\Middleware\CheckLeadPermissions::class,
            'check.campaign.permissions' => App\Http\Middleware\CheckCampaignPermissions::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->call(function () {
            $now = now();
            $specialTypes = ['customer_payment', 'certificate_expiry'];

            // Find due reminders that haven't been sent yet
            $due = CalendarEvent::query()
                ->where(function ($query) use ($specialTypes) {
                    $query->whereNull('event_type')
                        ->orWhereNotIn('event_type', $specialTypes);
                })
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

            $specialEvents = CalendarEvent::query()
                ->whereIn('event_type', $specialTypes)
                ->whereNotNull('assigned_to')
                ->whereNotNull('start_at')
                ->where('start_at', '>=', $now->copy()->subDay())
                ->orderBy('start_at')
                ->limit(300)
                ->get();

            foreach ($specialEvents as $event) {
                foreach ([1440, 0] as $offsetMinutes) {
                    $dueAt = $event->start_at?->copy()->subMinutes($offsetMinutes);
                    if (!$dueAt || $dueAt->greaterThan($now)) {
                        continue;
                    }

                    $inserted = DB::table('calendar_event_reminder_sends')->insertOrIgnore([
                        'calendar_event_id' => $event->id,
                        'reminder_offset_minutes' => $offsetMinutes,
                        'sent_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    if ($inserted !== 1) {
                        continue;
                    }

                    $user = User::query()->find($event->assigned_to);
                    if (!$user) {
                        continue;
                    }

                    $minsUntil = $event->start_at ? $now->diffInMinutes($event->start_at, false) : null;
                    $minsUntil = is_int($minsUntil) ? $minsUntil : null;

                    $payload = [
                        'kind' => 'calendar.reminder',
                        'event_id' => $event->id,
                        'title' => $event->title,
                        'start_at' => optional($event->start_at)->toIso8601String(),
                        'minutes_until' => $minsUntil,
                        'event_type' => $event->event_type,
                        'reminder_offset_minutes' => $offsetMinutes,
                        'url' => '/calendar',
                    ];

                    $user->notify(new CalendarEventReminderNotification($payload));
                }
            }
        })->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
