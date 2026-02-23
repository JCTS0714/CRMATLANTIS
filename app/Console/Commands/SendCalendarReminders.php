<?php

namespace App\Console\Commands;

use App\Models\CalendarEvent;
use App\Models\User;
use App\Notifications\CalendarEventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SendCalendarReminders extends Command
{
    protected $signature = 'calendar:send-reminders';
    protected $description = 'Send due calendar reminders to assignees.';

    public function handle(): int
    {
        $now = Carbon::now();
        $sent = 0;
        $specialTypes = ['customer_payment', 'certificate_expiry'];

        CalendarEvent::query()
            ->where(function ($query) use ($specialTypes) {
                $query->whereNull('event_type')
                    ->orWhereNotIn('event_type', $specialTypes);
            })
            ->whereNotNull('reminder_at')
            ->whereNull('reminded_at')
            ->where('reminder_at', '<=', $now)
            ->whereNotNull('assigned_to')
            ->with('assignee')
            ->orderBy('id')
            ->chunkById(200, function ($events) use (&$sent, $now) {
                foreach ($events as $event) {
                    $user = $event->assignee;
                    if (!$user) {
                        $event->reminded_at = $now;
                        $event->save();
                        continue;
                    }

                    $payload = [
                        'type' => 'calendar_reminder',
                        'event_id' => $event->id,
                        'title' => $event->title,
                        'start_at' => $event->start_at?->toIso8601String(),
                        'reminder_minutes' => $event->reminder_minutes,
                        'created_at' => $now->toIso8601String(),
                    ];

                    $user->notify(new CalendarEventReminderNotification($payload));

                    $event->reminded_at = $now;
                    $event->save();
                    $sent++;
                }
            });

        $specialEvents = CalendarEvent::query()
            ->whereIn('event_type', $specialTypes)
            ->whereNotNull('assigned_to')
            ->whereNotNull('start_at')
            ->where('start_at', '>=', $now->copy()->subDay())
            ->orderBy('id')
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

                $payload = [
                    'type' => 'calendar_reminder',
                    'event_id' => $event->id,
                    'title' => $event->title,
                    'start_at' => $event->start_at?->toIso8601String(),
                    'reminder_minutes' => $offsetMinutes,
                    'event_type' => $event->event_type,
                    'created_at' => $now->toIso8601String(),
                ];

                $user->notify(new CalendarEventReminderNotification($payload));
                $sent++;
            }
        }

        $this->info("Reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
