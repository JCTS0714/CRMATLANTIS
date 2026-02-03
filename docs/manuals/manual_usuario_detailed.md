# Manual de Usuario - Borrador
Este documento es un borrador generado automáticamente. Revísalo y amplíalo con pasos, pantallazos y ejemplos específicos de tu organización.

## Resumen de módulos

### Api

Descripción:
- Breve descripción de lo que hace el módulo **Api**. (Editar)

Acciones principales:
- `POST` `/api/chatbot/query`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Api

Descripción:
- Endpoints internos que la aplicación expone para funcionalidades integradas (p. ej. el servicio de ayuda/chat, APIs de datos para tablas y widgets). Están protegidos por `auth` y permisos según corresponda.

Acciones principales:
- `POST /api/chatbot/query` — consulta de ayuda basada en documentación.

Cómo usar (usuario/soporte):
- El equipo de producto o soporte puede usar este endpoint solo para integraciones internas; la interfaz de usuario ya lo consume desde el widget de ayuda.

Permisos relacionados:
- Normalmente protegido por `auth`; no hay permiso público por defecto.

### Backlog

Descripción:
- Breve descripción de lo que hace el módulo **Backlog**. (Editar)

Acciones principales:
- `GET` `/backlog` `backlog.index`
- `GET` `/backlog/board-data` `backlog.boardData`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Backlog

Descripción:
- Vista tipo tablero/kanban para gestionar tareas, incidencias y backlog de trabajo. Permite priorizar y mover ítems entre etapas.

Acciones principales:
- `GET /backlog` `backlog.index` — ver tablero.
- `GET /backlog/board-data` `backlog.boardData` — datos para el tablero.

Cómo usar (pasos):
- Abrir **Backlog** para ver tarjetas por etapa.
- Arrastrar una tarjeta a la etapa correcta para actualizar su estado.
- Hacer clic en una tarjeta para ver detalles, agregar comentarios o adjuntos.

Consejos:
- Mantener el backlog revisado semanalmente para priorizar trabajo.

Permisos relacionados:
- `backlog.view`, `backlog.update`, `backlog.create`

### Calendario

Descripción:
- Breve descripción de lo que hace el módulo **Calendario**. (Editar)

Acciones principales:
- `GET` `/calendar` `calendar.index`
- `GET` `/calendar/events` `calendar.events`
- `POST` `/calendar/events` `calendar.events.store`
- `PUT` `/calendar/events/{event}` `calendar.events.update`
- `DELETE` `/calendar/events/{event}` `calendar.events.destroy`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Calendario

Descripción:
- Visualiza y administra eventos (reuniones, recordatorios, tareas asignadas) y permite asociarlos con leads, clientes o incidencias.

Acciones principales:
- `GET /calendar` — ver calendario.
- `GET /calendar/events` `calendar.events` — listar eventos.
- `POST /calendar/events` `calendar.events.store` — crear evento.
- `PUT /calendar/events/{event}` `calendar.events.update` — actualizar evento.
- `DELETE /calendar/events/{event}` `calendar.events.destroy` — eliminar evento.

Cómo usar (pasos):
- Crear evento: botón **Nuevo** en calendario → completar título, fecha/hora, duración, participantes y vincular a un `lead` o `cliente` si aplica.
- Notificaciones: los participantes reciben notificaciones internas (y correo según configuración).

Consejos:
- Asocie eventos a leads/clients para mantener el contexto de la reunión.

Permisos relacionados:
- `calendar.view`, `calendar.create`, `calendar.update`, `calendar.delete`

### Chat

Descripción:
- Breve descripción de lo que hace el módulo **Chat**. (Editar)

Acciones principales:
- `GET` `/chat` `chat`
- `GET` `/chat/widget` `chat.widget`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Chat

Descripción:
- Chat de ayuda integrado que busca en la documentación de usuario y responde con explicaciones y pasos. Está pensado para consultas rápidas y guiar al usuario dentro del producto.

Acciones principales:
- `GET /chat` — interfaz completa de chat.
- `GET /chat/widget` — fragmento embebible usado por el botón flotante.
- `POST /api/chatbot/query` — endpoint que procesa la pregunta y devuelve una respuesta sintetizada y/o snippets.

Cómo usar (usuario):
- Haga clic en el botón flotante **Ayuda** (abajo a la derecha). Escriba su pregunta en lenguaje natural y presione **Enviar**.
- El sistema intentará responder con una sola respuesta clara; si necesita más detalle, puede pedir pasos concretos.

Consejos:
- Formule preguntas concretas: «¿Cómo programo una reunión con un lead?» en lugar de frases muy técnicas.

Permisos relacionados:
- Requiere sesión autenticada (`auth`).

### Configuración

Descripción:
- Breve descripción de lo que hace el módulo **Configuración**. (Editar)

Acciones principales:
- `GET` `/configuracion` `settings.index`
- `POST` `/configuracion/logo` `settings.logo.upload`
- `GET` `/configuracion/logo-path` `settings.logo.path`
- `POST` `/configuracion/logo-mark` `settings.logo.mark.upload`
- `POST` `/configuracion/logo-full` `settings.logo.full.upload`
- `GET` `/configuracion/logo-paths` `settings.logo.paths`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Configuración

Descripción:
- Panel para parámetros globales de la aplicación: logos, ajustes de correo, integraciónes y preferencias del sistema.

Acciones principales:
- `GET /configuracion` — abrir sección de configuración.
- Subir logos: `POST /configuracion/logo`, `POST /configuracion/logo-mark`, `POST /configuracion/logo-full`.
- Consultar rutas de los logos: `GET /configuracion/logo-path` y `GET /configuracion/logo-paths`.

Cómo usar (pasos):
- Cambiar logo: en **Configuración → Apariencia**, suba la imagen y guarde.
- Ajustes de correo y servicios: revise las variables de entorno en el despliegue (host, puerto, credenciales) desde el panel de configuración si existe la UI.

Consejos:
- Utilice imágenes en formatos recomendados y tamaños adecuados para evitar problemas de visualización.

Permisos relacionados:
- `settings.view`, `settings.update`

### Clientes

Descripción:
- Breve descripción de lo que hace el módulo **Clientes**. (Editar)

Acciones principales:
- `GET` `/customers` `customers.index`
- `GET` `/customers/data` `customers.data`
- `POST` `/customers` `customers.store`
- `POST` `/customers/import` `customers.import`
- `PUT` `/customers/{customer}` `customers.update`
- `DELETE` `/customers/{customer}` `customers.destroy`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Clientes

Descripción:
- Gestiona el registro y la información de las empresas o personas con las que trabaja la organización (datos fiscales, contactos, historiales de gestiones, documentos).

Acciones principales:
- `GET` `/customers` `customers.index` — ver listado y buscar clientes.
- `GET` `/customers/data` `customers.data` — datos JSON para tablas/paginación.
- `POST` `/customers` `customers.store` — crear cliente nuevo.
- `POST` `/customers/import` `customers.import` — importar clientes desde CSV.
- `PUT` `/customers/{customer}` `customers.update` — editar cliente.
- `DELETE` `/customers/{customer}` `customers.destroy` — eliminar cliente.

Cómo usar (pasos comunes):
- Crear un cliente: vaya a **Clientes → Nuevo**. Complete nombre, RUC/CUIT, dirección, contacto principal, teléfono y correo. Guarde.
- Buscar clientes: use el buscador en el listado por nombre, RUC o correo. Puede combinar filtros por estado o responsable.
- Importar clientes: prepare un CSV con columnas obligatorias (`name`, `ruc`, `email`) y opcionales. En **Clientes → Importar**, suba el archivo; revise el reporte de errores tras la importación.
- Editar información: abra la ficha del cliente y use el botón **Editar**. Cambios quedan registrados en el historial de la ficha.

Ejemplos de campos relevantes (usuario):
- `Nombre` — nombre de empresa o persona.
- `RUC` — número fiscal.
- `Contacto` — persona responsable dentro del cliente.
- `Teléfono / Email` — medios de contacto.
- `Observaciones` — notas internas visibles para el equipo.

Consejos y buenas prácticas:
- Antes de crear, busque por RUC para evitar duplicados.
- Use la importación para datos masivos y revise filas fallidas.

Permisos relacionados:
- `customers.view`, `customers.create`, `customers.update`, `customers.delete`

### Dashboard

Descripción:
- Breve descripción de lo que hace el módulo **Dashboard**. (Editar)

Acciones principales:
- `GET` `/dashboard` `dashboard`
- `GET` `/dashboard/summary` `dashboard.summary`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Dashboard

Descripción:
- Vista principal con KPIs y accesos rápidos a módulos. Resumen inmediato del estado del negocio (leads, clientes, incidencias, próximos eventos).

Cómo usar (usuario):
- En la página principal verá tarjetas con métricas clave; haga clic en ellas para navegar al módulo correspondiente.

Consejos:
- Personalice widgets si la opción está disponible y use filtros por rango de fechas para análisis.

Permisos relacionados:
- `dashboard.view`

### Desistidos

Descripción:
- Breve descripción de lo que hace el módulo **Desistidos**. (Editar)

Acciones principales:
- `GET` `/desistidos` `desistidos.index`
- `GET` `/desistidos/data` `desistidos.data`
- `GET` `/desistidos/{lostLead}` `desistidos.show`
- `PATCH` `/desistidos/{lostLead}`
- `POST` `/desistidos/import` `desistidos.import`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Desistidos

Descripción:
- Guarda leads que han desistido o se han perdido. Mantiene histórico y permite reactivar o analizar motivos.

Cómo usar (pasos):
- Para mover un lead a desistidos, use la acción correspondiente en la ficha del lead o en el listado.

Permisos relacionados:
- `desistidos.view`, `desistidos.update`, `desistidos.import`

### Email

Descripción:
- Breve descripción de lo que hace el módulo **Email**. (Editar)

Acciones principales:
- `GET` `/email/unsubscribe` `email.unsubscribe`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Email

Descripción:
- Funciones relacionadas con campañas de email y gestión de bajas (unsubscribe).

Acciones principales:
- `GET /email/unsubscribe` — enlace público para darse de baja.

Cómo usar (usuario):
- Los destinatarios pueden usar el enlace de baja incluido en correos para actualizar su preferencia.

Permisos relacionados:
- `email.manage`, `email.send`

### En espera

Descripción:
- Breve descripción de lo que hace el módulo **En espera**. (Editar)

Acciones principales:
- `GET` `/espera` `espera.index`
- `GET` `/espera/data` `espera.data`
- `GET` `/espera/{waitingLead}` `espera.show`
- `PATCH` `/espera/{waitingLead}`
- `POST` `/espera/import` `espera.import`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### En espera

Descripción:
- Lista de leads o tareas en estado de espera (p. ej. pendientes de respuesta o documentación).

Cómo usar (pasos):
- Revise periódicamente para reactivar o escalonar ítems que llevan demasiado tiempo en espera.

Permisos relacionados:
- `espera.view`, `espera.update`, `espera.import`

### Incidencias (Soporte / Backlog)

Descripción:
- Breve descripción de lo que hace el módulo **Incidencias (Soporte / Backlog)**. (Editar)

Acciones principales:
- `GET` `/incidencias` `incidencias.index`
- `GET` `/incidencias/data` `incidencias.data`
- `POST` `/incidencias/import` `incidencias.import`
- `POST` `/incidencias` `incidencias.store`
- `PUT` `/incidencias/{incidence}` `incidencias.update`
- `PATCH` `/incidencias/{incidence}/move-stage` `incidencias.moveStage`
- `PATCH` `/incidencias/{incidence}/archive` `incidencias.archive`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Incidencias (Soporte / Backlog)

Descripción:
- Registrar, priorizar y hacer seguimiento de problemas, solicitudes o tareas de soporte (incidencias). Incluye un flujo tipo backlog con etapas (por ejemplo: Nuevo → Asignado → En progreso → Resuelto → Archivado).

Acciones principales:
- `GET` `/incidencias` `incidencias.index` — ver listado/backlog.
- `GET` `/incidencias/data` `incidencias.data` — datos JSON para tablas/board.
- `POST` `/incidencias/import` `incidencias.import` — importar incidencias.
- `POST` `/incidencias` `incidencias.store` — crear incidencia.
- `PUT` `/incidencias/{incidence}` `incidencias.update` — editar incidencia.
- `PATCH` `/incidencias/{incidence}/move-stage` `incidencias.moveStage` — mover etapa (pipeline).
- `PATCH` `/incidencias/{incidence}/archive` `incidencias.archive` — archivar.

Cómo usar (pasos comunes):
- Crear una incidencia: seleccione **Incidencias → Nueva**. Indique cliente relacionado (si aplica), título, descripción y prioridad. Asigne responsable si sabe quién la resolverá.
- Priorizar y asignar: use el listado o el board para mover la incidencia a la etapa adecuada y asignarla a un miembro del equipo.
- Añadir comentarios/archivos: abra la incidencia y agregue notas, pasos reproducibles, capturas o archivos adjuntos.
- Cerrar/archivar: al resolver, marque la incidencia como resuelta y posteriormente archívela para mantener limpio el backlog.

Consejos y buenas prácticas:
- Use las etiquetas y prioridad para filtrar incidencias importantes.
- Vincule incidencias relevantes con `cliente_id` o números de certificado para tener contexto.
- Para problemas recurrentes, cree plantillas o checklists en la descripción.

Permisos relacionados:
- `incidencias.view`, `incidencias.create`, `incidencias.update`, `incidencias.delete`, `incidencias.moveStage`

### Leads

Descripción:
- Breve descripción de lo que hace el módulo **Leads**. (Editar)

Acciones principales:
- `GET` `/leads` `leads.index`
- `GET` `/leads/list` `leads.list`
- `GET` `/leads/data` `leads.data`
- `GET` `/leads/board-data` `leads.boardData`
- `GET` `/leads/whatsapp` `leads.whatsapp`
- `GET` `/leads/email` `leads.email`
- `GET` `/leads/whatsapp/recipients` `leads.whatsapp.recipients`
- `GET` `/leads/email/recipients` `leads.email.recipients`
- `GET` `/leads/whatsapp-campaigns` `leads.whatsapp.campaigns.index`
- `GET` `/leads/email-campaigns` `leads.email.campaigns.index`
- `GET` `/leads/whatsapp-campaigns/{campaign}` `leads.whatsapp.campaigns.show`
- `GET` `/leads/email-campaigns/{campaign}` `leads.email.campaigns.show`
- `POST` `/leads` `leads.store`
- `PUT` `/leads/{lead}` `leads.update`
- `POST` `/leads/whatsapp-campaigns` `leads.whatsapp.campaigns.store`
- `POST` `/leads/email-campaigns` `leads.email.campaigns.store`
- `POST` `/leads/email-campaigns/{campaign}/send`
- `PATCH` `/leads/whatsapp-campaign-recipients/{recipient}`
- `POST` `/leads/import/prospectos` `leads.import.prospectos`
- `PATCH` `/leads/{lead}/move-stage` `leads.moveStage`
- `PATCH` `/leads/{lead}/archive` `leads.archive`
- `POST` `/leads/{lead}/desist` `leads.desist`
- `POST` `/leads/{lead}/wait` `leads.wait`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Leads

Descripción:
- Gestiona potenciales clientes (prospectos) a lo largo de un pipeline de ventas: captación, seguimiento, campañas y conversión a cliente.

Acciones principales:
- `GET` `/leads` `leads.index` — listado de leads.
- `GET` `/leads/list` `leads.list` — vistas alternativas (lista/board).
- `GET` `/leads/data` `leads.data` — datos para tablas.
- `GET` `/leads/board-data` `leads.boardData` — datos para tablero Kanban.
- `POST` `/leads` `leads.store` — crear lead.
- `PUT` `/leads/{lead}` `leads.update` — editar lead.
- `PATCH` `/leads/{lead}/move-stage` `leads.moveStage` — mover lead entre etapas del pipeline.
- `PATCH` `/leads/{lead}/archive` `leads.archive` — archivar lead.
- `POST` `/leads/import/prospectos` `leads.import.prospectos` — importar prospectos.
- Acciones de campaña: WhatsApp/email para outreach y envíos masivos.

Cómo usar (pasos comunes):
- Crear lead manualmente: en **Leads → Nuevo**, rellene nombre, empresa, contacto, fuente y notas. Asigne un responsable si corresponde.
- Trabajar en el pipeline: use el tablero (board) para arrastrar leads entre etapas según el avance (ej: Nuevo → Contactado → Propuesta → Ganado/Perdido).
- Programar reuniones: desde la ficha del lead cree un evento en el calendario y asócielo al lead.
- Enviar comunicaciones: use las opciones de WhatsApp o Email dentro del lead para contactar sin salir de la plataforma.
- Convertir a cliente: cuando se cierre la venta, use la acción para convertir el lead en cliente (crear ficha de cliente y transferir datos relevantes).

Consejos y buenas prácticas:
- Mantenga notas actualizadas y registros de contacto en la ficha del lead.
- Use filtros por responsable, etapa y fuente para priorizar seguimiento.
- Para importaciones masivas, valide el CSV antes y revise filas fallidas.

Permisos relacionados:
- `leads.view`, `leads.create`, `leads.update`, `leads.delete`, `leads.moveStage`

### Notifications

Descripción:
- Breve descripción de lo que hace el módulo **Notifications**. (Editar)

Acciones principales:
- `GET` `/notifications` `notifications.index`
- `POST` `/notifications/{id}/read` `notifications.read`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Notifications

Descripción:
- Centro de notificaciones internas; lista alertas del sistema y marca como leídas.

Acciones principales:
- `GET /notifications` — ver notificaciones.
- `POST /notifications/{id}/read` — marcar como leída.

Cómo usar (usuario):
- Haga clic en el icono de notificaciones para abrir el panel y revisar avisos.

Consejos:
- Configure las preferencias de notificación en perfil si está disponible.

Permisos relacionados:
- `notifications.view`, `notifications.read`

### Postventa

Descripción:
- Breve descripción de lo que hace el módulo **Postventa**. (Editar)

Acciones principales:
- `GET` `/postventa/clientes` `postventa.customers`
- `GET` `/postventa/contadores` `postventa.contadores`
- `GET` `/postventa/contadores/data` `postventa.contadores.data`
- `POST` `/postventa/contadores` `postventa.contadores.store`
- `POST` `/postventa/contadores/import` `postventa.contadores.import`
- `PUT` `/postventa/contadores/{contador}` `postventa.contadores.update`
- `DELETE` `/postventa/contadores/{contador}` `postventa.contadores.destroy`
- `GET` `/postventa/certificados` `postventa.certificados`
- `GET` `/postventa/certificados/data` `postventa.certificados.data`
- `POST` `/postventa/certificados` `postventa.certificados.store`
- `POST` `/postventa/certificados/import-data` `postventa.certificados.importData`
- `POST` `/postventa/certificados/import-images` `postventa.certificados.importImages`
- `PUT` `/postventa/certificados/{certificado}` `postventa.certificados.update`
- `DELETE` `/postventa/certificados/{certificado}` `postventa.certificados.destroy`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Postventa

Descripción:
- Funcionalidades posteriores a la venta: gestión de contadores, certificados y atención postventa.

Acciones principales:
- Gestión de contadores y certificados (`postventa.*` routes). Importación y carga de imágenes para certificados.

Cómo usar (usuario):
- Use el módulo Postventa para registrar servicios posventa, cargar evidencias y generar acciones correctivas.

Permisos relacionados:
- `postventa.view`, `postventa.manage`

### Profile

Descripción:
- Breve descripción de lo que hace el módulo **Profile**. (Editar)

Acciones principales:
- `GET` `/profile` `profile.edit`
- `PATCH` `/profile` `profile.update`
- `POST` `/profile/photo` `profile.photo.update`
- `DELETE` `/profile` `profile.destroy`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Profile

Descripción:
- Administración de datos de la cuenta del usuario: nombre, correo, foto y preferencia.

Acciones principales:
- `GET /profile` — editar perfil.
- `PATCH /profile` — actualizar datos.
- `POST /profile/photo` — subir foto de perfil.
- `DELETE /profile` — eliminar cuenta (si aplica).

Consejos:
- Mantenga su correo y teléfono actualizados para notificaciones.

Permisos relacionados:
- `profile.view`, `profile.update`

### Related-lookup

Descripción:
- Breve descripción de lo que hace el módulo **Related-lookup**. (Editar)

Acciones principales:
- `GET` `/related-lookup` `related.lookup`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Related-lookup

Descripción:
- Utilidad para buscar y relacionar entidades (clientes, leads, incidencias) desde formularios y referencias cruzadas.

Cómo usar (usuario):
- En formularios, use el campo de búsqueda para asociar una entidad existente evitando duplicados.

Permisos relacionados:
- `related.lookup`

### Roles y permisos

Descripción:
- Breve descripción de lo que hace el módulo **Roles y permisos**. (Editar)

Acciones principales:
- `GET` `/roles` `roles.index`
- `GET` `/roles/data` `roles.data`
- `GET` `/roles/permissions` `roles.permissions`
- `POST` `/roles` `roles.store`
- `PUT` `/roles/{role}` `roles.update`
- `DELETE` `/roles/{role}` `roles.destroy`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Roles y permisos

Descripción:
- Administración de roles y permisos del sistema (spatie/laravel-permission). Permite crear roles, asignar permisos y revisar accesos.

Acciones principales:
- Crear/editar roles y asignar permisos desde la UI.

Cómo usar (usuario administrador):
- Verifique los permisos asignados a cada rol antes de otorgarlos.
- Cree roles basados en responsabilidades (admin, gerente, agente) y asigne únicamente los permisos necesarios.

Consejos:
- Use permisos granulares para limitar accesos por principio de menor privilegio.

Permisos relacionados:
- `roles.view`, `roles.create`, `roles.update`, `roles.delete`

### Root

Descripción:
- Breve descripción de lo que hace el módulo **Root**. (Editar)

Acciones principales:
- `GET` `/`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Root

Descripción:
- Ruta raíz que redirige a `login` o `dashboard` según sesión.

Permisos relacionados:
- Ninguno específico; comportamiento depende de `auth`.

### Usuarios

Descripción:
- Breve descripción de lo que hace el módulo **Usuarios**. (Editar)

Acciones principales:
- `GET` `/users` `users.index`
- `GET` `/users/data` `users.data`
- `GET` `/users/role-options` `users.roleOptions`
- `POST` `/users` `users.store`
- `PUT` `/users/{user}` `users.update`
- `DELETE` `/users/{user}` `users.destroy`

Cómo usar (pasos):
- Paso 1: ...
- Paso 2: ...

Permisos relacionados:
- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)
### Usuarios

Descripción:
- Gestión de cuentas de usuario: creación, edición, roles y permisos.

Acciones principales:
- Listado, creación, edición y eliminación de usuarios.
- Opciones para asignar roles y revisar permisos.

Cómo usar (usuario administrador):
- Cree usuarios con datos básicos y asigne el rol apropiado.
- Mantenga roles actualizados y audite accesos regularmente.

Permisos relacionados:
- `users.view`, `users.create`, `users.update`, `users.delete`


---

## Documentos originales (extractos)
### 2025-12-25-notas.md

> # CRM ATLANTIS — Notas de trabajo (2025-12-25)
> 
> > Actualización: el estado actual consolidado está en `docs/2025-12-26-notas.md`.
> 
> ## Objetivo del día
> - Dejar el proyecto corriendo en navegador con autenticación.
> - Montar el dashboard en Vue (Blade como shell) con Tailwind v4 + Flowbite.
> - Implementar el primer módulo real: **Usuarios** (CRUD + modales + buscador/paginación server-side).
> - Mejoras de UX: animaciones de modales, footer sticky, modo oscuro/claro.
> 
> ## Stack actual
> - Backend: Laravel 12 + MySQL (XAMPP)
> - Auth: Laravel Breeze (Blade)
> - Frontend: Vue 3 + Vite 7
> - UI: Tailwind CSS v4 + Flowbite
> 
> ## Arquitectura (por qué “funciona” sin una API separada)
> - Laravel sirve las vistas Blade como contenedor (wrapper) y los assets de Vite.
> - Vue se monta dentro del contenedor (div `#app`) y navega por rutas tipo `/dashboard` y `/users`.
> - Los datos se consumen vía endpoints JSON **en el mismo backend** (rutas internas protegidas por `auth`).
> 
> ## Rutas
> - `GET /` redirige a `login` o `dashboard` según sesión.
> - `GET /dashboard` (auth) muestra el wrapper del dashboard.
> - `GET /users` (auth) también muestra el mismo wrapper (Vue decide qué módulo renderizar por `window.location.pathname`).
> - Endpoints JSON de usuarios (auth):
>   - `GET /users/data` lista con paginación/búsqueda.
>   - `POST /users` crea.
>   - `PUT /users/{user}` actualiza.
>   - `DELETE /users/{user}` elimina.
> 
> Archivos:
> - routes/web.php
> - app/Http/Controllers/UserController.php
> 
> ## Base de datos
> - Configurada para MySQL (XAMPP) con la base `crmatlantis`.
> - Se corrieron migraciones.
> 
> ### Seeder de usuario interno
> - Existe `Database\Seeders\InternalUserSeeder` que crea/actualiza:
>   - Email: `admin@crmatlantis.local`
>   - Password: `Atlantis2025!`
> 
> Notas:
> - Recomendada práctica: cambiar esta contraseña en producción.
> - Actualización (2025-12-26): `DatabaseSeeder` ahora sí llama a `InternalUserSeeder` (junto con seeders de permisos y etapas de Leads). Ejecuta:
>   - `php artisan migrate --seed`
> 
> Archivos:
> - datab

### 2025-12-26-notas.md

> # CRM ATLANTIS — Notas de trabajo (2025-12-26)
> 
> ## Resumen de lo implementado
> 
> Hoy se consolidó el CRM con RBAC completo, módulos reales (Usuarios, Roles y Leads/Kanban), y mejoras de UX para que el dashboard sea usable como SPA dentro de Blade.
> 
> ## Stack actual
> - Backend: Laravel 12 + PHP 8.2
> - DB: MySQL (XAMPP)
> - Auth: Laravel Breeze (registro deshabilitado)
> - Frontend: Vue 3 + Vite 7
> - UI: Tailwind v4 + Flowbite
> - RBAC: spatie/laravel-permission
> 
> ## Arquitectura
> - Blade actúa como shell: `resources/views/dashboard.blade.php`.
> - Vue se monta en `#app` (`resources/js/app.js`) y cambia de módulo por `window.location.pathname` (`resources/js/components/App.vue`).
> - Endpoints JSON internos protegidos por `auth` + permisos (no hay API separada).
> 
> ## RBAC / Seguridad
> 
> ### Middleware Spatie en Laravel 12
> - Se registraron aliases en `bootstrap/app.php`:
>   - `permission`, `role`, `role_or_permission`
> 
> ### Permisos base creados por seeder
> - `users.view|create|update|delete`
> - `roles.view|create|update|delete`
> - `leads.view|create|update|delete`
> - `menu.users|menu.roles|menu.leads` (control de visibilidad del sidebar)
> 
> ### Roles base
> - `admin`: acceso total.
> - `dev`: acceso total.
> - `employee`: rol base sin permisos (para asignar granularmente).
> 
> > Importante: para ver un módulo debes dar permisos de ruta (`*.view`) y también el permiso de menú `menu.*` para que aparezca el link en el sidebar.
> 
> ## Módulo Usuarios
> 
> ### Backend
> - `GET /users/data`: paginación + búsqueda server-side.
> - Se añadió `GET /users/role-options`: retorna roles existentes para que el select sea dinámico.
> - Crear/editar usuario asigna roles usando Spatie (`syncRoles`).
> 
> ### Frontend
> - Tabla con buscador (debounce), paginación server-side, modales animados.
> - Select de rol cargado dinámicamente desde `/users/role-options`.
> 
> ## Módulo Roles
> 
> ### Backend
> - CRUD de roles vía JSON y catálogo de permisos:
>   - `GET /roles/data`
>   - `GET /roles/permissions`
>   - `POST /roles`, `PUT /roles/{role}`, `DELETE /roles/

### 2025-12-28-notas-parte-2.md

> # 2025-12-28 — CRM Atlantis (Parte 2: Postventa + Dashboard)
> 
> Fecha: 2025-12-28
> 
> ## Objetivo general
> - Migrar/replicar el módulo **Postventa** del sistema anterior dentro del CRM (Laravel + Vue), reutilizando el patrón de Leads (pipeline + lista) para Incidencias/Backlog.
> - Terminar los módulos “tablas simples” (Contadores y Certificados) e integrarlos a navegación/permisos.
> - Dejar un **Dashboard real** con métricas relevantes y listas accionables.
> 
> ---
> 
> ## 1) Postventa — Incidencias/Backlog (pipeline + lista)
> **Decisión de diseño**
> - Incidencias y Backlog se implementaron como una sola entidad (`incidences`) con etapas (`incidence_stages`), igual al pipeline de Leads.
> - La UX queda como Leads: **2 vistas** (Backlog/Pipeline y Lista), alternando por URL.
> 
> **Base de datos**
> - Migraciones:
>   - `database/migrations/*create_incidence_stages_table.php`
>   - `database/migrations/*create_incidences_table.php`
> - Campos relevantes:
>   - Correlativo (`INC-000001`), `stage_id`, `customer_id`, `title`, `date`, `priority`, `notes`, `archived_at`.
> 
> **Backend**
> - `app/Models/Incidence.php`, `app/Models/IncidenceStage.php`
> - `app/Http/Controllers/IncidenceController.php`
>   - `tableData()` lista
>   - `boardData()` backlog/pipeline
>   - `store()` crea incidencia y genera correlativo
>   - `moveStage()` mueve de etapa
>   - `archive()` archiva (usa `archived_at`)
> 
> **Frontend (Vue)**
> - `resources/js/components/BacklogBoard.vue` (Kanban/pipeline)
> - `resources/js/components/IncidenciasTable.vue` (Tabla/lista)
> - `resources/js/components/IncidenceQuickModal.vue`
>   - Modal global para “Nueva incidencia” usando `CustomEvent`.
> 
> **Rutas**
> - `routes/web.php`
>   - `GET /backlog` (vista backlog/pipeline)
>   - `GET /incidencias` (vista lista)
>   - `GET /backlog/board-data`
>   - `GET /incidencias/data`
>   - `POST /incidencias`
>   - `PATCH /incidencias/{incidence}/move-stage`
>   - `PATCH /incidencias/{incidence}/archive`
> 
> ---
> 
> ## 2) Postventa — Clientes (tabla + acción de relación con incidencia)
> **Qué se hizo**

### 2025-12-28-notas.md

> # 2025-12-28 — CRM Atlantis (resumen de avances)
> 
> Fecha: 2025-12-28
> 
> ## Objetivo general
> - Consolidar el CRM (Leads/Clientes) con UX consistente (modo oscuro + SweetAlert2).
> - Implementar módulo de Calendario (Opción A: agenda por usuario, privada) y recordatorios con notificaciones in-app (DB).
> 
> ---
> 
> ## 1) Modo oscuro (UI)
> **Qué se hizo**
> - Se ajustaron vistas Blade del perfil y navegación/header para que soporten `dark:` de Tailwind.
> 
> **Archivos clave**
> - `resources/views/profile/*`
> - `resources/views/layouts/navigation.blade.php`
> - `resources/views/components/*` relacionados con navegación/dropdowns
> 
> ---
> 
> ## 2) Leads (CRM): pipeline + lista, bloqueo “Ganado” y archivado
> **Qué se hizo**
> - Se implementaron 2 vistas para Leads:
>   - Pipeline/Kanban
>   - Lista/tabla (con tabs por etapa + select por fila para cambiar etapa)
> - Se agregó confirmación (SweetAlert2) antes de pasar a la etapa ganadora (`is_won`).
> - Una vez Ganado:
>   - Se bloquean movimientos (drag/select) para evitar cambios.
>   - Se habilita solo “Archivar” para ocultar del Kanban sin perder historial.
> - Se agregó `archived_at` para marcar leads archivados.
> 
> **Backend**
> - `app/Http/Controllers/LeadController.php`
>   - Endpoints para data de tabla y board.
>   - Movimiento de etapa.
>   - Archivado (`archived_at`).
> - Migración:
>   - `database/migrations/2025_12_27_000200_add_archived_at_to_leads_table.php`
> - Modelo:
>   - `app/Models/Lead.php` con casts/fillable para `archived_at`.
> 
> **Frontend**
> - `resources/js/components/LeadsBoard.vue`
>   - Confirmación antes de “Ganado”.
>   - Bloqueo de drag para Ganados/Archivados.
>   - Botón “Archivar” (visible en columna ganadora).
> - `resources/js/components/LeadsTable.vue`
>   - Select deshabilitado para Ganados/Archivados.
>   - Acción “Archivar” desde tabla.
>   - Columna “Archivado” mostrando `archived_at`.
> 
> **Rutas**
> - `routes/web.php`
>   - `GET /leads` (pipeline)
>   - `GET /leads/list` (tabla)
>   - `GET /leads/data` (data tabla)
>   - `GET /leads/board-data` (data kanban)
>   - `PATCH /l

### 2026-01-12-notas.md

> # 2026-01-12 — Notas (WhatsApp masivo + UX + fixes)
> 
> ## Objetivo del día
> 
> - Mejorar UX/UI en modo oscuro (filtros de Leads).
> - Diseñar e implementar un MVP de **campañas de WhatsApp “manual asistido”** dentro del CRM (sin API de WhatsApp).
> - Resolver issues de navegación y UX.
> - Separar claramente **Leads** vs **Clientes** en la selección de destinatarios.
> 
> > "Manual asistido" = el sistema **no envía** mensajes; solo **genera texto personalizado**, abre WhatsApp (`wa.me`) y el usuario marca el estado (enviado/abierto).
> 
> ---
> 
> ## 1) UI: filtros de Leads (modo oscuro)
> 
> **Archivo:** `resources/js/components/LeadsBoard.vue`
> 
> - Ajustes de estilos para inputs de filtros (select / date / botones) en dark mode:
>   - colores de texto / placeholder
>   - bordes y focus rings
>   - `color-scheme` para date inputs para icono/datepicker consistente en dark mode
> 
> Se reconstruyeron assets con Vite.
> 
> ---
> 
> ## 2) MVP: Campañas WhatsApp “manual asistido”
> 
> ### 2.1 Migración y tablas
> 
> **Migración:** `database/migrations/2026_01_12_120000_create_whatsapp_campaigns_tables.php`
> 
> - Tabla `whatsapp_campaigns`
> - Tabla `whatsapp_campaign_recipients`
>   - Recipients con relación polimórfica (`contactable_type`, `contactable_id`) para soportar Lead/Customer.
> 
> > Se ejecutó `php artisan migrate` con éxito.
> 
> ### 2.2 Modelos
> 
> - `app/Models/WhatsAppCampaign.php`
> - `app/Models/WhatsAppCampaignRecipient.php`
> 
> ### 2.3 Controller / API
> 
> **Controlador:** `app/Http/Controllers/WhatsAppCampaignController.php`
> 
> Endpoints:
> 
> - `GET /leads/whatsapp/recipients`
>   - Devuelve candidatos unificados en `contacts[]`.
>   - Soporta:
>     - `source`: `leads|customers`
>     - `stage_id` (solo para leads)
>     - `q` (búsqueda)
>     - `limit`
>     - `only_with_phone` (filtro de teléfono)
>   - Devuelve `counts` para UX:
>     - `total`
>     - `with_phone`
>     - `without_phone`
>     - `returned`
> 
> - `POST /leads/whatsapp-campaigns`
>   - Crea campaña con:
>     - `source` + `ids[]` (genérico: leads o customers)
>     - `message_template`
> 
> - `GET /le

### 2026-01-13-email-campanas.md

> # 2026-01-13 — Campañas de Email (plan + implementación inicial)
> 
> ## Qué es una campaña de email
> 
> - **Campaña** = un “lote” de envío informativo.
> - Tiene:
>   - `name` (opcional)
>   - `subject_template` + `body_template`
>   - lista de destinatarios (Leads o Customers)
>   - estados por destinatario (`pending/queued/sent/failed/skipped`).
> 
> A diferencia de WhatsApp manual asistido, aquí el sistema **sí envía** (via SMTP/mail provider), ideal para informativos.
> 
> ---
> 
> ## Implementación (MVP)
> 
> ### Base de datos
> 
> Migración:
> - `database/migrations/2026_01_13_090000_create_email_campaigns_tables.php`
> 
> Tablas:
> - `email_campaigns`
> - `email_campaign_recipients`
> - `email_unsubscribes`
> 
> ### Backend
> 
> Controladores:
> - `app/Http/Controllers/EmailCampaignController.php`
>   - `GET /leads/email/recipients` (Leads/Customers, filtros, conteos)
>   - `POST /leads/email-campaigns` (crear campaña + recipients)
>   - `GET /leads/email-campaigns` (historial)
>   - `GET /leads/email-campaigns/{campaign}` (detalle)
>   - `POST /leads/email-campaigns/{campaign}/send` (encola envío)
> 
> - `app/Http/Controllers/EmailUnsubscribeController.php`
>   - `GET /email/unsubscribe?email=...&token=...` (baja)
> 
> Jobs/Mail:
> - `app/Jobs/SendEmailCampaignRecipientJob.php`
> - `app/Mail/EmailCampaignRecipientMailable.php`
> 
> Notas:
> - Los envíos se encolan en `queue=mail`.
> - Se agrega automáticamente un footer con enlace de baja (token HMAC con `APP_KEY`).
> - Se excluye la etapa **Ganado** en Leads (se maneja como Customer).
> 
> ### Frontend
> 
> Pantalla:
> - `resources/js/components/LeadsEmailCampaign.vue`
> 
> Integración:
> - `resources/js/components/App.vue` (ruta SPA `/leads/email`)
> - `resources/js/components/Sidebar.vue` (link “Email masivo” en Pipeline)
> 
> ---
> 
> ## Configuración para enviar por Gmail (Google Workspace recomendado)
> 
> ### Para desarrollo (sin enviar de verdad)
> 
> - En `.env`:
>   - `MAIL_MAILER=log`
> 
> Esto escribe el contenido del email en logs en vez de enviarlo.
> 
> ### Para enviar con Gmail/Workspace (SMTP)
> 
> - En `.env`:
>   - `MAIL_MAILER=

### 2026-01-13-resumen-general.md

> # Resumen general — WhatsApp + Email (hasta ahora)
> 
> > Alcance: mejoras UI en Leads, módulo de WhatsApp masivo (manual asistido) y módulo de Email masivo (informativo, automatizado por cola), con separación Leads/Clientes y flujo de desuscripción.
> 
> ---
> 
> ## 1) Conceptos clave
> 
> ### 1.1 “Campaña”
> 
> - Una **campaña** es un “lote” de comunicación:
>   - Plantilla (mensaje/subject/body)
>   - Selección de destinatarios (Leads o Clientes)
>   - Seguimiento por destinatario (estado + timestamps + errores)
> 
> ### 1.2 WhatsApp vs Email
> 
> - **WhatsApp (manual asistido)**
>   - El CRM NO envía mensajes.
>   - Abre `wa.me` con texto prellenado y se marca estado manualmente.
> 
> - **Email (informativo, automatizado)**
>   - El CRM SÍ envía emails.
>   - Se encola por destinatario (jobs) para no bloquear la UI.
>   - Incluye enlace de **darse de baja** (unsubscribe) para campañas informativas.
> 
> ---
> 
> ## 2) UI/UX: mejoras en Leads
> 
> ### 2.1 Dark mode en filtros
> 
> Archivo:
> - `resources/js/components/LeadsBoard.vue`
> 
> Cambios:
> - Ajustes de estilos (texto/placeholder/bordes/focus) en inputs del filtro.
> - Mejora visual de date inputs en dark mode.
> 
> ---
> 
> ## 3) WhatsApp masivo (manual asistido)
> 
> ### 3.1 Base de datos
> 
> Migración:
> - `database/migrations/2026_01_12_120000_create_whatsapp_campaigns_tables.php`
> 
> Tablas:
> - `whatsapp_campaigns`
> - `whatsapp_campaign_recipients`
> 
> ### 3.2 Modelos
> 
> - `app/Models/WhatsAppCampaign.php`
> - `app/Models/WhatsAppCampaignRecipient.php`
> 
> ### 3.3 Backend (API)
> 
> Controlador:
> - `app/Http/Controllers/WhatsAppCampaignController.php`
> 
> Endpoints:
> - `GET /leads/whatsapp/recipients`
>   - Params: `source=leads|customers`, `stage_id` (leads), `q`, `limit`, `only_with_phone`
>   - Respuesta: `contacts[]`, `stages[]`, `counts{total,with_phone,without_phone,returned}`
>   - Nota: **Ganado** se excluye (se maneja como Cliente).
> 
> - `POST /leads/whatsapp-campaigns`
>   - Crea campaña con `source` + `ids[]` + `message_template`.
> 
> - `GET /leads/whatsapp-campaigns`
> - `GET /leads/whatsapp-campaigns/{campaign}`
> -

### 2026-01-16-notas.md

> # 2026-01-16 — Notas / Avance
> 
> ## Objetivo
> - Hacer visible y asignable la UI de campañas de correo masivo y ordenar las opciones de mensajería masiva en el menú.
> 
> ## Hecho hoy
> - Permisos:
>   - Se aseguró la existencia del permiso `menu.email` en seeders (Spatie Permissions).
>   - Se ajustó la UI de Roles para que los permisos `menu.*` (módulos del menú) no dependan de una lista fija, sino que se construyan desde los permisos disponibles.
> 
> - Menú (Sidebar):
>   - Se creó una nueva opción **“Bandeja de entrada”** con dropdown/flyout (hover cuando está colapsado), similar al comportamiento del menú de Leads/Pipeline.
>   - Se movieron las opciones de mensajería masiva al nuevo menú:
>     - **WhatsApp masivo** → `/leads/whatsapp`
>     - **Email masivo** → `/leads/email`
>   - Se corrigió el resaltado del ícono de Leads/Pipeline para que NO se marque cuando el usuario está dentro de “Bandeja de entrada” (rutas `/leads/whatsapp` y `/leads/email`).
> 
> ## Archivos tocados (principales)
> - `resources/js/components/RolesTable.vue`
>   - Módulos del menú ahora se derivan dinámicamente desde los permisos `menu.*`.
> 
> - `database/seeders/RolesAndPermissionsSeeder.php`
>   - Incluye `menu.email` dentro del set estándar de permisos.
> 
> - `resources/js/components/Sidebar.vue`
>   - Nuevo menú: “Bandeja de entrada” (expandido + flyout colapsado).
>   - Removidas las entradas de WhatsApp/Email del dropdown de Leads/Pipeline.
>   - Fix de estado “active” para Pipeline/Leads usando match exacto en `/leads`.
> 
> ## Comandos usados / verificación
> - Seed permisos: `php artisan db:seed --class=RolesAndPermissionsSeeder`
> - Build assets: `npm run build`
> 
> ## Commits locales relevantes
> - `bd38116` Add menu.email permission to roles UI
> - `ef236c2` Derive menu modules from permissions
> - `6f0fb65` Show Email masivo in collapsed Pipeline dropdown
> - `150c769` Add Bandeja de entrada menu for mass messaging
> - `fb1a955` Fix Leads highlight when using Bandeja de entrada
> 
> ## Notas
> - El repo remoto sigue en `origin/main` con commit b

### 2026-01-23-calendario-cron.md

> # Calendario: Scheduler + Cron (Hostinger)
> 
> Fecha: 2026-01-23
> 
> ## Objetivo
> Habilitar el envío automático de recordatorios del calendario según `reminder_minutes`.
> 
> ## Estado actual (funcionando)
> - Comando de recordatorios en `calendar:send-reminders`.
> - Scheduler ejecuta el comando cada minuto.
> - Cron en Hostinger dispara `schedule:run` cada minuto.
> - Notificaciones llegan correctamente al usuario asignado.
> 
> ## Archivos clave
> - app/Console/Kernel.php
> - app/Console/Commands/SendCalendarReminders.php
> - app/Notifications/CalendarEventReminderNotification.php
> - app/Http/Controllers/CalendarEventController.php
> 
> ## Cron en Hostinger
> Ruta final usada:
> 
> `/usr/bin/php /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new/artisan schedule:run >> /dev/null 2>&1`
> 
> Nota: en el panel, si ya agrega `/usr/bin/php /home/u652153415/`, entonces solo se escribe:
> 
> `domains/grupoatlantiscrm.eu/public_html/new/artisan schedule:run >> /dev/null 2>&1`
> 
> ## Cómo validar
> 1. Crear un evento con `reminder_minutes` corto (1–2 min).
> 2. Esperar 1–2 minutos.
> 3. Verificar notificación en la app.
> 4. Verificar resultado en “Cron Jobs” (Hostinger).
> 
> ## Nota de despliegue (UI calendario)
> - Los cambios visuales del calendario requieren **build** y subir los assets en `public/build/`.
> - Si el título del mes no se actualiza, ejecutar `npm run build` y desplegar los cambios.
> 
> ## Recomendaciones de mejora a futuro
> 1. **Colas + Worker persistente**
>    - Mover el envío a cola para mayor escalabilidad.
>    - Configurar un worker persistente (Supervisor/systemd o Horizon si se usa Redis).
> 
> 2. **Canales de notificación adicionales**
>    - Email o WhatsApp con preferencia por usuario.
> 
> 3. **Reintentos y fallback**
>    - Reintentos automáticos si falla el envío.
>    - Registro de fallos en tabla o log dedicado.
> 
> 4. **Optimización de consultas**
>    - Índice en `reminder_at` y `reminded_at` para acelerar el comando.
> 
> 5. **Zona horaria por usuario**
>    - Convertir `reminder_at` a la zona horaria del usuario si se re

### 2026-01-24-fix-storage-and-cloudflare.md

> # 2026-01-24 — Corrección: vendor, storage symlink y redirecciones Cloudflare
> 
> Resumen breve
> - Reproduje y resolví dos problemas principales mientras se intentaba levantar y usar el proyecto:
>   1. Error al ejecutar `php artisan serve` por dependencias faltantes (PHPUnit / sebastian/version).
>   2. En producción, las imágenes subidas (ícono/logo) no se mostraban en la web: faltaba exponer `storage/app/public` mediante `public/storage`, y además Cloudflare tenía reglas que redirigían el dominio a `vsbet.help` / `vsbet146.com`.
> 
> Acciones realizadas (comandos y resultados)
> 
> 1) Restaurar dependencias en local
> 
>   - `composer install --no-interaction --prefer-dist`
>   - Resultado: se instaló `sebastian/version` y otras dependencias; autoload regenerado. Con esto `php artisan serve` dejó de fallar por la clase faltante.
> 
> 2) Revisar flujo de subida de iconos (código)
> 
>   - Archivo inspeccionado: `app/Http/Controllers/SettingsController.php`.
>   - Comportamiento: guarda en `storage/app/public/settings` y devuelve `asset('storage/settings/...')`.
> 
> 3) Diagnóstico en producción
> 
>   - Verifiqué que el archivo existe en el origen:
>     - `ls -la storage/app/public/settings` → `logo_mark.png` presente.
>   - Verifiqué la presencia del enlace público:
>     - `ls -la public` inicialmente no mostraba `storage`.
>     - `readlink -f public/storage` devolvía una ruta, pero `stat public/storage` indicaba "No such file or directory" → symlink ausente/roto.
> 
> 4) Crear/rehacer el symlink público
> 
>   - Comando recomendado (desde la raíz del proyecto):
>     - `php artisan storage:link`
>   - Alternativa manual:
>     - `rm -rf public/storage`
>     - `ln -s "$(pwd)/storage/app/public" public/storage`
>   - Verificación:
>     - `ls -la public/storage/settings` mostró `logo_mark.png`.
> 
> 5) Detectar redirección 301 (Cloudflare)
> 
>   - `curl -I -L -v https://tu-dominio.com/storage/settings/logo_mark.png` devolvió un `301` que redirigía a `https://vsbet.help/...` y luego a `https://vsbet146.com/`.
>   - Cabeceras mostraron `

### 2026-01-24-resumen-sesion-logo.md

> # 2026-01-24 — Resumen de sesión (logos, storage y caché)
> 
> ## Objetivo
> Resolver problemas de subida/visualización de logos (ícono y logo largo), corregir cacheo, mejorar tamaño/claridad en el menú y asegurar despliegue correcto.
> 
> ---
> 
> ## 1) Error inicial al levantar local
> **Síntoma:** `php artisan serve` fallaba por clase faltante (`SebastianBergmann\Version`).
> **Acción:** `composer install --no-interaction --prefer-dist`.
> **Resultado:** dependencias restauradas y autoload regenerado.
> 
> ---
> 
> ## 2) Subida de logos en producción no se veía
> **Hallazgos:**
> - El archivo sí se guardaba en `storage/app/public/settings`.
> - Faltaba o estaba roto `public/storage` (symlink), o el CDN/Cloudflare redirigía a dominios extraños.
> 
> **Acciones:**
> - Verificación de symlink y creación con `php artisan storage:link` o `ln -s`.
> - Diagnóstico de redirecciones con `curl -I -L -v`.
> - Ajustes en Cloudflare (Page Rules/DNS) para quitar redirects externos.
> 
> ---
> 
> ## 3) Cache (cambios no se reflejaban)
> **Causa:** cache del navegador/Cloudflare. El archivo se actualizaba, pero la URL era la misma.
> 
> **Solución aplicada (la más estable):**
> - Guardar logos con nombre único por subida (timestamp) y mantener un **manifest** JSON para saber cuál es el actual.
> - Backend y vistas leen el manifest y sirven la URL nueva.
> 
> **Resultado:** los cambios se reflejan con recarga simple (sin Ctrl+F5).
> 
> ---
> 
> ## 4) Calidad y tamaño en el menú
> **Problema:** el logo se veía pequeño o borroso.
> 
> **Ajustes hechos:**
> - Aumentamos tamaño de render en sidebar.
> - Aumentamos resolución de imagen procesada.
> - Añadimos **recorte automático de transparencia** para eliminar padding y que el logo “llene” mejor la caja.
> 
> ---
> 
> ## Cambios principales en código
> 
> ### Backend
> - [app/Http/Controllers/SettingsController.php](app/Http/Controllers/SettingsController.php)
>   - Resize del logo mark y logo full.
>   - Fallback seguro si falla el procesamiento.
>   - **Archivos versionados** (nombres únicos).
>   - **Manifest JSON** con el path actual.

### chatbot-poc.md

> # Chatbot POC (búsqueda de documentación)
> 
> Este POC proporciona un endpoint simple que busca en la carpeta `docs/` los archivos que coinciden con la consulta del usuario y devuelve extractos relevantes. Objetivo: validar flujo RAG básico sin capacidades de LLM ni embeddings todavía.
> 
> Endpoint
> - `POST /api/chatbot/query` (POC)
> - Body JSON: `{ "q": "texto de la pregunta" }`
> 
> Qué devuelve
> - JSON con `data` => array de up to 5 items: `{ file, score, snippet }`.
> 
> Limitaciones del POC
> - Búsqueda textual simple (conteo de tokens), sin embeddings ni semántica.
> - No autenticado — proteger en producción.
> - Latencia proporcional al número de archivos en `docs/` (este POC lee archivos en cada request).
> 
> Pasos siguientes recomendados (mejoras)
> 1. Indexar e insertar en vector DB local (FAISS) y usar embeddings (`sentence-transformers`) para búsqueda semántica.
> 2. Añadir pipeline que precompute embeddings y actualice índice cuando se agreguen documentos.
> 3. Implementar RAG: recuperar top-k + opcional generación con LLM (o plantillas) para mejorar respuestas.
> 4. Proteger el endpoint con autenticación y cuota, y agregar caché para respuestas frecuentes.
> 
> Cómo probar localmente
> 1. Arranca servidor Laravel:
> ```bash
> php artisan serve
> ```
> 2. Llamada ejemplo (curl):
> ```bash
> curl -X POST http://127.0.0.1:8000/api/chatbot/query \
>   -H "Content-Type: application/json" \
>   -d '{"q": "¿cómo sincronizo permisos?"}'
> ```
> 
> Integración futura con interfaz
> - Podemos exponer un modal/chat en el dashboard que consuma este endpoint y muestre las coincidencias y recomendaciones de acción.
> 
> Fin.

### cron-endpoint.md

> **Cron Endpoint (Fallback) — Setup and usage**
> 
> - **Purpose:** provide a protected HTTP endpoint that triggers `php artisan schedule:run` and a single `queue:work --once` run. Use an external cron service (cron-job.org, easycron) to call it every minute when Hostinger's cron panel or `crontab` is unreliable.
> 
> - **Files changed:** `routes/web.php` — added POST `/_internal/cron/{token}`.
> 
> - **Add to `.env`:**
> 
>   CRON_TOKEN=replace_with_a_long_random_string
> 
> - **How to test locally / from your machine:**
> 
> ```bash
> # Example (replace <TOKEN> and domain)
> curl -X POST https://grupoatlantiscrm.eu/new/_internal/cron/<TOKEN>
> ```
> 
> Expected response: `OK` (HTTP 200). Check Laravel logs and queue behavior to confirm jobs ran.
> 
> - **Configure cron-job.org (example):**
>   - Create a free account.
>   - Add a new job:
>     - URL: `https://grupoatlantiscrm.eu/new/_internal/cron/<TOKEN>`
>     - Method: POST
>     - Interval: 1 minute
> 
> - **Security notes:**
>   - Keep `CRON_TOKEN` secret and long (32+ chars).
>   - Use HTTPS only. Consider IP allowlisting or short-lived tokens if needed.
> 
> - **Deploy:**
>   - Commit and deploy the code change to production (or paste the route snippet into `routes/web.php`).
>   - Add `CRON_TOKEN` to the production `.env`.
>   - Configure the external cron to POST the URL every minute.

### deploy-hostinger-subdominio.md

> # Deploy en Hostinger (hPanel) a subdominio — CRMATLANTIS (Laravel)
> 
> Esta guía documenta el proceso real usado para subir este proyecto Laravel a un **subdominio** en Hostinger usando **Auto Deploy (Git)**, y cómo resolvimos los errores típicos (500 por cachés de bootstrap, APP_KEY, Vite manifest, etc.).
> 
> ## Objetivo
> 
> - Subir el proyecto a un subdominio (ej. `new.grupoatlantiscrm.eu`) **sin interferir** con el dominio principal.
> - Mantener la estructura correcta de Laravel:
>   - Código completo en una carpeta privada del sitio (ej. `.../public_html/new`)
>   - Servir públicamente solo `public/` (ej. `.../public_html/new/public`)
> 
> ## 1) Rutas correctas (lo más importante)
> 
> ### Carpeta donde se despliega el repo (Auto Deploy)
> 
> - **Repo →** `/home/u652153415/domains/grupoatlantiscrm.eu/public_html/new`
> 
> Ahí deben existir `artisan`, `composer.json`, `app/`, `routes/`, `public/`, etc.
> 
> ### Carpeta raíz del subdominio ("Document Root" en concepto)
> 
> En Hostinger no suele llamarse “DocumentRoot”; es el campo de **Carpeta/Directorio/Ruta** del subdominio.
> 
> - **Subdominio →** `/home/u652153415/domains/grupoatlantiscrm.eu/public_html/new/public`
> 
> Esto evita exponer `.env`, `storage/`, etc.
> 
> ## 2) Preparación de `.env` (servidor)
> 
> En el servidor, dentro de la carpeta del proyecto:
> 
> ```bash
> cd /home/u652153415/domains/grupoatlantiscrm.eu/public_html/new
> ```
> 
> Crear `.env` desde el ejemplo:
> 
> ```bash
> cp .env.example .env
> ```
> 
> Editar `.env` (ej. con `nano`):
> 
> ```bash
> nano .env
> ```
> 
> Salir guardando en nano:
> - `Ctrl+O` → Enter → `Ctrl+X`
> 
> Valores mínimos:
> - `APP_ENV=production`
> - `APP_DEBUG=false`
> - `APP_URL=https://new.grupoatlantiscrm.eu`
> - `DB_CONNECTION=mysql`
> - `DB_HOST=localhost` (en Hostinger suele funcionar si la BD es local)
> - `DB_PORT=3306`
> - `DB_DATABASE=...`
> - `DB_USERNAME=...`
> - `DB_PASSWORD=...`
> 
> Notas:
> - Si `DB_CONNECTION=sqlite`, Laravel ignorará `DB_HOST/DB_USERNAME/...`.
> 
> ## 3) Composer (servidor)
> 
> ### Instalación limpia (recomendado si hubo errores previos)
> 
> Si `compos

### deploy-process.md

> # Proceso de despliegue (CRM ATLANTIS)
> 
> Este documento captura el flujo de despliegue recomendado y los comandos exactos para no olvidar pasos críticos relacionados con permisos, migraciones y caches.
> 
> Resumen rápido
> - El pipeline/CI ejecuta verificaciones, migraciones de prueba y sincroniza permisos (`permissions:sync`).
> - En el servidor de producción seguir el orden: actualizar código → migrar → sincronizar permisos → (opcional) ejecutar seeders → limpiar caches → reiniciar workers.
> 
> 1) Preparación local / PR
> - Commit y push del branch:
> 
> ```bash
> git add .
> git commit -m "feat: ..."
> git push origin feature/x
> ```
> 
> - Abrir PR y revisar cambios de rutas/permisos.
> 
> 2) Qué hace CI (recomendado)
> - Checkout, instalar dependencias.
> - Ejecuta migraciones sobre DB de prueba.
> - Ejecuta `php artisan permissions:sync` (detecta y crea permisos desde middleware `permission:`).
> - Ejecuta opcionalmente `php artisan db:seed --class=RolesAndPermissionsSeeder` (en CI es seguro; en prod evaluar antes).
> - Limpia caches.
> 
> 3) Deploy en servidor (pasos detallados)
> 
> - (Opcional) Poner la app en mantenimiento:
> 
> ```bash
> php artisan down
> ```
> 
> - Actualizar código y dependencias:
> 
> ```bash
> git pull origin main
> composer install --no-interaction --no-dev --prefer-dist
> npm ci && npm run build  # si se modificó frontend
> ```
> 
> - Ejecutar migraciones y sincronizar permisos (orden recomendado):
> 
> Si tus seeders dependen de permisos existentes:
> ```bash
> php artisan migrate --force
> php artisan permissions:sync
> php artisan db:seed --class=RolesAndPermissionsSeeder   # opcional, revisar efecto
> ```
> 
> Si no ejecutas seeders globales en prod y prefieres crear permisos antes:
> ```bash
> php artisan permissions:sync
> php artisan migrate --force
> ```
> 
> - Limpiar caches y reiniciar workers:
> 
> ```bash
> php artisan cache:clear
> php artisan config:clear
> php artisan route:clear
> php artisan view:clear
> php artisan optimize:clear
> php artisan queue:restart
> ```
> 
> - Sacar la app de mantenimiento:
> 
> ```bash
> php artisan up
> ```
> 
> 4) Considerac

### manual_usuario.txt

> MANUAL DE USUARIO — CRM ATLANTIS
> Fecha: 2026-01-23
> 
> Objetivo
> Este manual explica de forma simple las funciones del sistema y cómo usar cada sección.
> 
> 1) Acceso y navegación
> - Iniciar sesión con tu usuario y contraseña.
> - Usa el menú lateral para ir a cada módulo.
> - El título superior muestra la sección actual.
> 
> 2) Dashboard (Inicio)
> Qué ves:
> - KPIs y métricas resumidas según tus permisos.
> - Listas rápidas de trabajo: próximos eventos, certificados por vencer, últimos leads e incidencias.
> Para qué sirve:
> - Tener un panorama rápido del trabajo pendiente.
> 
> 3) Leads (Pipeline)
> 3.1 Pipeline (Vista Kanban)
> - Muestra oportunidades por etapas.
> - Arrastra tarjetas entre etapas para actualizar el estado.
> - Ideal para seguimiento diario del embudo.
> 
> 3.2 Leads (Vista Lista)
> - Lista completa con filtros y búsqueda.
> - Permite editar, actualizar estado y datos del lead.
> 
> 3.3 Desistidos
> - Lista de leads marcados como desistidos.
> - Sirve para mantener histórico y reportes.
> 
> 3.4 Zona de espera
> - Leads en pausa temporal.
> - Útil para reactivar en el futuro.
> 
> 4) Clientes
> - Listado general de clientes.
> - Acceso a datos principales y seguimiento.
> 
> 5) Agenda y recordatorios (Calendario)
> - Crea eventos y recordatorios.
> - Visualiza próximos compromisos.
> - Recibes notificaciones de eventos asignados.
> 
> 6) Incidencias (Postventa)
> 6.1 Incidencias (Vista Lista)
> - Registro y seguimiento de incidencias.
> - Estado, responsable y fechas.
> 
> 6.2 Backlog (Vista tablero)
> - Vista tipo tablero para priorizar y mover incidencias por etapas.
> 
> 7) Postventa
> 7.1 Clientes (Postventa)
> - Clientes en fase postventa.
> 
> 7.2 Contadores
> - Gestión de contadores relacionados a clientes.
> 
> 7.3 Certificados
> - Gestión de certificados y fechas importantes.
> - Alertas/recordatorios por vencimiento.
> 
> 8) Bandeja de entrada (Comunicaciones)
> 8.1 WhatsApp masivo (manual asistido)
> Qué hace:
> - El sistema NO envía mensajes.
> - Genera mensajes personalizados y abre WhatsApp con el texto listo.
> 
> Cómo usarlo:
> 1) Selecciona origen: Leads o Cli

### mapping_old_to_new.md

> # Mapeo de campos — Sistema antiguo -> Nuevo sistema
> 
> Este documento contiene el mapeo inicial de las tablas y atributos del sistema antiguo (ver `docs/tablas_y_atributos.txt`) hacia las tablas/modelos del nuevo proyecto Laravel.
> 
> ## Notas generales
> - Campos no existentes en el modelo nuevo se marcan como `--` y requieren decisión: crear columna, guardar en `observacion`/`notes`, o descartar.
> - Relacionar `cliente_id` normalmente a `customer_id` en `Customer` model.
> - Para imports: exportar CSV con los encabezados indicados en la columna `old_field`.
> 
> ---
> 
> ### clientes -> `Customer` (app/Models/Customer.php)
> 
> old_field -> new_field (nota)
> - id -> id
> - nombre -> name
> - empresa -> company_name
> - tipo -> document_type? (revisar significado)
> - documento -> document_number
> - telefono -> contact_phone
> - correo -> contact_email
> - motivo -> -- (mover a `observacion`/notes en Lead/Customer)
> - ciudad -> company_address? (o `observacion`)
> - migracion -> migracion (Lead model tiene migracion; Customer no) -> guardar en `observacion` o crear columna
> - referencia -> -- (observacion)
> - fecha_contacto -> -- (no hay campo directo) -> guardar en `observacion` o crear campo
> - fecha_creacion -> created_at
> - estado -> -- (mapear a estado/active si aplica)
> - post_precio -> -- (no equivalente)
> - post_rubro -> --
> - post_ano -> --
> - post_mes -> --
> - post_link -> --
> - post_usuario -> -- (sensible)
> - post_contrasena -> -- (NO subir credenciales; descartar)
> 
> Recomendación: exportar estos campos y decidir por cada uno si crear columna en `customers` o guardarlos en `observacion`/metadatos.
> 
> ---
> 
> ### actividades -> `CalendarEvent` (app/Models/CalendarEvent.php)
> 
> old_field -> new_field (nota)
> - id -> id
> - cliente_id -> related_id (set `related_type` = `App\\Models\\Customer`)
> - usuario_id -> assigned_to (o created_by según origen)
> - tipo -> -- (guardar en `description` o crear `type`)
> - asunto -> title
> - fecha_hora -> start_at (usar `start_at`; `end_at` puede quedar null)
> - resultado -> descripti

### permissions-workflow.md

> # Permisos y flujo de creación de módulos (CRM ATLANTIS)
> 
> Este documento resume la solución implementada para gestionar permisos y la creación de módulos de forma segura y reproducible.
> 
> Resumen rápido
> - `php artisan permissions:sync` — detecta permisos desde las rutas (middleware `permission:`) y crea en la BD los permisos encontrados; además genera permisos estándar `view/create/update/delete` por recurso y `menu.<recurso>` para control del sidebar. Limpia la caché de Spatie al finalizar.
> - `php artisan make:module {name} [--prefix=...]` — genera un controlador stub en `app/Http/Controllers`, un archivo de rutas en `routes/modules/{resource}.php` (protegido por permisos) y un seeder en `database/seeders` con permisos básicos del módulo.
> - Seeder `RolesAndPermissionsSeeder` — ahora invoca `permissions:sync` y asegura roles base (`admin`, `dev`, `employee`) y asigna permisos existentes a `admin` y `dev`.
> - Workflow CI: añadida la acción GitHub `.github/workflows/permissions-sync.yml` que ejecuta migraciones y `permissions:sync` en PRs/push a `main`.
> 
> Por qué este enfoque
> - Automatiza la creación de permisos para evitar olvidos humanos al añadir módulos.
> - Mantiene reproducibilidad: los seeders y migrations permiten versionar cambios.
> - Añade un punto de control (revisión en PR / UI) antes de exponer menús o asignar permisos en producción.
> 
> Uso del comando `permissions:sync`
> - Ejecutar localmente después de añadir nuevas rutas o cambios de middleware:
> ```bash
> php artisan permissions:sync
> ```
> - Qué hace: escanea todas las rutas registradas, extrae las cadenas del middleware `permission:...`, crea esos permisos si faltan y añade permisos estándar y `menu.*` por recurso.
> 
> Uso del generador `make:module`
> - Ejemplo:
> ```bash
> php artisan make:module certificado --prefix=postventa/certificados
> ```
> - Resultado:
>   - `app/Http/Controllers/CertificadoController.php` (stub)
>   - `routes/modules/certificados.php` (rutas protegidas por `certificados.view|create|update|delete`)
>   - `

### tablas_y_atributos.txt

> actividades
> - id
> - cliente_id
> - usuario_id
> - tipo
> - asunto
> - fecha_hora
> - resultado
> 
> clientes
> - id
> - nombre
> - empresa
> - tipo
> - documento
> - telefono
> - correo
> - motivo
> - ciudad
> - migracion
> - referencia
> - fecha_contacto
> - fecha_creacion
> - estado
> - post_precio
> - post_rubro
> - post_ano
> - post_mes
> - post_link
> - post_usuario
> - post_contrasena
> 
> clientes_backup
> - id
> - nombre
> - empresa
> - tipo
> - documento
> - telefono
> - correo
> - motivo
> - ciudad
> - migracion
> - referencia
> - fecha_contacto
> - fecha_creacion
> - estado
> 
> contador
> - id
> - nro
> - comercio
> - nom_contador
> - titular_tlf
> - telefono
> - telefono_actu
> - link
> - usuario
> - contrasena
> - created_at
> 
> contadores_clientes
> - id
> - contador_id
> - cliente_id
> - fecha_asignacion
> 
> evento
> - id
> - titulo
> - color
> 
> incidencias
> - id
> - correlativo
> - nombre_incidencia
> - cliente_id
> - usuario_id
> - fecha
> - prioridad
> - observaciones
> - fecha_creacion
> - columna_backlog
> 
> oportunidades
> - id
> - cliente_id
> - usuario_id
> - titulo
> - descripcion
> - valor_estimado
> - probabilidad
> - estado
> - fecha_apertura
> - fecha_cierre_estimada
> - fecha_modificacion
> - alta_prioridad
> - actividad
> - fecha_actividad
> 
> reuniones
> - id
> - cliente_id
> - usuario_id
> - evento_id
> - ultima_notificacion
> - titulo
> - descripcion
> - fecha
> - hora_inicio
> - hora_fin
> - ubicacion
> - estado
> - archivado
> - archivado_por
> - archivado_en
> - recordatorio
> - observaciones
> 
> usuarios
> - id
> - nombre
> - usuario
> - password
> - perfil
> - foto
> - estado
> - ultimo_login
> - fecha
> - sesion_token
> - sesion_expira
> - remember_token
> - remember_expires
> 
> tenants
> - id
> - ruc
> - subdomain
> - status
> - created_at
> - updated_at
> 
> tenant_db
> - tenant_id
> - db_host
> - db_name
> - db_user
> - db_pass
> - db_charset
> - created_at
> - updated_at
> 
> admin_users
> - id
> - username
> - password_hash
> - is_active
> - last_login_at
> - created_at
> - updated_at
> 
> audit_log
> - id
> - admin_user_id
> - tenant_id
> - action
> - details
> - ip
> - created_at
> 
> notificaciones_certificados
> - id
> - certificado_id
> - tipo
> - fecha_envio
> - usuario_id
> 
> whatsapp_plantillas
> - id
> - nombre
> - contenido
> - tipo
> - creado_por
> - creado
