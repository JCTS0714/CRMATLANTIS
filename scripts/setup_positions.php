<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Lead;
use App\Models\LeadStage;

echo "Configurando posiciones iniciales para testing..." . PHP_EOL;

foreach(LeadStage::orderBy('sort_order')->get() as $stage) {
    echo "Etapa: {$stage->name}" . PHP_EOL;
    
    $leads = Lead::where('stage_id', $stage->id)
                 ->orderByDesc('updated_at')
                 ->get();
    
    foreach($leads as $index => $lead) {
        $position = $leads->count() - $index;
        $lead->update(['position' => $position]);
        echo "  Lead {$lead->id} ({$lead->name}) -> posición {$position}" . PHP_EOL;
    }
}

echo "Listo para testing!" . PHP_EOL;