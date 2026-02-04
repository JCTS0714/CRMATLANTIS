#!/bin/bash

echo "=== DIAGNÓSTICO COMPLETO DE PROBLEMA ASSETS DINÁMICOS ==="
echo "Fecha: $(date)"
echo

# 1. Verificar archivos compilados existen
echo "1. VERIFICANDO ARCHIVOS COMPILADOS:"
echo "   ContadoresTable-BBgU-2Fk.js existe:"
if [ -f "public/build/assets/ContadoresTable-BBgU-2Fk.js" ]; then
    echo "   ✅ SÍ - $(ls -la public/build/assets/ContadoresTable-BBgU-2Fk.js)"
else
    echo "   ❌ NO - Archivo no encontrado"
fi

echo "   Manifest.json existe:"
if [ -f "public/build/manifest.json" ]; then
    echo "   ✅ SÍ - $(ls -la public/build/manifest.json)"
    echo "   Contenido manifest para ContadoresTable:"
    grep -A 8 "ContadoresTable" public/build/manifest.json || echo "   ❌ No encontrado en manifest"
else
    echo "   ❌ NO - Manifest no encontrado"
fi

echo
echo "2. VERIFICANDO CONFIGURACIÓN DE ENTORNO:"
echo "   APP_URL en .env:"
grep "APP_URL" .env || echo "   ❌ APP_URL no encontrado"

echo "   APP_ENV:"
grep "APP_ENV" .env || echo "   ❌ APP_ENV no encontrado"

echo
echo "3. VERIFICANDO CONFIGURACIÓN VITE:"
echo "   vite.config.js existe:"
if [ -f "vite.config.js" ]; then
    echo "   ✅ SÍ"
    echo "   Configuración base URL:"
    grep -A 5 -B 5 "base\|publicPath" vite.config.js || echo "   No configuración de base URL específica"
else
    echo "   ❌ NO"
fi

echo
echo "4. VERIFICANDO ESTRUCTURA /public/build/:"
echo "   Total archivos en assets:"
ls -la public/build/assets/ | wc -l

echo "   Archivos JS compilados:"
ls -la public/build/assets/*.js | head -10

echo
echo "5. VERIFICANDO PERMISOS:"
echo "   Permisos directorio build:"
ls -ld public/build/
echo "   Permisos directorio assets:"
ls -ld public/build/assets/

echo
echo "6. PROBANDO ACCESO DIRECTO A ARCHIVO:"
echo "   Intentando leer ContadoresTable directamente:"
if [ -r "public/build/assets/ContadoresTable-BBgU-2Fk.js" ]; then
    echo "   ✅ Archivo es legible"
    echo "   Primeras líneas:"
    head -5 "public/build/assets/ContadoresTable-BBgU-2Fk.js"
else
    echo "   ❌ Archivo no es legible"
fi

echo
echo "7. VERIFICANDO CONFIGURACIÓN LARAVEL:"
php artisan config:show app.url 2>/dev/null || echo "   Error obteniendo configuración URL"

echo
echo "=== RECOMENDACIONES ==="
echo
echo "A. Si archivos existen pero no cargan:"
echo "   1. Verificar configuración servidor web"
echo "   2. Verificar APP_URL en producción"
echo "   3. Limpiar caché Laravel: php artisan cache:clear"
echo "   4. Limpiar caché config: php artisan config:clear"
echo "   5. Verificar .htaccess o nginx.conf"
echo
echo "B. Si archivos no existen:"
echo "   1. Ejecutar: npm run build"
echo "   2. Verificar package.json scripts"
echo "   3. Revisar vite.config.js"
echo
echo "C. Para producción:"
echo "   1. APP_URL debe ser: https://new.grupoatlantiscrm.eu"
echo "   2. APP_ENV debe ser: production"
echo "   3. Ejecutar: php artisan config:cache"
echo "   4. Verificar permisos servidor web"