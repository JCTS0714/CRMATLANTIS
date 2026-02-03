# 2026-01-24 — Corrección: vendor, storage symlink y redirecciones Cloudflare

Resumen breve
- Reproduje y resolví dos problemas principales mientras se intentaba levantar y usar el proyecto:
  1. Error al ejecutar `php artisan serve` por dependencias faltantes (PHPUnit / sebastian/version).
  2. En producción, las imágenes subidas (ícono/logo) no se mostraban en la web: faltaba exponer `storage/app/public` mediante `public/storage`, y además Cloudflare tenía reglas que redirigían el dominio a `vsbet.help` / `vsbet146.com`.

Acciones realizadas (comandos y resultados)

1) Restaurar dependencias en local

  - `composer install --no-interaction --prefer-dist`
  - Resultado: se instaló `sebastian/version` y otras dependencias; autoload regenerado. Con esto `php artisan serve` dejó de fallar por la clase faltante.

2) Revisar flujo de subida de iconos (código)

  - Archivo inspeccionado: `app/Http/Controllers/SettingsController.php`.
  - Comportamiento: guarda en `storage/app/public/settings` y devuelve `asset('storage/settings/...')`.

3) Diagnóstico en producción

  - Verifiqué que el archivo existe en el origen:
    - `ls -la storage/app/public/settings` → `logo_mark.png` presente.
  - Verifiqué la presencia del enlace público:
    - `ls -la public` inicialmente no mostraba `storage`.
    - `readlink -f public/storage` devolvía una ruta, pero `stat public/storage` indicaba "No such file or directory" → symlink ausente/roto.

4) Crear/rehacer el symlink público

  - Comando recomendado (desde la raíz del proyecto):
    - `php artisan storage:link`
  - Alternativa manual:
    - `rm -rf public/storage`
    - `ln -s "$(pwd)/storage/app/public" public/storage`
  - Verificación:
    - `ls -la public/storage/settings` mostró `logo_mark.png`.

5) Detectar redirección 301 (Cloudflare)

  - `curl -I -L -v https://tu-dominio.com/storage/settings/logo_mark.png` devolvió un `301` que redirigía a `https://vsbet.help/...` y luego a `https://vsbet146.com/`.
  - Cabeceras mostraron `server: cloudflare` — la redirección venía del CDN/Cloudflare y no del origen.

6) Solución de redirección

  - Revisar y corregir en Cloudflare:
    - Cloudflare → DNS: poner el registro A en `DNS only` (gris) para probar.
    - Cloudflare → Page Rules: eliminar cualquier Forwarding URL / Redirect hacia `vsbet.*`.
  - Alternativa de diagnóstico directo al origen (sin Cloudflare):
    - `curl -I --resolve tu-dominio.com:443:YOUR_SERVER_IP https://tu-dominio.com/storage/settings/logo_mark.png`
    - Si este comando devuelve `200`, el origen está bien y la causa era Cloudflare.

7) Permisos (si devuelve 403)

  - Cambiar propietario/permiso (ejemplo):
    - `sudo chown -R www-data:www-data storage public/storage`
    - `sudo chmod -R 775 storage public/storage`
  - En hosting compartido sin `sudo`, probar `chmod -R 775 storage public/storage`.

8) Parche temporal para cache del navegador (opcional)

  - Para forzar que el cliente recargue tras subir una nueva imagen, se puede añadir cache-busting en la respuesta JSON:
    ```php
    'path' => asset('storage/settings/logo_mark.png') . '?v=' . time(),
    ```
  - Archivo a modificar (opcional): `app/Http/Controllers/SettingsController.php` en `uploadLogoMark` y `uploadLogoFull`.

Notas finales
- Resultado final: se restauraron dependencias en local; en producción se creó/rehízo el `public/storage` y se eliminó la causa de redirección en Cloudflare, tras lo cual la URL pública devolvió `200 OK` y la imagen fue visible.
- Si vuelve a aparecer comportamiento extraño, revisar (1) Cloudflare Page Rules y modo proxy del registro DNS, (2) `.htaccess` / vhost, y (3) logs de Laravel en `storage/logs/laravel.log`.

Si quieres, aplico el parche de cache-busting en el repo ahora o genero un pequeño script de verificación que puedas ejecutar en producción.
