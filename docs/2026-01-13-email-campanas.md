# 2026-01-13 — Campañas de Email (plan + implementación inicial)

## Qué es una campaña de email

- **Campaña** = un “lote” de envío informativo.
- Tiene:
  - `name` (opcional)
  - `subject_template` + `body_template`
  - lista de destinatarios (Leads o Customers)
  - estados por destinatario (`pending/queued/sent/failed/skipped`).

A diferencia de WhatsApp manual asistido, aquí el sistema **sí envía** (via SMTP/mail provider), ideal para informativos.

---

## Implementación (MVP)

### Base de datos

Migración:
- `database/migrations/2026_01_13_090000_create_email_campaigns_tables.php`

Tablas:
- `email_campaigns`
- `email_campaign_recipients`
- `email_unsubscribes`

### Backend

Controladores:
- `app/Http/Controllers/EmailCampaignController.php`
  - `GET /leads/email/recipients` (Leads/Customers, filtros, conteos)
  - `POST /leads/email-campaigns` (crear campaña + recipients)
  - `GET /leads/email-campaigns` (historial)
  - `GET /leads/email-campaigns/{campaign}` (detalle)
  - `POST /leads/email-campaigns/{campaign}/send` (encola envío)

- `app/Http/Controllers/EmailUnsubscribeController.php`
  - `GET /email/unsubscribe?email=...&token=...` (baja)

Jobs/Mail:
- `app/Jobs/SendEmailCampaignRecipientJob.php`
- `app/Mail/EmailCampaignRecipientMailable.php`

Notas:
- Los envíos se encolan en `queue=mail`.
- Se agrega automáticamente un footer con enlace de baja (token HMAC con `APP_KEY`).
- Se excluye la etapa **Ganado** en Leads (se maneja como Customer).

### Frontend

Pantalla:
- `resources/js/components/LeadsEmailCampaign.vue`

Integración:
- `resources/js/components/App.vue` (ruta SPA `/leads/email`)
- `resources/js/components/Sidebar.vue` (link “Email masivo” en Pipeline)

---

## Configuración para enviar por Gmail (Google Workspace recomendado)

### Para desarrollo (sin enviar de verdad)

- En `.env`:
  - `MAIL_MAILER=log`

Esto escribe el contenido del email en logs en vez de enviarlo.

### Para enviar con Gmail/Workspace (SMTP)

- En `.env`:
  - `MAIL_MAILER=smtp`
  - `MAIL_HOST=smtp.gmail.com`
  - `MAIL_PORT=587`
  - `MAIL_ENCRYPTION=tls`
  - `MAIL_USERNAME=tu_correo@tudominio.com`
  - `MAIL_PASSWORD=APP_PASSWORD` (recomendado con 2FA)
  - `MAIL_FROM_ADDRESS=tu_correo@tudominio.com`
  - `MAIL_FROM_NAME="CRM Atlantis"`

> Recomendación: usar **Google Workspace** (correo del dominio) y App Password.

### Cola (queue)

Para que el envío ocurra en background:
- configurar `QUEUE_CONNECTION` (por ejemplo `database` o `redis`)
- ejecutar worker:
  - `php artisan queue:work --queue=mail`

Si `QUEUE_CONNECTION=sync`, se enviará inmediatamente al llamar “Enviar”.

---

## SPF / DKIM (entrega y reputación)

Cuando se use un correo del dominio (`@tudominio.com`) es clave configurar DNS:

- **SPF**: autoriza a Google a enviar por tu dominio.
- **DKIM**: firma los correos para que no se marquen como falsificados.

Se configura desde el panel de Google Admin + el DNS del dominio.

---

## Desuscripción (unsubscribe)

- En campañas informativas se incluye un enlace de baja.
- Al darse de baja, se registra el email en `email_unsubscribes`.
- Los siguientes envíos omiten ese email con estado `skipped`.

---

## Pendientes sugeridos

- Botón para “Preview” (renderizar en UI con un destinatario de ejemplo).
- Indicador de progreso en campaña (sent/failed/pending).
- Plantillas HTML (opcional) + branding.
- Configuración de correo por empresa (si se vende el sistema multi-tenant).
