# 2025-12-28 — CRM Atlantis (Parte 2: Postventa + Dashboard)

Fecha: 2025-12-28

## Objetivo general
- Migrar/replicar el módulo **Postventa** del sistema anterior dentro del CRM (Laravel + Vue), reutilizando el patrón de Leads (pipeline + lista) para Incidencias/Backlog.
- Terminar los módulos “tablas simples” (Contadores y Certificados) e integrarlos a navegación/permisos.
- Dejar un **Dashboard real** con métricas relevantes y listas accionables.

---

## 1) Postventa — Incidencias/Backlog (pipeline + lista)
**Decisión de diseño**
- Incidencias y Backlog se implementaron como una sola entidad (`incidences`) con etapas (`incidence_stages`), igual al pipeline de Leads.
- La UX queda como Leads: **2 vistas** (Backlog/Pipeline y Lista), alternando por URL.

**Base de datos**
- Migraciones:
  - `database/migrations/*create_incidence_stages_table.php`
  - `database/migrations/*create_incidences_table.php`
- Campos relevantes:
  - Correlativo (`INC-000001`), `stage_id`, `customer_id`, `title`, `date`, `priority`, `notes`, `archived_at`.

**Backend**
- `app/Models/Incidence.php`, `app/Models/IncidenceStage.php`
- `app/Http/Controllers/IncidenceController.php`
  - `tableData()` lista
  - `boardData()` backlog/pipeline
  - `store()` crea incidencia y genera correlativo
  - `moveStage()` mueve de etapa
  - `archive()` archiva (usa `archived_at`)

**Frontend (Vue)**
- `resources/js/components/BacklogBoard.vue` (Kanban/pipeline)
- `resources/js/components/IncidenciasTable.vue` (Tabla/lista)
- `resources/js/components/IncidenceQuickModal.vue`
  - Modal global para “Nueva incidencia” usando `CustomEvent`.

**Rutas**
- `routes/web.php`
  - `GET /backlog` (vista backlog/pipeline)
  - `GET /incidencias` (vista lista)
  - `GET /backlog/board-data`
  - `GET /incidencias/data`
  - `POST /incidencias`
  - `PATCH /incidencias/{incidence}/move-stage`
  - `PATCH /incidencias/{incidence}/archive`

---

## 2) Postventa — Clientes (tabla + acción de relación con incidencia)
**Qué se hizo**
- Se agregó un listado “Clientes (postventa)” que permite enlazar rápidamente un cliente a una incidencia (flujo de trabajo).

**Frontend**
- `resources/js/components/PostventaCustomersTable.vue`

**Rutas**
- `GET /postventa/clientes` (renderiza la SPA)

---

## 3) Postventa — Contadores (tabla simple + relación con cliente)
**Decisión de datos**
- Se modeló `contadores` como tabla principal.
- Se agregó una tabla intermedia `contador_customer` para asignación a cliente.
- Se mantiene restricción práctica: **un contador solo puede estar asignado a un cliente** (`unique(contador_id)` en pivot).

**Migraciones**
- `database/migrations/2025_12_28_000300_create_contadores_table.php`
- `database/migrations/2025_12_28_000301_create_contador_customer_table.php`

**Backend**
- `app/Models/Contador.php` + relación a customers.
- `app/Models/Customer.php` se amplió con `contadores()`.
- `app/Http/Controllers/ContadorController.php`
  - `data()` (incluye cliente asignado si existe)
  - `store()`, `update()`, `destroy()`

**Frontend**
- `resources/js/components/ContadoresTable.vue`
- SweetAlert prompts:
  - `resources/js/ui/alerts.js` (`promptContadorCreate`, `promptContadorEdit`)

**Rutas**
- `GET /postventa/contadores`
- `GET /postventa/contadores/data`
- `POST /postventa/contadores`
- `PUT /postventa/contadores/{contador}`
- `DELETE /postventa/contadores/{contador}`

---

## 4) Postventa — Certificados (tabla simple + imagen con upload)
**Aclaración funcional**
- Es válido que Certificados sea una tabla sin relaciones (no todas las tablas deben tener FKs).

**Migración**
- `database/migrations/2025_12_28_000302_create_certificados_table.php`
  - Incluye `imagen` (string path), fechas y `created_by/updated_by`.

**Backend**
- `app/Models/Certificado.php`
- `app/Http/Controllers/CertificadoController.php`
  - CRUD + paginación/búsqueda.
  - **Upload de imagen**:
    - Acepta `imagen` tipo file (`image`, max 4MB).
    - Guarda en `storage/app/public/certificados` y persiste la ruta en `certificados.imagen`.
    - En update, si suben nueva imagen, elimina la anterior del disco.
    - En delete, también elimina la imagen.

**Frontend**
- `resources/js/components/CertificadosTable.vue`
- SweetAlert prompts:
  - `resources/js/ui/alerts.js` (`promptCertificadoCreate`, `promptCertificadoEdit`)
  - Se cambió el campo Imagen a `type="file"`.
  - Al editar: si el usuario no selecciona archivo, no se sobrescribe la imagen actual.
- Envío `multipart/form-data`:
  - Create: usa `FormData` cuando hay archivo.
  - Update: usa `POST + _method=PUT` cuando hay archivo para que PHP reciba `$_FILES`.

**Rutas**
- `GET /postventa/certificados`
- `GET /postventa/certificados/data`
- `POST /postventa/certificados`
- `PUT /postventa/certificados/{certificado}`
- `DELETE /postventa/certificados/{certificado}`

**Nota**
- Si se requiere servir imágenes públicamente: `php artisan storage:link`.

---

## 5) Navegación — Sidebar Postventa + hover en modo colapsado
**Problemas corregidos**
- Dropdown Postventa no desplegaba.
- El hover-flyout en sidebar colapsado se veía “detrás” del contenido.

**Solución**
- Dropdown Postventa corregido.
- Hover-flyout:
  - Se renderiza fuera del sidebar usando `Teleport to="body"`.
  - Se posiciona por `getBoundingClientRect()`.
  - Z-index alto para evitar problemas de stacking context.

**Archivo clave**
- `resources/js/components/Sidebar.vue`

**Menú Postventa**
- Incidencias
- Clientes
- Contadores
- Certificados

---

## 6) Permisos y roles (Spatie)
**Qué se hizo**
- Se agregaron permisos y se validó que “Roles” no acepte permisos inexistentes.
- Se asignaron permisos a roles `admin` y `dev`.

**Seeder**
- `database/seeders/RolesAndPermissionsSeeder.php`
  - `menu.postventa`
  - `incidencias.*`, `contadores.*`, `certificados.*`

**Comandos típicos**
- `php artisan db:seed --class=RolesAndPermissionsSeeder`
- `php artisan optimize:clear`

---

## 7) Dashboard real (métricas + listas)
**Qué se hizo**
- Se implementó un dashboard con datos reales (no placeholders) y secciones enfocadas a operación diaria:
  - KPIs por módulo (según permisos)
  - Certificados por vencer
  - Próximos eventos (asignados al usuario)
  - Últimos leads / últimas incidencias

**Backend**
- `app/Http/Controllers/DashboardController.php`
  - `GET /dashboard/summary`
  - Filtra contenido por permisos (`user->can(...)`).

**Frontend**
- `resources/js/components/DashboardView.vue`
  - Consume `/dashboard/summary`.
  - Tarjetas clicables hacia los módulos.

**Integración en SPA**
- `resources/js/components/App.vue`
  - Renderiza `DashboardView` para `/dashboard`.

---

## 8) Checklist rápido (postventa + dashboard)
1) Ir a `/dashboard` y verificar tarjetas + listas (según permisos).
2) Entrar a Postventa:
   - `/backlog` (pipeline)
   - `/incidencias` (lista)
   - `/postventa/clientes`
   - `/postventa/contadores`
   - `/postventa/certificados`
3) Certificados:
   - Crear/editar y subir imagen.
   - Validar `storage:link` si se necesita acceder por URL pública.
