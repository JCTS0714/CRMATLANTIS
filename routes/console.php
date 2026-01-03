<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\ProspectosCsvImporter;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('crm:import:prospectos {file : Ruta al CSV exportado del sistema anterior} {--dry-run : No escribe en BD, solo valida} {--user= : ID de usuario para created_by} {--update-existing : Actualiza leads existentes (por DNI/RUC) en vez de saltarlos}', function () {
    $file = (string) $this->argument('file');
    $dryRun = (bool) $this->option('dry-run');
    $updateExisting = (bool) $this->option('update-existing');
    $createdBy = $this->option('user');
    $createdBy = is_numeric($createdBy) ? (int) $createdBy : null;

    try {
        $importer = app()->make(ProspectosCsvImporter::class);
        $result = $importer->import($file, [
            'dryRun' => $dryRun,
            'updateExisting' => $updateExisting,
            'createdBy' => $createdBy,
        ]);
    } catch (\Throwable $e) {
        $this->error($e->getMessage() ?: 'No se pudo importar el CSV.');
        return 1;
    }

    $this->info('Importación finalizada');
    $this->line('---');
    $this->line('Creados: '.$result['created']);
    $this->line('Actualizados: '.$result['updated']);
    $this->line('Saltados: '.$result['skipped']);
    $this->line('Inválidos: '.$result['invalid']);
    $this->line('Modo: '.$result['mode']);

    return 0;
})->purpose('Importa prospectos (CSV legado) como leads en etapa Seguimiento');
