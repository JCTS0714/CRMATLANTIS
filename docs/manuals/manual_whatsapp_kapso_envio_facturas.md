# Manual operativo: envio de facturas por WhatsApp (Kapso)

Fecha: 2026-05-02

## 1) Objetivo
Documentar el proceso aplicado para habilitar y validar el envio exitoso de mensajes de WhatsApp con archivo adjunto desde el modulo de bandeja de facturas.

## 2) Cambios funcionales aplicados
Se implemento y desplego lo siguiente:

- Gestion de plantillas de mensaje (crear, editar, eliminar, seleccionar predeterminada).
- Seleccion multiple de clientes para aplicar plantilla y envio masivo.
- Acciones masivas en envios: WhatsApp, Email y marcado manual.
- Flujo de preparacion de archivo publico + diagnosticos por cliente.
- Rutas API para CRUD de plantillas.

## 3) Flujo de despliegue aplicado
### 3.1 Build local antes de subir
Se ejecuto build de frontend para generar assets nuevos en public/build:

```bash
npm run build
```

### 3.2 Versionado y publicacion en GitHub
Se realizo commit y push en rama main.

### 3.3 Comandos principales de servidor (SSH)
Secuencia recomendada aplicada para desplegar:

```bash
cd /ruta/de/tu/proyecto
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan queue:restart
php artisan up
```

## 4) Incidencia 1 resuelta: Error 419 PAGE EXPIRED
### 4.1 Causa
Token CSRF/sesion invalida por cambios de entorno y/o cache de configuracion.

### 4.2 Correccion aplicada
1. Limpiar y recachear configuracion.
2. Recargar servicios de PHP/Nginx.
3. Reintentar login en sesion limpia del navegador.

Comandos:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan config:cache
# Luego recargar servicios web segun el servidor
```

### 4.3 Variables criticas a revisar
- APP_URL (HTTPS real)
- SESSION_DRIVER
- SESSION_DOMAIN
- SESSION_SECURE_COOKIE
- APP_KEY (no debe cambiar entre despliegues)

## 5) Incidencia 2 resuelta: "Kapso no esta configurado en variables de entorno"
### 5.1 Causa
El runtime de Laravel no estaba leyendo/actualizando correctamente configuracion de Kapso en servidor (cache/env).

### 5.2 Variables exactas requeridas por el codigo
En services.kapso se leen estas keys:

- KAPSO_ENABLED=true
- KAPSO_API_KEY=...
- KAPSO_PHONE_NUMBER_ID=...
- KAPSO_BASE_URL=https://api.kapso.ai/meta/whatsapp/v24.0 (opcional, con default)

### 5.3 Verificacion aplicada
Comprobar .env y lo que Laravel tiene en runtime:

```bash
grep -E "^(KAPSO_ENABLED|KAPSO_API_KEY|KAPSO_PHONE_NUMBER_ID|KAPSO_BASE_URL)=" .env
php artisan tinker --execute="dump(config('services.kapso'));"
```

Luego refrescar cache:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan tinker --execute="dump(config('services.kapso'));"
```

## 6) Validaciones operativas del envio
Para que el envio de archivo por WhatsApp funcione correctamente:

1. El cliente debe tener celular valido en formato normalizable.
2. La URL del archivo debe ser publica y alcanzable desde internet.
3. Kapso debe estar configurado (enabled + api key + phone number id).
4. Se recomienda validar estado de Kapso desde la UI con "Probar Kapso" antes de enviar en lote.

Diagnosticos que puede mostrar el sistema:

- celular_invalido
- kapso_no_configurado
- public_base_url_no_publica

## 7) Proceso operativo final (paso a paso)
1. Sincronizar pendientes del mes actual.
2. Preparar archivo por cliente (o lote segun operativa).
3. Seleccionar plantilla activa.
4. Aplicar plantilla a filtrados o seleccionados.
5. Activar seleccion y marcar destinatarios.
6. En pestaña de envios:
- Enviar WA a seleccionados, o
- Enviar Email a seleccionados, o
- Marcar enviados manualmente.

## 8) Checklist rapido para futuras salidas a produccion
- [ ] npm run build ejecutado antes de publicar.
- [ ] Commit/push en rama correcta.
- [ ] git pull en servidor.
- [ ] php artisan migrate --force.
- [ ] php artisan optimize:clear y php artisan optimize.
- [ ] Configuracion Kapso validada via tinker.
- [ ] Prueba real con 1 cliente antes de envio masivo.

## 9) Resultado
Se logro el primer envio exitoso de mensaje WhatsApp con archivo adjunto desde el modulo de bandeja de facturas.
