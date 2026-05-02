<?php

use App\Http\Controllers\Calendar\CalendarEventController;
use App\Http\Controllers\Incidence\IncidenceController;
use App\Http\Controllers\RelatedLookupController;
use App\Http\Controllers\Scrum\ScrumTaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:calendar.view')->group(function () {
    Route::get('/calendar', function () {
        return view('dashboard');
    })->name('calendar.index');

    Route::get('/calendar/events', [CalendarEventController::class, 'index'])->name('calendar.events');
    Route::get('/related-lookup', [RelatedLookupController::class, 'index'])->name('related.lookup');
});

Route::post('/calendar/events', [CalendarEventController::class, 'store'])
    ->middleware('permission:calendar.create')
    ->name('calendar.events.store');

Route::put('/calendar/events/{event}', [CalendarEventController::class, 'update'])
    ->middleware('permission:calendar.update')
    ->name('calendar.events.update');

Route::delete('/calendar/events/{event}', [CalendarEventController::class, 'destroy'])
    ->middleware('permission:calendar.delete')
    ->name('calendar.events.destroy');

Route::get('/scrum/tareas', function () {
    return view('dashboard');
})->name('scrum.tasks');

Route::get('/scrum/tareas/data', [ScrumTaskController::class, 'index'])->name('scrum.tasks.data');
Route::get('/scrum/tareas/responsables', [ScrumTaskController::class, 'users'])->name('scrum.tasks.users');
Route::post('/scrum/tareas', [ScrumTaskController::class, 'store'])->name('scrum.tasks.store');
Route::put('/scrum/tareas/{scrumTask}', [ScrumTaskController::class, 'update'])->name('scrum.tasks.update');
Route::patch('/scrum/tareas/{scrumTask}/status', [ScrumTaskController::class, 'updateStatus'])->name('scrum.tasks.status');
Route::delete('/scrum/tareas/{scrumTask}', [ScrumTaskController::class, 'destroy'])->name('scrum.tasks.destroy');

Route::middleware('permission:incidencias.view')->group(function () {
    Route::get('/incidencias', function () {
        return view('dashboard');
    })->name('incidencias.index');

    Route::get('/incidencias/board', function () {
        return redirect('/backlog');
    })->name('incidencias.board');

    Route::get('/backlog', function () {
        return view('dashboard');
    })->name('backlog.index');

    Route::get('/incidencias/data', [IncidenceController::class, 'tableData'])->name('incidencias.data');
    Route::get('/backlog/board-data', [IncidenceController::class, 'boardData'])->name('backlog.boardData');
});

Route::post('/incidencias/import', [IncidenceController::class, 'importCsv'])
    ->middleware('permission:incidencias.create')
    ->name('incidencias.import');

Route::post('/incidencias', [IncidenceController::class, 'store'])
    ->middleware('permission:incidencias.create')
    ->name('incidencias.store');

Route::put('/incidencias/{incidence}', [IncidenceController::class, 'update'])
    ->middleware('permission:incidencias.update')
    ->name('incidencias.update');

Route::delete('/incidencias/{incidence}', [IncidenceController::class, 'destroy'])
    ->middleware('permission:incidencias.delete')
    ->name('incidencias.destroy');

Route::patch('/incidencias/{incidence}/move-stage', [IncidenceController::class, 'moveStage'])
    ->middleware('permission:incidencias.update')
    ->name('incidencias.moveStage');

Route::patch('/incidencias/reorder', [IncidenceController::class, 'reorder'])
    ->middleware('permission:incidencias.update')
    ->name('incidencias.reorder');

Route::patch('/incidencias/{incidence}/archive', [IncidenceController::class, 'archive'])
    ->middleware('permission:incidencias.update')
    ->name('incidencias.archive');
