<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');

Route::middleware('permission:roles.view')->group(function () {
    Route::get('/roles', function () {
        return view('dashboard');
    })->name('roles.index');

    Route::get('/roles/data', [RoleController::class, 'data'])->name('roles.data');
    Route::get('/roles/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
});

Route::post('/roles', [RoleController::class, 'store'])
    ->middleware('permission:roles.create')
    ->name('roles.store');

Route::put('/roles/{role}', [RoleController::class, 'update'])
    ->middleware('permission:roles.update')
    ->name('roles.update');

Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
    ->middleware('permission:roles.delete')
    ->name('roles.destroy');

Route::middleware('permission:users.view')->group(function () {
    Route::get('/users', function () {
        return view('dashboard');
    })->name('users.index');

    Route::get('/users/data', [UserController::class, 'index'])->name('users.data');
    Route::get('/users/role-options', [UserController::class, 'roleOptions'])->name('users.roleOptions');
});

Route::post('/users', [UserController::class, 'store'])
    ->middleware('permission:users.create')
    ->name('users.store');

Route::put('/users/{user}', [UserController::class, 'update'])
    ->middleware('permission:users.update')
    ->name('users.update');

Route::delete('/users/{user}', [UserController::class, 'destroy'])
    ->middleware('permission:users.delete')
    ->name('users.destroy');
