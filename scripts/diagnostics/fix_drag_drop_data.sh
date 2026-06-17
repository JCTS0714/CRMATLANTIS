#!/bin/bash
echo "🔧 Script de corrección de datos para Drag & Drop"
echo "================================================"

echo "⚠️  IMPORTANTE: Este script actualizará datos en la base de datos."
echo "     Asegúrate de tener un backup antes de continuar."
echo ""
read -p "¿Continuar? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Operación cancelada."
    exit 1
fi

echo "🔄 Corrigiendo datos de LEADS..."
php artisan tinker --execute="
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

DB::transaction(function() {
    // Obtener todas las etapas de leads
    \$stages = \App\Models\LeadStage::orderBy('sort_order')->get();
    
    foreach (\$stages as \$stage) {
        echo 'Procesando etapa: ' . \$stage->name . PHP_EOL;
        
        // Obtener leads de esta etapa ordenados por updated_at desc
        \$leads = Lead::where('stage_id', \$stage->id)
                     ->orderByDesc('updated_at')
                     ->get();
        
        // Asignar posiciones (descendente, el más reciente tiene position más alta)
        foreach (\$leads as \$index => \$lead) {
            \$newPosition = \$leads->count() - \$index;
            \$lead->update(['position' => \$newPosition]);
            echo '  Lead ' . \$lead->id . ' -> position: ' . \$newPosition . PHP_EOL;
        }
    }
    
    echo 'Leads corregidos: ✅' . PHP_EOL;
});
"

echo "🔄 Corrigiendo datos de INCIDENCIAS..."
php artisan tinker --execute="
use App\Models\Incidence;
use Illuminate\Support\Facades\DB;

DB::transaction(function() {
    // Obtener todas las etapas de incidencias
    \$stages = \App\Models\IncidenceStage::orderBy('sort_order')->get();
    
    foreach (\$stages as \$stage) {
        echo 'Procesando etapa: ' . \$stage->name . PHP_EOL;
        
        // Obtener incidencias de esta etapa ordenadas por updated_at desc
        \$incidences = Incidence::where('stage_id', \$stage->id)
                               ->orderByDesc('updated_at')
                               ->get();
        
        // Asignar sort_order (ascendente, la más reciente tiene sort_order más bajo)
        foreach (\$incidences as \$index => \$incidence) {
            \$newSortOrder = \$index + 1;
            \$incidence->update(['sort_order' => \$newSortOrder]);
            echo '  Incidencia ' . \$incidence->id . ' -> sort_order: ' . \$newSortOrder . PHP_EOL;
        }
    }
    
    echo 'Incidencias corregidas: ✅' . PHP_EOL;
});
"

echo "✅ Corrección completada!"
echo "🧪 Para probar:"
echo "   1. Abre /leads (kanban)"
echo "   2. Abre /backlog (kanban)"
echo "   3. Intenta arrastrar elementos dentro de la misma columna"
echo ""
echo "💡 Si sigue sin funcionar, revisa los logs de Laravel:"
echo "   tail -f storage/logs/laravel.log"