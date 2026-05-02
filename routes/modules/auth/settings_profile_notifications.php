<?php

use App\Http\Controllers\Api\NotificationController as ApiNotificationController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// Configuracion - UI SPA (mismo wrapper que el resto de modulos)
Route::get('/configuracion', function () {
    return view('dashboard');
})->name('settings.index');

Route::post('/configuracion/logo', [SettingsController::class, 'uploadLogo'])
    ->name('settings.logo.upload');

Route::get('/configuracion/logo-path', [SettingsController::class, 'logoPath'])
    ->name('settings.logo.path');

Route::post('/configuracion/logo-mark', [SettingsController::class, 'uploadLogoMark'])
    ->name('settings.logo.mark.upload');

Route::post('/configuracion/logo-full', [SettingsController::class, 'uploadLogoFull'])
    ->name('settings.logo.full.upload');

Route::get('/configuracion/logo-paths', [SettingsController::class, 'logoPaths'])
    ->name('settings.logo.paths');

Route::post('/api/chatbot/query', [ChatbotController::class, 'query']);

// Simple UI for user help chat
Route::get('/chat', function () {
    return view('chat');
})->middleware('auth')->name('chat');

// Embeddable fragment used by floating widget
Route::get('/chat/widget', function () {
    return view('chat-embed');
})->middleware('auth')->name('chat.widget');

// API de notificaciones
Route::prefix('api/notifications')->group(function () {
    Route::post('/closed', [ApiNotificationController::class, 'markClosed'])->name('api.notifications.closed');
    Route::get('/upcoming-events', [ApiNotificationController::class, 'getUpcomingEvents'])->name('api.notifications.upcoming');
    Route::post('/test', [ApiNotificationController::class, 'sendTestNotification'])->name('api.notifications.test');
    Route::put('/preferences', [ApiNotificationController::class, 'updatePreferences'])->name('api.notifications.preferences.update');
    Route::get('/preferences', [ApiNotificationController::class, 'getPreferences'])->name('api.notifications.preferences');
    Route::get('/status', [ApiNotificationController::class, 'getNotificationStatus'])->name('api.notifications.status');
});
