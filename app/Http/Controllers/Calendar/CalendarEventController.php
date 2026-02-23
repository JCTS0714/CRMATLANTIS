<?php

namespace App\Http\Controllers\Calendar;

use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class CalendarEventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $userId = (int) $request->user()->id;
        $start = Carbon::parse($validated['start']);
        $end = Carbon::parse($validated['end']);

        $events = CalendarEvent::query()
            ->where('assigned_to', $userId)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_at', '<=', $start)->where('end_at', '>=', $end);
                    });
            })
            ->orderBy('start_at')
            ->get();

        return response()->json([
            'data' => $events->map(function (CalendarEvent $e) {
                $eventType = $e->event_type ?? 'general';

                $palette = match ($eventType) {
                    'customer_payment' => ['#059669', '#047857', '#ffffff'],
                    'certificate_expiry' => ['#d97706', '#b45309', '#ffffff'],
                    'lead_followup' => ['#4f46e5', '#4338ca', '#ffffff'],
                    'meeting' => ['#7c3aed', '#6d28d9', '#ffffff'],
                    default => ['#2563eb', '#1d4ed8', '#ffffff'],
                };

                $isAllDay = (bool) $e->all_day;
                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'start' => $isAllDay ? optional($e->start_at)->toDateString() : optional($e->start_at)->toIso8601String(),
                    'end' => $isAllDay ? optional($e->end_at)->toDateString() : optional($e->end_at)->toIso8601String(),
                    'allDay' => $isAllDay,
                    'backgroundColor' => $palette[0],
                    'borderColor' => $palette[1],
                    'textColor' => $palette[2],
                    'extendedProps' => [
                        'event_type' => $e->event_type,
                        'description' => $e->description,
                        'location' => $e->location,
                        'reminder_minutes' => $e->reminder_minutes,
                        'related_type' => $e->related_type,
                        'related_id' => $e->related_id,
                        'meta' => $e->meta,
                    ],
                ];
            })->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => ['nullable', 'string', Rule::in(['general', 'meeting', 'lead_followup', 'customer_payment', 'certificate_expiry'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'all_day' => ['nullable', 'boolean'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'reminder_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'related_type' => ['nullable', 'string', Rule::in(['lead', 'customer', 'certificate'])],
            'related_id' => ['nullable', 'integer', 'min:1'],
            'meta' => ['nullable', 'array'],
        ]);

        if (($validated['related_type'] ?? null) && !($validated['related_id'] ?? null)) {
            return response()->json([
                'message' => 'El ID relacionado es requerido si eliges tipo relacionado.',
            ], 422);
        }

        $eventType = $validated['event_type'] ?? 'general';
        if ($eventType === 'customer_payment' && (($validated['related_type'] ?? null) !== 'customer' || !($validated['related_id'] ?? null))) {
            return response()->json([
                'message' => 'Los eventos de pago deben estar vinculados a un cliente.',
            ], 422);
        }

        if ($eventType === 'certificate_expiry' && (($validated['related_type'] ?? null) !== 'certificate' || !($validated['related_id'] ?? null))) {
            return response()->json([
                'message' => 'Los vencimientos de certificado deben estar vinculados a un certificado.',
            ], 422);
        }

        $relatedMap = [
            'lead' => \App\Models\Lead::class,
            'customer' => \App\Models\Customer::class,
            'certificate' => \App\Models\Certificado::class,
        ];

        $relatedType = $validated['related_type'] ?? null;
        $relatedId = $validated['related_id'] ?? null;

        $event = new CalendarEvent();
        $event->event_type = $eventType;
        $event->title = $validated['title'];
        $event->description = $validated['description'] ?? null;
        $event->location = $validated['location'] ?? null;
        $event->all_day = (bool) ($validated['all_day'] ?? false);
        $event->start_at = Carbon::parse($validated['start_at']);
        $event->end_at = isset($validated['end_at']) ? Carbon::parse($validated['end_at']) : null;

        $event->reminder_minutes = $validated['reminder_minutes'] ?? null;
        $event->reminder_at = $event->reminder_minutes
            ? (clone $event->start_at)->subMinutes((int) $event->reminder_minutes)
            : null;
        $event->reminded_at = null;

        if ($relatedType) {
            $event->related_type = $relatedMap[$relatedType] ?? null;
            $event->related_id = $relatedId;
        }

        $event->meta = $validated['meta'] ?? null;

        $event->created_by = $request->user()->id;
        $event->assigned_to = $request->user()->id;
        $event->save();

        return response()->json([
            'message' => 'Evento creado.',
            'data' => $event,
        ], 201);
    }

    public function update(Request $request, CalendarEvent $event): JsonResponse
    {
        if ((int) $event->assigned_to !== (int) $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'event_type' => ['nullable', 'string', Rule::in(['general', 'meeting', 'lead_followup', 'customer_payment', 'certificate_expiry'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'all_day' => ['nullable', 'boolean'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'reminder_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'related_type' => ['nullable', 'string', Rule::in(['lead', 'customer', 'certificate'])],
            'related_id' => ['nullable', 'integer', 'min:1'],
            'meta' => ['nullable', 'array'],
        ]);

        if (($validated['related_type'] ?? null) && !($validated['related_id'] ?? null)) {
            return response()->json([
                'message' => 'El ID relacionado es requerido si eliges tipo relacionado.',
            ], 422);
        }

        $nextEventType = $validated['event_type'] ?? $event->event_type ?? 'general';
        $currentEventType = $event->event_type ?? 'general';
        $isCurrentAutoType = in_array($currentEventType, ['customer_payment', 'certificate_expiry'], true);

        if ($isCurrentAutoType && $nextEventType !== $currentEventType) {
            return response()->json([
                'message' => 'No se puede cambiar el tipo de un evento automático.',
            ], 422);
        }

        if ($nextEventType === 'customer_payment' && (($validated['related_type'] ?? null) !== 'customer' || !($validated['related_id'] ?? null))) {
            return response()->json([
                'message' => 'Los eventos de pago deben estar vinculados a un cliente.',
            ], 422);
        }

        if ($nextEventType === 'certificate_expiry' && (($validated['related_type'] ?? null) !== 'certificate' || !($validated['related_id'] ?? null))) {
            return response()->json([
                'message' => 'Los vencimientos de certificado deben estar vinculados a un certificado.',
            ], 422);
        }

        if ($isCurrentAutoType) {
            $currentRelatedTypeShort = $event->related_type === \App\Models\Customer::class
                ? 'customer'
                : ($event->related_type === \App\Models\Certificado::class ? 'certificate' : null);

            if (($validated['related_type'] ?? null) !== $currentRelatedTypeShort || (int) ($validated['related_id'] ?? 0) !== (int) ($event->related_id ?? 0)) {
                return response()->json([
                    'message' => 'No se puede cambiar la relación de un evento automático.',
                ], 422);
            }
        }

        $relatedMap = [
            'lead' => \App\Models\Lead::class,
            'customer' => \App\Models\Customer::class,
            'certificate' => \App\Models\Certificado::class,
        ];

        $event->event_type = $nextEventType;
        $event->title = $validated['title'];
        $event->description = $validated['description'] ?? null;
        $event->location = $validated['location'] ?? null;
        $event->all_day = (bool) ($validated['all_day'] ?? false);
        $event->start_at = Carbon::parse($validated['start_at']);
        $event->end_at = isset($validated['end_at']) ? Carbon::parse($validated['end_at']) : null;

        $event->reminder_minutes = $validated['reminder_minutes'] ?? null;
        $event->reminder_at = $event->reminder_minutes
            ? (clone $event->start_at)->subMinutes((int) $event->reminder_minutes)
            : null;
        $event->reminded_at = null; // re-arm reminder after edits

        $relatedType = $validated['related_type'] ?? null;
        $relatedId = $validated['related_id'] ?? null;
        if ($relatedType) {
            $event->related_type = $relatedMap[$relatedType] ?? null;
            $event->related_id = $relatedId;
        } else {
            $event->related_type = null;
            $event->related_id = null;
        }

        $event->meta = $validated['meta'] ?? null;

        $event->save();

        return response()->json([
            'message' => 'Evento actualizado.',
            'data' => $event->fresh(),
        ]);
    }

    public function destroy(Request $request, CalendarEvent $event): JsonResponse
    {
        if ((int) $event->assigned_to !== (int) $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $event->delete();

        return response()->json([
            'message' => 'Evento eliminado.',
        ]);
    }
}
