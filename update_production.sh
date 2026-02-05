#!/bin/bash
# Comandos para ejecutar en producción después del git pull

echo "🔄 Actualizando dependencias de Composer..."
composer install --optimize-autoloader --no-dev

echo "🔄 Ejecutando migraciones..."
php artisan migrate --force

echo "🔄 Ejecutando seeder para permiso de Bandeja de entrada..."
php artisan db:seed --class=InboxPermissionSeeder --force

echo "🔄 Sincronizando permisos desde rutas..."
php artisan permissions:sync

echo "🔄 Limpiando cachés..."
php artisan config:clear
php artisan cache:clear
php artisan permission:cache-reset

echo "🔄 Compilando assets..."
npm ci
npm run build

echo "✅ Actualización completada"
echo ""
echo "🔍 Verificando permisos menu.* creados:"
php artisan permission:show | grep menu