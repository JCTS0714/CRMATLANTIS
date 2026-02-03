Seguridad — recomendaciones rápidas para CRM ATLANTIS

Resumen
- Escaneo rápido de `app/Http/Controllers` detectó múltiples puntos de subida (`store()`, `storeAs()`) y uso limitado de `DB::raw` sólo para agregados.
- La aplicación ya usa validación de Laravel en la mayoría de los endpoints (reglas `image|mimes|mimetypes|max`, validaciones de email), y `Auth::attempt` para login — buena base.

Recomendaciones prioritarias (alta → baja)

1) Forzar extensión segura y nombres no previsibles
- Evitar usar `storeAs()` con nombres basados en entrada del usuario (ej. `ruc + ext`).
- Preferir `store()` o `store('path', 'public')` y usar `$file->hashName()` o `Str::random()` para el nombre.
- Si necesitas un nombre predecible, sanitizarlo estrictamente y mapear la extensión desde el MIME (no confiar en `getClientOriginalExtension()`).

2) Re-encodear imágenes y normalizar formato
- Re-encodea imágenes subidas con GD o Intervention Image (por ej. guardar PNG/JPEG desde imagen procesada). Esto descarta metadata/exif maliciosa y asegura el contenido real.
- `SettingsController` ya re-encodea los logos — aplicar la misma práctica en cargas críticas (profile photos, importaciones masivas) cuando sea necesario.

3) Validación estricta de archivos
- Mantén reglas como `file|image|mimes:jpg,png,webp|mimetypes:image/jpeg,image/png,image/webp|max:4096`.
- Para ZIP/CSV/otros, valida `mimes:zip,csv,txt` y tamaño.
- Para cargas de múltiples extensiones (pdf, imagen), verifica `getClientMimeType()` y rehacer extensión apropiadamente.

4) Evitar ejecución en directorios de uploads
- Impedir la ejecución de PHP y otros scripts en `storage/app/public` y subcarpetas: añadir `.htaccess` (Apache) o directiva Nginx.
- Ejemplo (Apache) para `storage/app/public`:

```
# Denegar ejecución de PHP
<FilesMatch "\.(php|phtml|php3|php4|phps)$">
  Require all denied
</FilesMatch>

# Opcional: denegar todo el acceso directo si lo deseas
Order deny,allow
Deny from all
</FilesMatch>
```

- Para Nginx, asegurar una regla que no pase peticiones a PHP-FPM desde `/storage/` y sirve solo archivos estáticos.

5) Escaneo antivirus / filtrado de archivos
- Integrar ClamAV (o servicio SaaS) en el pipeline de uploads: subir a un área temporal, ejecutar `clamdscan`/`clamscan`, luego mover a almacenamiento definitivo.
- Rechazar archivos con detecciones y loguear/alertar a admin.

6) Minimizar información en errores y monitorización
- No mostrar detalles de la base de datos o stack trace en respuestas públicas.
- Registrar eventos sospechosos (uploads rechazadas por mime, inputs no válidos, intentos de login fallidos repetidos) y enviar alertas si ocurren patrones.

7) Revisar uso de SQL crudo
- Reemplazar `whereRaw`, `DB::select`, `DB::statement` con bindings o Eloquent/Query Builder con parámetros enlazados.
- En el escaneo rápido se encontraron `DB::raw('count(*) as count')` para agregados (LeadController e IncidenceController). Es razonable; no es vulnerable si no concatena input.

8) Políticas adicionales
- Añadir encabezados de seguridad: `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy`, `Content-Security-Policy` estricta para el panel.
- Limitar tipos y tamaños de uploads según uso real.
- Mantener backups y pruebas en entornos de staging para pruebas de seguridad.

Acciones rápidas que puedo aplicar (elige si quieres):
- Forzar extensión desde MIME y cambiar `storeAs()` a `store()` en puntos concretos (`CertificadoController::importImages`, `ProfileController`, `UserController`).
- Añadir `.htaccess` de ejemplo en `public/storage` o instrucción Nginx.
- Añadir middleware para headers de seguridad (snippet Laravel) y ejemplo de integración con ClamAV.

Notas
- Antes de cambiar nombres o rutas de archivos en producción, ejecutar pruebas y mantener compatibilidad con referencias existentes.
- Para auditorías más profundas, puedo generar un listado detallado (archivo+línea) de todos los `storeAs()` y cargas encontradas.
