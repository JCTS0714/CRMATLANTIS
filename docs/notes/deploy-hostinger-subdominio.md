# Deploy en Hostinger (hPanel) a subdominio — CRMATLANTIS (Laravel)

Esta guía documenta el proceso real usado para subir este proyecto Laravel a un **subdominio** en Hostinger usando **Auto Deploy (Git)**, y cómo resolvimos los errores típicos (500 por cachés de bootstrap, APP_KEY, Vite manifest, etc.).

## Objetivo

- Subir el proyecto a un subdominio (ej. `new.grupoatlantiscrm.eu`) **sin interferir** con el dominio principal.
- Mantener la estructura correcta de Laravel:
  - Código completo en una carpeta privada del sitio (ej. `.../public_html/new`)
  - Servir públicamente solo `public/` (ej. `.../public_html/new/public`)

## 1) Rutas correctas (lo más importante)

### Carpeta donde se despliega el repo (Auto Deploy)

- **Repo →** `/home/u652153415/domains/grupoatlantiscrm.eu/public_html/new`

Ahí deben existir `artisan`, `composer.json`, `app/`, `routes/`, `public/`, etc.

### Carpeta raíz del subdominio ("Document Root" en concepto)

En Hostinger no suele llamarse “DocumentRoot”; es el campo de **Carpeta/Directorio/Ruta** del subdominio.

- **Subdominio →** `/home/u652153415/domains/grupoatlantiscrm.eu/public_html/new/public`

Esto evita exponer `.env`, `storage/`, etc.

## 2) Preparación de `.env` (servidor)

En el servidor, dentro de la carpeta del proyecto:

```bash
cd /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new
```

Crear `.env` desde el ejemplo:

```bash
cp .env.example .env
```

Editar `.env` (ej. con `nano`):

```bash
nano .env
```

Salir guardando en nano:
- `Ctrl+O` → Enter → `Ctrl+X`

Valores mínimos:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://new.grupoatlantiscrm.eu`
- `DB_CONNECTION=mysql`
- `DB_HOST=localhost` (en Hostinger suele funcionar si la BD es local)
- `DB_PORT=3306`
- `DB_DATABASE=...`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`

Notas:
- Si `DB_CONNECTION=sqlite`, Laravel ignorará `DB_HOST/DB_USERNAME/...`.

## 3) Composer (servidor)

### Instalación limpia (recomendado si hubo errores previos)

Si `composer install --no-dev` falla con mensajes tipo “Could not scan for classes…” es señal de que `vendor/` quedó inconsistente.

Solución:

```bash
cd /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new
rm -rf vendor
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
```

Si por scripts fallara (plan B):

```bash
rm -rf vendor
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts
php artisan package:discover --ansi
```

## 4) APP_KEY (obligatorio)

Si aparece en logs:
> `No application encryption key has been specified.`

Ejecutar:

```bash
cd /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new
php artisan key:generate
php artisan optimize:clear
php artisan config:cache
```

Verificar que existe:

```bash
grep -n "APP_KEY" .env
```

## 5) Cachés de bootstrap (PailServiceProvider / providers cache)

Síntoma:
> `Class "Laravel\Pail\PailServiceProvider" not found`

Causa:
- Se desplegaron archivos generados de `bootstrap/cache/*.php` que referencian paquetes dev.

Solución inmediata en servidor:

```bash
cd /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new
rm -f bootstrap/cache/services.php bootstrap/cache/packages.php bootstrap/cache/config.php bootstrap/cache/events.php bootstrap/cache/routes.php bootstrap/cache/routes-v7.php
php artisan optimize:clear
```

Solución permanente (repo):
- Ignorar `bootstrap/cache/*.php` en git (no se deben versionar).

## 6) Permisos de escritura (storage/cache)

```bash
cd /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new
chmod -R 775 storage bootstrap/cache
```

## 7) BD y migraciones

Si al limpiar cachés aparece:
> `SQLSTATE[HY000] [1045] Access denied ...`

Causa:
- Credenciales incorrectas o usuario sin permisos en la BD.

Luego de corregir `.env` en Hostinger (y asignar permisos al usuario en la base), correr:

```bash
cd /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new
php artisan migrate --force
```

## 8) Vite / assets (Hostinger sin node/npm)

### Problema
En Hostinger (según este caso) **no hay** `node`/`npm`:

- `node -v` → `command not found`
- `npm -v` → `command not found`

Entonces no se puede hacer `npm run build` en el servidor.

El error típico en Laravel:
- `Vite manifest not found at: public/build/manifest.json`

### Solución usada
- Compilar assets **en local** y subir `public/build` al repo para que el autodeploy lo despliegue.

En local:

```bash
cd C:\xampp\htdocs\CRMATLANTIS
npm install
npm run build
```

Luego:
- Asegurar que `public/build` NO esté ignorado por git.
- Commit/push incluyendo `public/build`.

## 9) Imagen de fondo del login y manifest

Problema:
- `Unable to locate file in Vite manifest: resources/images/fondo_login.jpg`

Solución usada:
- Servir esa imagen desde `public/images` y usar `asset('images/...')` en vez de `Vite::asset(...)`.

## 10) Admin sin restricciones (backend + menú del SPA)

Problema observado:
- El rol `admin` existía, pero la UI mostraba solo Dashboard.

Causa:
- El menú del SPA se decide por `permissions` enviadas en `window.__AUTH_USER__`.

Solución usada:
- Backend: `Gate::before(...)` para que `admin` pase cualquier check.
- Frontend payload: si el usuario es `admin`, enviarle todas las permissions para que el sidebar muestre todo.

## 11) Seeder de admin

Existe un seeder llamado `InternalUserSeeder` que crea/asegura el usuario admin y le asigna rol `admin`.

Fue mejorado para:
- No hardcodear credenciales.
- Solo cambiar password si `SEED_ADMIN_PASSWORD` está definido.

Ejemplo de variables en `.env` (servidor):

- `SEED_ADMIN_NAME=Administrador`
- `SEED_ADMIN_EMAIL=admin@tudominio.com`
- `SEED_ADMIN_PASSWORD=TuPasswordSegura123!`

Ejecutar:

```bash
cd /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new
php artisan db:seed --class='Database\\Seeders\\InternalUserSeeder' --force
```

## 12) Checklist final (producción)

En servidor:

```bash
cd /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link
```

Verificar:
- `public/build/manifest.json` existe
- Login carga y el admin ve el menú completo

## 13) Diagnóstico rápido de errores

Ver los últimos errores de Laravel:

```bash
cd /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new
grep -n "production.ERROR" storage/logs/laravel.log | tail -n 10
```

Ver el final del log:

```bash
tail -n 200 storage/logs/laravel.log
```
