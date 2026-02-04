<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Incidence;
use App\Models\IncidenceStage;

echo "Configurando sort_order para incidencias..." . PHP_EOL;

foreach(IncidenceStage::orderBy('sort_order')->get() as $stage) {
    echo "Etapa: {$stage->name}" . PHP_EOL;
    
    $incidences = Incidence::where('stage_id', $stage->id)
                 ->orderByDesc('updated_at')
                 ->get();
    
    foreach($incidences as $index => $incidence) {
        // Para incidencias usamos sort_order ASCENDENTE (1, 2, 3...)
        $sortOrder = $index + 1;
        $incidence->update(['sort_order' => $sortOrder]);
        echo "  Incidencia {$incidence->id} ({$incidence->title}) -> sort_order {$sortOrder}" . PHP_EOL;
    }
}

echo "Listo para testing del backlog!" . PHP_EOL;