<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Marcar notificación como cerrada
     */
    public function markClosed(Request $request)
    {
        $request->validate([
            'notificationId' => 'required|string',
            'closedAt' => 'required|date'
        ]);

        Log::info('Notificación cerrada', [
            'notification_id' => $request->notificationId,
            'closed_at' => $request->closedAt,
            'user_id' => auth()->id()
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Obtener eventos próximos para notificaciones
     */
    public function getUpcomingEvents(Request $request)
    {
        $user = auth()->user();
        $minutesAhead = $request->input('minutes', 60); // Por defecto 1 hora
        
        $upcomingEvents = CalendarEvent::where('user_id', $user->id)
            ->where('start_datetime', '>', now())
            ->where('start_datetime', '<=', now()->addMinutes($minutesAhead))
            ->orderBy('start_datetime')
            ->get();

        return response()->json([
            'events' => $upcomingEvents->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start_datetime' => $event->start_at,
                    'end_datetime' => $event->end_at,
                    'start_time' => $event->start_at->format('H:i'),
                    'end_time' => $event->end_at->format('H:i'),
                    'color' => $event->color ?? '#3b82f6',
                    'reminder_minutes' => $event->reminder_minutes
                ];
            })
        ]);
    }

    /**
     * Programar notificación inmediata (para pruebas)
     */
    public function sendTestNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'type' => 'string|in:test,calendar,reminder'
        ]);

        $user = auth()->user();

        // En un entorno real, aquí enviarías la notificación push
        // Por ahora, solo registramos que se solicitó
        Log::info('Notificación de prueba solicitada', [
            'user_id' => $user->id,
            'title' => $request->title,
            'body' => $request->body,
            'type' => $request->type ?? 'test'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notificación enviada',
            'notification' => [
                'title' => $request->title,
                'body' => $request->body,
                'type' => $request->type ?? 'test',
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Configurar preferencias de notificaciones
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'calendar_notifications' => 'boolean',
            'sound_enabled' => 'boolean',
            'reminder_minutes' => 'array',
            'reminder_minutes.*' => 'integer|min:0|max:1440' // Máximo 24 horas
        ]);

        $user = auth()->user();

        // Actualizar preferencias en el perfil del usuario
        $preferences = $user->preferences ?? [];
        
        if ($request->has('calendar_notifications')) {
            $preferences['calendar_notifications'] = $request->calendar_notifications;
        }
        
        if ($request->has('sound_enabled')) {
            $preferences['notification_sound'] = $request->sound_enabled;
        }
        
        if ($request->has('reminder_minutes')) {
            $preferences['reminder_minutes'] = $request->reminder_minutes;
        }

        $user->update(['preferences' => $preferences]);

        return response()->json([
            'status' => 'success',
            'message' => 'Preferencias actualizadas',
            'preferences' => $preferences
        ]);
    }

    /**
     * Obtener preferencias de notificaciones
     */
    public function getPreferences()
    {
        $user = auth()->user();
        $preferences = $user->preferences ?? [];

        $defaultPreferences = [
            'calendar_notifications' => true,
            'notification_sound' => true,
            'reminder_minutes' => [15, 5]
        ];

        return response()->json([
            'preferences' => array_merge($defaultPreferences, $preferences)
        ]);
    }

    /**
     * Obtener estado de notificaciones del navegador
     */
    public function getNotificationStatus()
    {
        $user = auth()->user();
        
        return response()->json([
            'user_id' => $user->id,
            'server_time' => now()->toISOString(),
            'timezone' => config('app.timezone'),
            'notifications_enabled' => $user->preferences['calendar_notifications'] ?? true
        ]);
    }
}