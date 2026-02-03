# Proceso de despliegue (CRM ATLANTIS)

Este documento captura el flujo de despliegue recomendado y los comandos exactos para no olvidar pasos críticos relacionados con permisos, migraciones y caches.

Resumen rápido
- El pipeline/CI ejecuta verificaciones, migraciones de prueba y sincroniza permisos (`permissions:sync`).
- En el servidor de producción seguir el orden: actualizar código → migrar → sincronizar permisos → (opcional) ejecutar seeders → limpiar caches → reiniciar workers.

1) Preparación local / PR
- Commit y push del branch:

```bash
git add .
git commit -m "feat: ..."
git push origin feature/x
```

- Abrir PR y revisar cambios de rutas/permisos.

2) Qué hace CI (recomendado)
- Checkout, instalar dependencias.
- Ejecuta migraciones sobre DB de prueba.
- Ejecuta `php artisan permissions:sync` (detecta y crea permisos desde middleware `permission:`).
- Ejecuta opcionalmente `php artisan db:seed --class=RolesAndPermissionsSeeder` (en CI es seguro; en prod evaluar antes).
- Limpia caches.

3) Deploy en servidor (pasos detallados)

- (Opcional) Poner la app en mantenimiento:

```bash
php artisan down
```

- Actualizar código y dependencias:

```bash
git pull origin main
composer install --no-interaction --no-dev --prefer-dist
npm ci && npm run build  # si se modificó frontend
```

- Ejecutar migraciones y sincronizar permisos (orden recomendado):

Si tus seeders dependen de permisos existentes:
```bash
php artisan migrate --force
php artisan permissions:sync
php artisan db:seed --class=RolesAndPermissionsSeeder   # opcional, revisar efecto
```

Si no ejecutas seeders globales en prod y prefieres crear permisos antes:
```bash
php artisan permissions:sync
php artisan migrate --force
```

- Limpiar caches y reiniciar workers:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
php artisan queue:restart
```

- Sacar la app de mantenimiento:

```bash
php artisan up
```

4) Consideraciones y avisos
- `php artisan permissions:sync` usa `updateOrCreate` — es idempotente.
- `RolesAndPermissionsSeeder` reasigna permisos a `admin` y `dev`; no ejecutarlo en producción sin querer esa reasignación. Para agregar sólo permisos de un módulo, ejecutar el seeder del módulo generado (ej. `PermissionModuleXSeeder`).
- Hacer backup de la BD antes de migraciones en producción.
- En despliegues con múltiples instancias, ejecutar los comandos de limpieza de cache y `queue:restart` en cada instancia o coordinar mediante orquestador.
- Si hay cambios en rutas o permisos, ejecutar `php artisan permissions:sync` en CI y en producción para mantener consistencia.

5) Checklist rápido (copiar/pegar durante deploy)
- [ ] Hacer backup DB
- [ ] git pull origin main
- [ ] composer install
- [ ] npm build (si aplica)
- [ ] php artisan migrate --force
- [ ] php artisan permissions:sync
- [ ] (opcional) php artisan db:seed --class=RolesAndPermissionsSeeder
- [ ] php artisan cache:clear && config:clear && route:clear && view:clear
- [ ] php artisan queue:restart
- [ ] php artisan up

6) Ubicación de referencia
- Archivo CI ejemplo: `.github/workflows/permissions-sync.yml`
- Comando para generar módulo: `php artisan make:module {name} [--prefix]`
- Documentación de permisos: `docs/permissions-workflow.md`

Fin.
