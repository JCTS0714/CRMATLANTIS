<?php

namespace App\Console\Commands;

use App\Models\CalendarEvent;
use App\Notifications\CalendarEventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendCalendarReminders extends Command
{
    protected $signature = 'calendar:send-reminders';
    protected $description = 'Send due calendar reminders to assignees.';

    public function handle(): int
    {
        $now = Carbon::now();
        $sent = 0;

        CalendarEvent::query()
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

        $this->info("Reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
