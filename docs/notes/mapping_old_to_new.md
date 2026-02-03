# Mapeo de campos — Sistema antiguo -> Nuevo sistema

Este documento contiene el mapeo inicial de las tablas y atributos del sistema antiguo (ver `docs/tablas_y_atributos.txt`) hacia las tablas/modelos del nuevo proyecto Laravel.

## Notas generales
- Campos no existentes en el modelo nuevo se marcan como `--` y requieren decisión: crear columna, guardar en `observacion`/`notes`, o descartar.
- Relacionar `cliente_id` normalmente a `customer_id` en `Customer` model.
- Para imports: exportar CSV con los encabezados indicados en la columna `old_field`.

---

### clientes -> `Customer` (app/Models/Customer.php)

old_field -> new_field (nota)
- id -> id
- nombre -> name
- empresa -> company_name
- tipo -> document_type? (revisar significado)
- documento -> document_number
- telefono -> contact_phone
- correo -> contact_email
- motivo -> -- (mover a `observacion`/notes en Lead/Customer)
- ciudad -> company_address? (o `observacion`)
- migracion -> migracion (Lead model tiene migracion; Customer no) -> guardar en `observacion` o crear columna
- referencia -> -- (observacion)
- fecha_contacto -> -- (no hay campo directo) -> guardar en `observacion` o crear campo
- fecha_creacion -> created_at
- estado -> -- (mapear a estado/active si aplica)
- post_precio -> -- (no equivalente)
- post_rubro -> --
- post_ano -> --
- post_mes -> --
- post_link -> --
- post_usuario -> -- (sensible)
- post_contrasena -> -- (NO subir credenciales; descartar)

Recomendación: exportar estos campos y decidir por cada uno si crear columna en `customers` o guardarlos en `observacion`/metadatos.

---

### actividades -> `CalendarEvent` (app/Models/CalendarEvent.php)

old_field -> new_field (nota)
- id -> id
- cliente_id -> related_id (set `related_type` = `App\\Models\\Customer`)
- usuario_id -> assigned_to (o created_by según origen)
- tipo -> -- (guardar en `description` o crear `type`)
- asunto -> title
- fecha_hora -> start_at (usar `start_at`; `end_at` puede quedar null)
- resultado -> description (concatenar al final)

Recomendación: durante import set `related_type` y `related_id` para vincular al cliente.

---

### clientes_backup
Semejante a `clientes`. Tratar como respaldo; importar solo si es necesario.

---

### contador -> `Contador` (app/Models/Contador.php -> tabla `contadores`)

old_field -> new_field
- id -> id
- nro -> nro
- comercio -> comercio
- nom_contador -> nom_contador
- titular_tlf -> titular_tlf
- telefono -> telefono
- telefono_actu -> telefono_actu
- link -> link
- usuario -> usuario
- contrasena -> contrasena (sensible)
- created_at -> created_at

Nota: tabla nueva se llama `contadores`. Pivot `contadores_clientes` -> `contador_customer`.

---

### contadores_clientes -> pivot `contador_customer`

- id -> -- (pivot)
- contador_id -> contador_id
- cliente_id -> customer_id
- fecha_asignacion -> pivot `fecha_asignacion`

---

### evento -> (posible catálogo) -> `CalendarEvent` o tabla de tipos

- id -> id
- titulo -> title
- color -> -- (si el calendario usa colores, mapear)

---

### incidencias -> `Incidence` (app/Models/Incidence.php)

old_field -> new_field
- id -> id
- correlativo -> correlative
- nombre_incidencia -> title
- cliente_id -> customer_id
- usuario_id -> created_by
- fecha -> date
- prioridad -> priority
- observaciones -> notes
- fecha_creacion -> created_at
- columna_backlog -> stage_id? (mapear al `IncidenceStage` según valor)

Nota: convertir `columna_backlog` a `stage_id` haciendo una tabla de traducción entre nombres/posiciones.

---

### oportunidades -> `Lead` (app/Models/Lead.php)

old_field -> new_field
- id -> id
- cliente_id -> customer_id
- usuario_id -> created_by
- titulo -> name
- descripcion -> observacion
- valor_estimado -> amount
- probabilidad -> -- (mapear a stage/probabilidad en LeadStage)
- estado -> archived_at / stage? (revisar workflow)
- fecha_apertura -> created_at or migracion? (revisar)
- fecha_cierre_estimada -> won_at? (revisar meaning)
- fecha_modificacion -> updated_at
- alta_prioridad -> -- (guardar en observacion)
- actividad -> -- (link to activity/CalendarEvent)
- fecha_actividad -> --

Recomendación: mapear `probabilidad` y `estado` a `stage_id` en `lead_stages`.

---

### reuniones -> `CalendarEvent`

old_field -> new_field
- id -> id
- cliente_id -> related_id (set `related_type`)
- usuario_id -> created_by/assigned_to
- evento_id -> related_type or event type
- ultima_notificacion -> reminder_at / reminded_at
- titulo -> title
- descripcion -> description
- fecha -> start_at
- hora_inicio -> start_at (combine fecha+hora_inicio)
- hora_fin -> end_at
- ubicacion -> location
- estado -> --
- archivado, archivado_por, archivado_en -> archived flags (map to `archived_at` and pivot fields)
- recordatorio -> reminder_minutes
- observaciones -> description/notes

---

### usuarios -> `User` (app/Models/User.php)

old_field -> new_field
- id -> id
- nombre -> name
- usuario -> -- (username not present; can be stored in `email` if fits, or create `username` column)
- password -> password (hashing required)
- perfil -> -- (roles via spatie permissions)
- foto -> -- (store path in user profile)
- estado -> -- (activate/deactivate via `is_active` custom column)
- ultimo_login -> last_login_at (if exists)
- fecha -> created_at
- sesion_token, sesion_expira, remember_token, remember_expires -> remember_token / personal tokens

Nota: passwords deben importarse solo si están ya hasheados; de lo contrario exigir reset.

---

### tenants, tenant_db, admin_users, audit_log
- Revisar si la app nueva usa multi-tenant. Si no, crear scripts personalizados para migrar tenants y conexiones.
- `admin_users` -> si hay `users` con roles admin, mapear.
- `audit_log` -> si existe tabla `audit_logs` en nueva app, mapear campos; de lo contrario, exportar como histórico.

---

### notificaciones_certificados -> `certificados` / `Certificado` (app/Models/Certificado.php)

- id -> id
- certificado_id -> id (relacion)
- tipo -> tipo
- fecha_envio -> ultima_notificacion (o tabla de logs)
- usuario_id -> updated_by / created_by

---

### whatsapp_plantillas, programados_whatsapp, programados_whatsapp_logs
- No hay modelos directos; crear tablas nuevas o guardar plantillas en `settings`/`templates`.

---

## Próximos pasos sugeridos
1. Revisar y validar este mapeo con el equipo para campos marcados `--`.
2. Crear CSV templates para `clientes`, `actividades`, `incidencias`, `oportunidades`, `reuniones`, `usuarios`, `contador`, `contadores_clientes`.
3. Implementar un `php artisan` command por tabla que lea CSV y use los modelos para insertar con validación y logging.

---

## Avance 2026-01-05 — Import CSV Contadores (end-to-end)

Se dejó implementado el flujo completo para importar contadores desde un CSV legado (tipo “Post Venta - Contadores”).

### Backend
- Ruta HTTP (protegida por permisos):
	- `POST /postventa/contadores/import` (middleware `permission:contadores.create`)
	- Implementación: `ContadorController::importCsv()`
	- Espera multipart con el archivo en el campo `csv`.
	- Soporta `dry` (boolean) para ejecutar como dry-run (`--dry-run`).
- Controller:
	- Guarda el archivo en `storage/app/imports/` y llama al comando Artisan `import:contadores`.
	- Si el comando falla “duro” (archivo no existe/no se puede leer), responde 422 con el output.

### Comando Artisan
- Comando: `php artisan import:contadores {file} {--dry-run}`
- Ubicación: `routes/console.php`
- Características:
	- Lectura CSV con `fgetcsv`.
	- Normaliza encabezados (minúsculas, sin acentos, soporta BOM UTF-8) para tolerar variaciones:
		- `N°/Nº/No/Nro/#` -> `nro`
		- `Comercio(s)` -> `comercio`
		- `Nombre Contador` -> `nom_contador`
		- `Nombre en Celular` -> `titular_tlf`
		- `Teléfono` -> `telefono`
		- `Link` -> `link`
		- `Usuario` -> `usuario`
		- `Contraseña` -> `contrasena`
		- `Servidor` -> `servidor`
	- Limpieza básica:
		- Convierte valores tipo `NO TIENE`, `N/A` a `null`.
		- Si `usuario` parece email, se guarda en minúsculas.
		- Filas “vacías” (sin campos relevantes) se saltan.
	- Errores por fila:
		- Se loguean en `storage/logs/import_contadores.log`.
		- No rompen el proceso ni devuelven exit-code distinto de 0.

### Frontend (Vue)
- Componente: `resources/js/components/ContadoresTable.vue`
- Se agregó un botón/selector “Importar CSV”:
	- Sube el archivo a `/postventa/contadores/import`.
	- Muestra estado simple (importando / output) y dispara refresh de la tabla al finalizar.

### Cómo usar
- Desde UI: Postventa → Contadores → “Importar CSV”.
- Desde consola (opcional):
	- Dry-run: `php artisan import:contadores C:\\ruta\\archivo.csv --dry-run`
	- Real: `php artisan import:contadores C:\\ruta\\archivo.csv`

### Pendiente / notas
- Definir si se debe evitar duplicados (por `nro` + `usuario` + `servidor`, etc.) o si se importará “tal cual”.
- (Opcional) Agregar toggle de `dry-run` en UI, si se necesita.

---

Archivo generado automáticamente por el proceso de mapeo inicial.
