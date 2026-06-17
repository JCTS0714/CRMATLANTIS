#!/bin/bash
echo "🔧 Script de diagnóstico rápido para Drag & Drop"
echo "=============================================="

# Verificar si las migraciones se ejecutaron
echo "📊 Verificando estructura de tablas..."
php artisan tinker --execute="
echo 'Leads table:';
\Illuminate\Support\Facades\Schema::hasColumn('leads', 'position') ? 'position: ✅' : 'position: ❌';
echo PHP_EOL;

echo 'Incidences table:';
\Illuminate\Support\Facades\Schema::hasColumn('incidences', 'sort_order') ? 'sort_order: ✅' : 'sort_order: ❌';
echo PHP_EOL;
"

# Verificar datos de prueba
echo "📈 Verificando datos existentes..."
php artisan tinker --execute="
echo 'Total leads: ' . \App\Models\Lead::count();
echo PHP_EOL;
echo 'Leads con position = 0: ' . \App\Models\Lead::where('position', 0)->count();
echo PHP_EOL;
echo 'Total incidencias: ' . \App\Models\Incidence::count();
echo PHP_EOL;
echo 'Incidencias con sort_order = 0: ' . \App\Models\Incidence::where('sort_order', 0)->count();
echo PHP_EOL;
"

echo "✅ Diagnóstico completado. Revisa los resultados arriba."
echo "Si hay muchos registros con valor 0, ejecuta el script de corrección."