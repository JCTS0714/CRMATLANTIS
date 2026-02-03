<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Rutas del módulo demos
    Route::middleware('permission:demos.view')->group(function () {
        Route::get('/demo', function () { return view('dashboard'); })->name('demo.index');
        Route::get('/demo/data', [\App\Http\Controllers\DemoController::class, 'data'])->name('demo.data');
    });

    Route::post('/demo', [\App\Http\Controllers\DemoController::class, 'store'])->middleware('permission:demos.create')->name('demo.store');
    Route::put('/demo/{id}', [\App\Http\Controllers\DemoController::class, 'update'])->middleware('permission:demos.update')->name('demo.update');
    Route::delete('/demo/{id}', [\App\Http\Controllers\DemoController::class, 'destroy'])->middleware('permission:demos.delete')->name('demo.destroy');
});
