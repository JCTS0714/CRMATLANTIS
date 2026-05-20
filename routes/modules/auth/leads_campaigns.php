<?php

use App\Http\Controllers\Inbox\FacturaEnvioController;
use App\Http\Controllers\Integrations\KapsoIntegrationController;
use App\Http\Controllers\Lead\LeadController;
use App\Http\Controllers\Lead\LeadDataController;
use App\Http\Controllers\Lead\LeadImportController;
use App\Http\Controllers\Lead\LostLeadController;
use App\Http\Controllers\Lead\WaitingLeadController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:leads.view')->group(function () {
    Route::get('/leads', function () {
        return view('dashboard');
    })->name('leads.index');

    Route::get('/leads/list', function () {
        return view('dashboard');
    })->name('leads.list');

    // Refactored: Data queries moved to LeadDataController
    Route::get('/leads/data', [LeadDataController::class, 'tableData'])->name('leads.data');
    Route::get('/leads/board-data', [LeadDataController::class, 'boardData'])->name('leads.boardData');

    Route::get('/desistidos', function () {
        return view('dashboard');
    })->name('desistidos.index');

    Route::get('/desistidos/data', [LostLeadController::class, 'index'])->name('desistidos.data');

    Route::get('/desistidos/{lostLead}', [LostLeadController::class, 'show'])
        ->whereNumber('lostLead')
        ->name('desistidos.show');

    Route::get('/espera', function () {
        return view('dashboard');
    })->name('espera.index');

    Route::get('/espera/data', [WaitingLeadController::class, 'index'])->name('espera.data');

    Route::get('/espera/{waitingLead}', [WaitingLeadController::class, 'show'])
        ->whereNumber('waitingLead')
        ->name('espera.show');
});

Route::patch('/desistidos/{lostLead}', [LostLeadController::class, 'update'])
    ->whereNumber('lostLead')
    ->middleware('permission:leads.update')
    ->name('desistidos.update');

Route::patch('/espera/{waitingLead}', [WaitingLeadController::class, 'update'])
    ->whereNumber('waitingLead')
    ->middleware('permission:leads.update')
    ->name('espera.update');

Route::post('/espera/{waitingLead}/reactivate', [WaitingLeadController::class, 'reactivate'])
    ->whereNumber('waitingLead')
    ->middleware('permission:leads.create')
    ->name('espera.reactivate');

Route::post('/leads', [LeadController::class, 'store'])
    ->middleware('permission:leads.create')
    ->name('leads.store');

Route::put('/leads/{lead}', [LeadController::class, 'update'])
    ->middleware('permission:leads.update')
    ->name('leads.update');

// Refactored: Import moved to LeadImportController
Route::post('/leads/import', [LeadImportController::class, 'import'])
    ->middleware('permission:leads.create')
    ->name('leads.import');

Route::post('/leads/import/prospectos', [LeadImportController::class, 'import'])
    ->middleware('permission:leads.create')
    ->name('leads.import.prospectos');

Route::post('/desistidos/import', [LostLeadController::class, 'importCsv'])
    ->middleware('permission:leads.create')
    ->name('desistidos.import');

Route::post('/espera/import', [WaitingLeadController::class, 'importCsv'])
    ->middleware('permission:leads.create')
    ->name('espera.import');

Route::patch('/leads/{lead}/move-stage', [LeadController::class, 'moveStage'])
    ->middleware('permission:leads.update')
    ->name('leads.moveStage');

// Refactored: Reorder moved to LeadDataController
Route::patch('/leads/reorder', [LeadDataController::class, 'reorder'])
    ->middleware('permission:leads.update')
    ->name('leads.reorder');

Route::patch('/leads/{lead}/archive', [LeadController::class, 'archive'])
    ->middleware('permission:leads.update')
    ->name('leads.archive');

Route::post('/leads/{lead}/desist', [LostLeadController::class, 'createFromLead'])
    ->middleware('permission:leads.update')
    ->name('leads.desist');

Route::post('/leads/{lead}/wait', [WaitingLeadController::class, 'createFromLead'])
    ->middleware('permission:leads.update')
    ->name('leads.wait');

Route::middleware('permission:menu.inbox')->group(function () {
    Route::get('/api/integraciones/kapso/status', [KapsoIntegrationController::class, 'status'])
        ->name('integraciones.kapso.status');

    Route::post('/api/integraciones/kapso/test', [KapsoIntegrationController::class, 'test'])
        ->name('integraciones.kapso.test');

    Route::get('/api/facturas/pendientes', [FacturaEnvioController::class, 'pendientes'])
        ->name('facturas.pendientes');

    Route::get('/api/facturas/diagnostico', [FacturaEnvioController::class, 'diagnostico'])
        ->name('facturas.diagnostico');

    Route::post('/api/facturas/fijar-permisos', [FacturaEnvioController::class, 'fijarPermisos'])
        ->name('facturas.fijarPermisos');

    Route::get('/api/facturas/plantillas', [FacturaEnvioController::class, 'plantillas'])
        ->name('facturas.plantillas');

    Route::post('/api/facturas/plantillas', [FacturaEnvioController::class, 'crearPlantilla'])
        ->name('facturas.plantillas.crear');

    Route::put('/api/facturas/plantillas/{plantillaId}', [FacturaEnvioController::class, 'actualizarPlantilla'])
        ->whereNumber('plantillaId')
        ->name('facturas.plantillas.actualizar');

    Route::delete('/api/facturas/plantillas/{plantillaId}', [FacturaEnvioController::class, 'eliminarPlantilla'])
        ->whereNumber('plantillaId')
        ->name('facturas.plantillas.eliminar');

    Route::post('/api/facturas/preparar', [FacturaEnvioController::class, 'preparar'])
        ->name('facturas.preparar');

    Route::post('/api/facturas/{pagoId}/enviar-whatsapp', [FacturaEnvioController::class, 'enviarWhatsapp'])
        ->whereNumber('pagoId')
        ->name('facturas.enviarWhatsapp');

    Route::post('/api/facturas/{pagoId}/marcar-enviada', [FacturaEnvioController::class, 'marcarEnviada'])
        ->whereNumber('pagoId')
        ->name('facturas.marcarEnviada');

    Route::post('/api/facturas/{pagoId}/enviar-email', [FacturaEnvioController::class, 'enviarEmail'])
        ->whereNumber('pagoId')
        ->name('facturas.enviarEmail');

    Route::patch('/api/facturas/clientes/{clienteId}', [FacturaEnvioController::class, 'updateCliente'])
        ->whereNumber('clienteId')
        ->name('facturas.updateCliente');

    Route::get('/leads/whatsapp', function () {
        return redirect('/inbox/facturas');
    });

    Route::get('/leads/email', function () {
        return redirect('/inbox/facturas');
    });
});

// Permite sincronizar pendientes de facturas al terminar importación en Postventa,
// incluso para usuarios que no tienen acceso al módulo Inbox completo.
Route::post('/api/facturas/pagos/sync-mes-actual', [FacturaEnvioController::class, 'syncMesActual'])
    ->middleware('permission:menu.inbox|customers.create')
    ->name('facturas.pagos.syncMesActual.postventa');
