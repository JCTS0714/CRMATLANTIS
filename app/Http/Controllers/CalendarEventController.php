<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

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
                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'start' => optional($e->start_at)->toIso8601String(),
                    'end' => optional($e->end_at)->toIso8601String(),
                    'allDay' => (bool) $e->all_day,
                    'extendedProps' => [
                        'description' => $e->description,
                        'location' => $e->location,
                        'reminder_minutes' => $e->reminder_minutes,
                        'related_type' => $e->related_type,
                        'related_id' => $e->related_id,
                    ],
                ];
            })->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'all_day' => ['nullable', 'boolean'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'reminder_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'related_type' => ['nullable', 'string', Rule::in(['lead', 'customer'])],
            'related_id' => ['nullable', 'integer', 'min:1'],
        ]);

        if (($validated['related_type'] ?? null) && !($validated['related_id'] ?? null)) {
            return response()->json([
                'message' => 'El ID relacionado es requerido si eliges tipo relacionado.',
            ], 422);
        }

        $relatedMap = [
            'lead' => \App\Models\Lead::class,
            'customer' => \App\Models\Customer::class,
        ];

        $relatedType = $validated['related_type'] ?? null;
        $relatedId = $validated['related_id'] ?? null;

        $event = new CalendarEvent();
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'all_day' => ['nullable', 'boolean'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'reminder_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'related_type' => ['nullable', 'string', Rule::in(['lead', 'customer'])],
            'related_id' => ['nullable', 'integer', 'min:1'],
        ]);

        if (($validated['related_type'] ?? null) && !($validated['related_id'] ?? null)) {
            return response()->json([
                'message' => 'El ID relacionado es requerido si eliges tipo relacionado.',
            ], 422);
        }

        $relatedMap = [
            'lead' => \App\Models\Lead::class,
            'customer' => \App\Models\Customer::class,
        ];

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
