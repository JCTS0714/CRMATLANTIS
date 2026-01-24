# Calendario: Scheduler + Cron (Hostinger)

Fecha: 2026-01-23

## Objetivo
Habilitar el envío automático de recordatorios del calendario según `reminder_minutes`.

## Estado actual (funcionando)
- Comando de recordatorios en `calendar:send-reminders`.
- Scheduler ejecuta el comando cada minuto.
- Cron en Hostinger dispara `schedule:run` cada minuto.
- Notificaciones llegan correctamente al usuario asignado.

## Archivos clave
- app/Console/Kernel.php
- app/Console/Commands/SendCalendarReminders.php
- app/Notifications/CalendarEventReminderNotification.php
- app/Http/Controllers/CalendarEventController.php

## Cron en Hostinger
Ruta final usada:

`/usr/bin/php /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new/artisan schedule:run >> /dev/null 2>&1`

Nota: en el panel, si ya agrega `/usr/bin/php /home/u652153415/`, entonces solo se escribe:

`domains/grupoatlantiscrm.eu/public_html/new/artisan schedule:run >> /dev/null 2>&1`

## Cómo validar
1. Crear un evento con `reminder_minutes` corto (1–2 min).
2. Esperar 1–2 minutos.
3. Verificar notificación en la app.
4. Verificar resultado en “Cron Jobs” (Hostinger).

## Recomendaciones de mejora a futuro
1. **Colas + Worker persistente**
   - Mover el envío a cola para mayor escalabilidad.
   - Configurar un worker persistente (Supervisor/systemd o Horizon si se usa Redis).

2. **Canales de notificación adicionales**
   - Email o WhatsApp con preferencia por usuario.

3. **Reintentos y fallback**
   - Reintentos automáticos si falla el envío.
   - Registro de fallos en tabla o log dedicado.

4. **Optimización de consultas**
   - Índice en `reminder_at` y `reminded_at` para acelerar el comando.

5. **Zona horaria por usuario**
   - Convertir `reminder_at` a la zona horaria del usuario si se requiere multi‑zona.

6. **Panel de control**
   - Vista administrativa para monitorear recordatorios enviados/pendientes.

7. **Seguridad y control**
   - Evitar duplicados usando `reminded_at` y bloqueo adicional si se requiere.
