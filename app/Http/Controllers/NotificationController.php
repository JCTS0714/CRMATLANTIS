<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $unread = $user->unreadNotifications()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $count = $user->unreadNotifications()->count();

        return response()->json([
            'data' => [
                'count' => (int) $count,
                'notifications' => $unread->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'type' => $n->type,
                        'data' => $n->data,
                        'created_at' => optional($n->created_at)->toIso8601String(),
                    ];
                })->values(),
            ],
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $n = $user->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        return response()->json(['message' => 'OK']);
    }
}
