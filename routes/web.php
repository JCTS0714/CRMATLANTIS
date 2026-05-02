<?php

use App\Http\Controllers\EmailUnsubscribeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Public unsubscribe endpoint for email campaigns
Route::get('/email/unsubscribe', [EmailUnsubscribeController::class, 'unsubscribe'])->name('email.unsubscribe');

Route::middleware('auth')->group(function () {
    require __DIR__.'/modules/authenticated.php';
});

require __DIR__.'/modules/demos.php';

require __DIR__.'/auth.php';
