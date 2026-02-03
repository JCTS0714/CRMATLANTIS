# Permisos y flujo de creación de módulos (CRM ATLANTIS)

Este documento resume la solución implementada para gestionar permisos y la creación de módulos de forma segura y reproducible.

Resumen rápido
- `php artisan permissions:sync` — detecta permisos desde las rutas (middleware `permission:`) y crea en la BD los permisos encontrados; además genera permisos estándar `view/create/update/delete` por recurso y `menu.<recurso>` para control del sidebar. Limpia la caché de Spatie al finalizar.
- `php artisan make:module {name} [--prefix=...]` — genera un controlador stub en `app/Http/Controllers`, un archivo de rutas en `routes/modules/{resource}.php` (protegido por permisos) y un seeder en `database/seeders` con permisos básicos del módulo.
- Seeder `RolesAndPermissionsSeeder` — ahora invoca `permissions:sync` y asegura roles base (`admin`, `dev`, `employee`) y asigna permisos existentes a `admin` y `dev`.
- Workflow CI: añadida la acción GitHub `.github/workflows/permissions-sync.yml` que ejecuta migraciones y `permissions:sync` en PRs/push a `main`.

Por qué este enfoque
- Automatiza la creación de permisos para evitar olvidos humanos al añadir módulos.
- Mantiene reproducibilidad: los seeders y migrations permiten versionar cambios.
- Añade un punto de control (revisión en PR / UI) antes de exponer menús o asignar permisos en producción.

Uso del comando `permissions:sync`
- Ejecutar localmente después de añadir nuevas rutas o cambios de middleware:
```bash
php artisan permissions:sync
```
- Qué hace: escanea todas las rutas registradas, extrae las cadenas del middleware `permission:...`, crea esos permisos si faltan y añade permisos estándar y `menu.*` por recurso.

Uso del generador `make:module`
- Ejemplo:
```bash
php artisan make:module certificado --prefix=postventa/certificados
```
- Resultado:
  - `app/Http/Controllers/CertificadoController.php` (stub)
  - `routes/modules/certificados.php` (rutas protegidas por `certificados.view|create|update|delete`)
  - `database/seeders/PermissionModuleCertificadosSeeder.php` (asegura `certificados.*` y `menu.certificados`)
- Pasos sugeridos tras generar: revisar `routes/modules/{resource}.php` y adaptar endpoints; ejecutar el seeder del módulo si quieres crear los permisos inmediatamente:
```bash
php artisan db:seed --class=PermissionModuleCertificadosSeeder
```

Integración en CI / deploy
- Recomendación mínima: ejecutar `php artisan permissions:sync` antes de `php artisan migrate` durante el deploy.
- Ejemplo: se añadió `.github/workflows/permissions-sync.yml` que prepara DB SQLite, ejecuta `php artisan migrate` y `php artisan permissions:sync` en PRs/push a `main`.

Buenas prácticas operacionales
- Flujo de feature/PR: el desarrollador crea el módulo (o llama al generator), ejecuta `permissions:sync` local, añade el seeder (si aplica) y abre PR. Revisar permisos nuevos en la PR. CI vuelve a ejecutar `permissions:sync` y valida.
- Versiona cambios sensibles en migrations/seeders con mensajes claros para mantener histórico.
- Evita ejecutar el seeder global en producción sin revisión, porque reasigna permisos a roles (`admin`/`dev`) según la política del seeder.

Consideraciones y alternativas
- Actualmente el `permissions:sync` crea permisos estándar para cualquier recurso detectado. Si prefieres más control, se puede cambiar para crear sólo permisos explícitos (los que aparezcan en middleware o en stubs del generador), dejando al seeder del módulo la responsabilidad de crear los permisos adicionales.
- Añadir UI de revisión: una mejora práctica es exponer en un panel admin los permisos detectados pero no asignados, permitiendo aprobar antes de mostrar el menú en producción.

Archivos clave
- `app/Console/Commands/SyncPermissions.php` — comando de sincronización automático.
- `app/Console/Commands/MakeModule.php` — generador `make:module`.
- `database/seeders/RolesAndPermissionsSeeder.php` — seeder principal ahora usa `permissions:sync`.
- `routes/modules/*` — rutas generadas para módulos.
- `.github/workflows/permissions-sync.yml` — ejemplo de integración CI.

Problemas comunes y soluciones
- "No veo el menú después de dar permisos": asegúrate de que el permiso `menu.<resource>` está asignado al rol y que el frontend carga permisos actualizados (refrescar sesión o token). Ejecuta `php artisan permissions:sync` y limpias caché con `php artisan cache:clear`.
- "Se crean permisos que no quiero": ejecuta `permissions:sync` en modo controlado y considera eliminar los permisos no deseados manualmente o ajustar el generador para no crear permisos implícitos.

¿Quieres que documente también la UI de revisión o genere una PR con estos cambios listos para merge? Responde con preferencia y lo hago.

*** Fin de documento ***
