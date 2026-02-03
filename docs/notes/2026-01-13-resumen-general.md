# Resumen general — WhatsApp + Email (hasta ahora)

> Alcance: mejoras UI en Leads, módulo de WhatsApp masivo (manual asistido) y módulo de Email masivo (informativo, automatizado por cola), con separación Leads/Clientes y flujo de desuscripción.

---

## 1) Conceptos clave

### 1.1 “Campaña”

- Una **campaña** es un “lote” de comunicación:
  - Plantilla (mensaje/subject/body)
  - Selección de destinatarios (Leads o Clientes)
  - Seguimiento por destinatario (estado + timestamps + errores)

### 1.2 WhatsApp vs Email

- **WhatsApp (manual asistido)**
  - El CRM NO envía mensajes.
  - Abre `wa.me` con texto prellenado y se marca estado manualmente.

- **Email (informativo, automatizado)**
  - El CRM SÍ envía emails.
  - Se encola por destinatario (jobs) para no bloquear la UI.
  - Incluye enlace de **darse de baja** (unsubscribe) para campañas informativas.

---

## 2) UI/UX: mejoras en Leads

### 2.1 Dark mode en filtros

Archivo:
- `resources/js/components/LeadsBoard.vue`

Cambios:
- Ajustes de estilos (texto/placeholder/bordes/focus) en inputs del filtro.
- Mejora visual de date inputs en dark mode.

---

## 3) WhatsApp masivo (manual asistido)

### 3.1 Base de datos

Migración:
- `database/migrations/2026_01_12_120000_create_whatsapp_campaigns_tables.php`

Tablas:
- `whatsapp_campaigns`
- `whatsapp_campaign_recipients`

### 3.2 Modelos

- `app/Models/WhatsAppCampaign.php`
- `app/Models/WhatsAppCampaignRecipient.php`

### 3.3 Backend (API)

Controlador:
- `app/Http/Controllers/WhatsAppCampaignController.php`

Endpoints:
- `GET /leads/whatsapp/recipients`
  - Params: `source=leads|customers`, `stage_id` (leads), `q`, `limit`, `only_with_phone`
  - Respuesta: `contacts[]`, `stages[]`, `counts{total,with_phone,without_phone,returned}`
  - Nota: **Ganado** se excluye (se maneja como Cliente).

- `POST /leads/whatsapp-campaigns`
  - Crea campaña con `source` + `ids[]` + `message_template`.

- `GET /leads/whatsapp-campaigns`
- `GET /leads/whatsapp-campaigns/{campaign}`
- `PATCH /leads/whatsapp-campaign-recipients/{recipient}`

Permisos:
- Para `source=customers` se requiere `customers.view`.

### 3.4 Frontend

Pantalla:
- `resources/js/components/LeadsWhatsAppCampaign.vue`

Integración:
- `resources/js/components/App.vue` (ruta `/leads/whatsapp`)
- `resources/js/components/Sidebar.vue` (link en Pipeline, expandido y colapsado)

Fixes relevantes:
- Validación del querystring: `only_with_phone` ahora se envía como `1/0`.
- Conteos reales: se muestran totales y “sin teléfono” para explicar listas vacías.

---

## 4) Email masivo (informativo + unsubscribe)

### 4.1 Base de datos

Migración:
- `database/migrations/2026_01_13_090000_create_email_campaigns_tables.php`

Tablas:
- `email_campaigns`
- `email_campaign_recipients`
- `email_unsubscribes`

### 4.2 Modelos

- `app/Models/EmailCampaign.php`
- `app/Models/EmailCampaignRecipient.php`
- `app/Models/EmailUnsubscribe.php`

### 4.3 Backend (API + envío por cola)

Controlador:
- `app/Http/Controllers/EmailCampaignController.php`

Endpoints:
- `GET /leads/email/recipients`
  - Params: `source=leads|customers`, `stage_id` (leads), `q`, `limit`, `only_with_email`
  - Respuesta: `contacts[]`, `stages[]`, `counts{total,with_email,without_email,returned}`
  - Nota: **Ganado** se excluye para Leads.

- `POST /leads/email-campaigns`
  - Crea campaña con `source` + `ids[]` + `subject_template` + `body_template`.

- `GET /leads/email-campaigns`
- `GET /leads/email-campaigns/{campaign}`

- `POST /leads/email-campaigns/{campaign}/send`
  - Encola el envío por destinatario.
  - Param opcional: `include_failed` para reintentar fallidos.

Permisos:
- Para `source=customers` se requiere `customers.view`.

Job y Mailable:
- `app/Jobs/SendEmailCampaignRecipientJob.php`
- `app/Mail/EmailCampaignRecipientMailable.php`

### 4.4 Unsubscribe (público)

Controlador:
- `app/Http/Controllers/EmailUnsubscribeController.php`

Ruta pública:
- `GET /email/unsubscribe?email=...&token=...`

Vistas:
- `resources/views/email/unsubscribed.blade.php`
- `resources/views/email/unsubscribe_invalid.blade.php`

Detalles:
- El token se genera con HMAC (`hash_hmac('sha256', email, APP_KEY)`), para evitar bajas “forjadas”.
- Los emails desuscritos se guardan en `email_unsubscribes`.
- Los envíos posteriores los omiten y marcan `skipped`.

### 4.5 Frontend

Pantalla:
- `resources/js/components/LeadsEmailCampaign.vue`

Integración:
- `resources/js/components/App.vue` (ruta `/leads/email`)
- `resources/js/components/Sidebar.vue` (link “Email masivo” en Pipeline)

---

## 5) Rutas agregadas

Archivo:
- `routes/web.php`

- UI shell:
  - `GET /leads/whatsapp`
  - `GET /leads/email`

- APIs WhatsApp:
  - `GET /leads/whatsapp/recipients`
  - `GET /leads/whatsapp-campaigns`
  - `GET /leads/whatsapp-campaigns/{campaign}`
  - `POST /leads/whatsapp-campaigns`
  - `PATCH /leads/whatsapp-campaign-recipients/{recipient}`

- APIs Email:
  - `GET /leads/email/recipients`
  - `GET /leads/email-campaigns`
  - `GET /leads/email-campaigns/{campaign}`
  - `POST /leads/email-campaigns`
  - `POST /leads/email-campaigns/{campaign}/send`

- Público:
  - `GET /email/unsubscribe`

---

## 6) Configuración de correo (Gmail / Google Workspace)

### 6.1 Desarrollo (sin enviar)

- En `.env`:
  - `MAIL_MAILER=log`

### 6.2 Envío real por Gmail SMTP

Recomendado: correo del dominio con Google Workspace.

- En `.env`:
  - `MAIL_MAILER=smtp`
  - `MAIL_HOST=smtp.gmail.com`
  - `MAIL_PORT=587`
  - `MAIL_ENCRYPTION=tls`
  - `MAIL_USERNAME=tu_correo@tudominio.com`
  - `MAIL_PASSWORD=APP_PASSWORD`
  - `MAIL_FROM_ADDRESS=tu_correo@tudominio.com`
  - `MAIL_FROM_NAME="CRM Atlantis"`

### 6.3 Cola (recomendado)

- Configurar `QUEUE_CONNECTION` (ej: `database`)
- Ejecutar worker:
  - `php artisan queue:work --queue=mail`

> Si `QUEUE_CONNECTION=sync`, el envío ocurre en la request.

---

## 7) Documentos relacionados

- `docs/2026-01-12-notas.md` (WhatsApp + fixes de UI y navegación)
- `docs/2026-01-13-email-campanas.md` (Email masivo + configuración)

---

## 8) Próximos pasos sugeridos

- Preview de email (render con destinatario ejemplo antes de crear/enviar).
- Resumen de progreso en campaña (sent/failed/queued/pending).
- Mejor UX para contactos sin teléfono/email (no seleccionables, etiquetas claras).
- Panel de “configuración de correo” (pensando en vender el sistema por empresa).
