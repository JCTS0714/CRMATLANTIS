<?php

use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\PostVenta\CertificadoController;
use App\Http\Controllers\PostVenta\ContadorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:customers.view')->group(function () {
    Route::get('/customers', function (Request $request) {
        $query = $request->getQueryString();
        $target = '/postventa/clientes' . ($query ? ('?' . $query) : '');

        return redirect($target);
    })->name('customers.index');

    Route::get('/customers/data', [CustomerController::class, 'data'])->name('customers.data');
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');

    Route::get('/customers/{customer}', [CustomerController::class, 'show'])
        ->whereNumber('customer')
        ->name('customers.show');
});

Route::post('/customers', [CustomerController::class, 'store'])
    ->middleware('permission:customers.create')
    ->name('customers.store');

Route::patch('/customers/{customer}/status', [CustomerController::class, 'updateStatus'])
    ->whereNumber('customer')
    ->middleware('permission:customers.update')
    ->name('customers.status');

Route::post('/customers/import', [CustomerController::class, 'importCsv'])
    ->middleware('permission:customers.create')
    ->name('customers.import');

Route::post('/customers/clear-local', [CustomerController::class, 'clearTableLocal'])
    ->middleware('permission:customers.delete')
    ->name('customers.clear-local');

Route::put('/customers/{customer}', [CustomerController::class, 'update'])
    ->middleware('permission:customers.update')
    ->name('customers.update');

Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
    ->middleware('permission:customers.delete')
    ->name('customers.destroy');

Route::middleware('permission:customers.view')->group(function () {
    Route::get('/postventa/clientes', function () {
        return view('dashboard');
    })->name('postventa.customers');
});

Route::middleware('permission:contadores.view')->group(function () {
    Route::get('/postventa/contadores', function () {
        return view('dashboard');
    })->name('postventa.contadores');

    Route::get('/postventa/contadores/data', [ContadorController::class, 'data'])->name('postventa.contadores.data');
});

Route::post('/postventa/contadores', [ContadorController::class, 'store'])
    ->middleware('permission:contadores.create')
    ->name('postventa.contadores.store');

Route::post('/postventa/contadores/import', [ContadorController::class, 'importCsv'])
    ->middleware('permission:contadores.create')
    ->name('postventa.contadores.import');

Route::post('/postventa/contadores/clear-local', [ContadorController::class, 'clearTableLocal'])
    ->middleware('permission:contadores.delete')
    ->name('postventa.contadores.clear-local');

Route::put('/postventa/contadores/{contador}', [ContadorController::class, 'update'])
    ->middleware('permission:contadores.update')
    ->name('postventa.contadores.update');

Route::delete('/postventa/contadores/{contador}', [ContadorController::class, 'destroy'])
    ->middleware('permission:contadores.delete')
    ->name('postventa.contadores.destroy');

Route::middleware('permission:certificados.view')->group(function () {
    Route::get('/postventa/certificados', function () {
        return view('dashboard');
    })->name('postventa.certificados');

    Route::get('/postventa/certificados/data', [CertificadoController::class, 'data'])->name('postventa.certificados.data');
});

Route::post('/postventa/certificados', [CertificadoController::class, 'store'])
    ->middleware('permission:certificados.create')
    ->name('postventa.certificados.store');

Route::post('/postventa/certificados/import-data', [CertificadoController::class, 'importData'])
    ->middleware('permission:certificados.create')
    ->name('postventa.certificados.importData');

Route::post('/postventa/certificados/import-images', [CertificadoController::class, 'importImages'])
    ->middleware('permission:certificados.create')
    ->name('postventa.certificados.importImages');

Route::put('/postventa/certificados/{certificado}', [CertificadoController::class, 'update'])
    ->middleware('permission:certificados.update')
    ->name('postventa.certificados.update');

Route::delete('/postventa/certificados/{certificado}', [CertificadoController::class, 'destroy'])
    ->middleware('permission:certificados.delete')
    ->name('postventa.certificados.destroy');
