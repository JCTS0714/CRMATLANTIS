# Script de diagnostico y correccion para Drag and Drop
# ===================================================

Write-Host "Diagnostico Drag and Drop - CRM Atlantis" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

Write-Host "`nPaso 1: Verificando estructura de tablas..." -ForegroundColor Green
php artisan tinker --execute="
if (\Illuminate\Support\Facades\Schema::hasColumn('leads', 'position')) {
    echo 'leads.position: OK' . PHP_EOL;
} else {
    echo 'leads.position: ERROR - EJECUTAR MIGRACION' . PHP_EOL;
}

if (\Illuminate\Support\Facades\Schema::hasColumn('incidences', 'sort_order')) {
    echo 'incidences.sort_order: OK' . PHP_EOL;  
} else {
    echo 'incidences.sort_order: ERROR - EJECUTAR MIGRACION' . PHP_EOL;
}
"

Write-Host "`nPaso 2: Verificando datos existentes..." -ForegroundColor Green
php artisan tinker --execute="
echo 'LEADS:' . PHP_EOL;
echo 'Total: ' . \App\Models\Lead::count() . PHP_EOL;
echo 'Con position = 0: ' . \App\Models\Lead::where('position', 0)->count() . PHP_EOL;
echo 'Sin position (null): ' . \App\Models\Lead::whereNull('position')->count() . PHP_EOL;
echo '' . PHP_EOL;
echo 'INCIDENCIAS:' . PHP_EOL;
echo 'Total: ' . \App\Models\Incidence::count() . PHP_EOL;
echo 'Con sort_order = 0: ' . \App\Models\Incidence::where('sort_order', 0)->count() . PHP_EOL;
echo 'Sin sort_order (null): ' . \App\Models\Incidence::whereNull('sort_order')->count() . PHP_EOL;
"

Write-Host "`nDeseas corregir los datos automaticamente? (Escribe SI para continuar)" -ForegroundColor Yellow
$response = Read-Host "Respuesta"

if ($response -eq "SI") {
    Write-Host "`nPaso 3: Corrigiendo datos de LEADS..." -ForegroundColor Green
    
    php artisan tinker --execute="
use App\Models\Lead;
use App\Models\LeadStage;
use Illuminate\Support\Facades\DB;

DB::transaction(function() {
    \$stages = LeadStage::orderBy('sort_order')->get();
    \$totalFixed = 0;
    
    foreach (\$stages as \$stage) {
        echo 'Etapa: ' . \$stage->name . PHP_EOL;
        
        \$leads = Lead::where('stage_id', \$stage->id)
                     ->orderByDesc('updated_at')
                     ->get();
        
        foreach (\$leads as \$index => \$lead) {
            \$newPosition = \$leads->count() - \$index;
            \$lead->update(['position' => \$newPosition]);
            \$totalFixed++;
        }
    }
    
    echo 'Total leads corregidos: ' . \$totalFixed . PHP_EOL;
});
"
    
    Write-Host "`nPaso 4: Corrigiendo datos de INCIDENCIAS..." -ForegroundColor Green
    
    php artisan tinker --execute="
use App\Models\Incidence;
use App\Models\IncidenceStage;
use Illuminate\Support\Facades\DB;

DB::transaction(function() {
    \$stages = IncidenceStage::orderBy('sort_order')->get();
    \$totalFixed = 0;
    
    foreach (\$stages as \$stage) {
        echo 'Etapa: ' . \$stage->name . PHP_EOL;
        
        \$incidences = Incidence::where('stage_id', \$stage->id)
                               ->orderByDesc('updated_at')
                               ->get();
        
        foreach (\$incidences as \$index => \$incidence) {
            \$newSortOrder = \$index + 1;
            \$incidence->update(['sort_order' => \$newSortOrder]);
            \$totalFixed++;
        }
    }
    
    echo 'Total incidencias corregidas: ' . \$totalFixed . PHP_EOL;
});
"
    
    Write-Host "`nCorreccion completada!" -ForegroundColor Green
    Write-Host "Para probar:" -ForegroundColor Cyan
    Write-Host "   1. Abre /leads (kanban)" -ForegroundColor White
    Write-Host "   2. Abre /backlog (kanban)" -ForegroundColor White
    Write-Host "   3. Intenta arrastrar elementos dentro de la misma columna" -ForegroundColor White
    
} else {
    Write-Host "Operacion cancelada." -ForegroundColor Red
}