#!/usr/bin/env bash
set -euo pipefail

# Script para preparar storage, permisos e instalar entradas de cron en Hostinger
# Rutas adaptadas a la información proporcionada por el usuario.

PROJECT_PUBLIC="/home/u652153415/domains/grupoatlantiscrm.eu/public_html/new/public"
PROJECT_ROOT="/home/u652153415/domains/grupoatlantiscrm.eu/public_html/new"
PHP_BIN="/usr/bin/php"
USER_OWNER="u652153415"

echo "Proyecto raíz: $PROJECT_ROOT"

echo "Creando carpetas necesarias y ajustando permisos..."
mkdir -p "$PROJECT_ROOT/storage/logs" "$PROJECT_ROOT/bootstrap/cache"
chown -R "$USER_OWNER":"$USER_OWNER" "$PROJECT_ROOT/storage" "$PROJECT_ROOT/bootstrap/cache" || true
chmod -R 775 "$PROJECT_ROOT/storage" "$PROJECT_ROOT/bootstrap/cache" || true

echo "Creando archivos de log si no existen..."
touch "$PROJECT_ROOT/storage/logs/schedule.log" "$PROJECT_ROOT/storage/logs/queue.log"

LINE1="* * * * * cd $PROJECT_ROOT && $PHP_BIN artisan schedule:run >> $PROJECT_ROOT/storage/logs/schedule.log 2>&1"
LINE2="* * * * * cd $PROJECT_ROOT && $PHP_BIN artisan queue:work --once --timeout=60 --tries=3 >> $PROJECT_ROOT/storage/logs/queue.log 2>&1"

TMP_CRON="/tmp/grupoatlantis_cron_$(date +%s).txt"

echo "Preparando crontab temporal: $TMP_CRON"
(crontab -l 2>/dev/null || true) > "$TMP_CRON"

if ! grep -Fqx "$LINE1" "$TMP_CRON"; then
  echo "$LINE1" >> "$TMP_CRON"
  echo "Añadida línea: schedule:run"
else
  echo "La línea schedule:run ya existe en crontab."
fi

if ! grep -Fqx "$LINE2" "$TMP_CRON"; then
  echo "$LINE2" >> "$TMP_CRON"
  echo "Añadida línea: queue:work --once"
else
  echo "La línea queue:work ya existe en crontab."
fi

echo "Instalando crontab..."
crontab "$TMP_CRON"
rm -f "$TMP_CRON"

echo "Instalación completa. Verifica con: crontab -l"
echo "Revisa logs en: $PROJECT_ROOT/storage/logs/schedule.log"

exit 0
