<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## CRM ATLANTIS

CRM interno (Laravel 12 + Breeze + Vue 3 + Vite + Tailwind v4 + Flowbite) con RBAC (spatie/laravel-permission) y módulos iniciales: Usuarios, Roles/Permisos y Leads (Kanban).

### Estado actual (implementado)

**Stack**
- Backend: Laravel 12, PHP 8.2 (XAMPP)
- Frontend: Vue 3 + Vite 7
- UI: Tailwind CSS v4 + Flowbite
- Auth: Laravel Breeze (registro deshabilitado)
- RBAC: spatie/laravel-permission

**Arquitectura**
- Laravel sirve una vista Blade como “shell” del dashboard: `resources/views/dashboard.blade.php`.
- Vue se monta en `#app` (`resources/js/app.js`) y decide el módulo a renderizar por `window.location.pathname` (`resources/js/components/App.vue`).
- Los datos se consumen vía endpoints JSON en rutas web (mismo dominio) protegidas por `auth` + permisos.

### Instalación local (Windows + XAMPP)

1) Instalar dependencias
- `composer install`
- `npm install`

2) Configurar `.env`
- DB: MySQL (XAMPP) y base `crmatlantis` (o la que uses).

3) Migrar + seed
- `php artisan migrate --seed`

Esto ejecuta (por `DatabaseSeeder`):
- `RolesAndPermissionsSeeder` (roles/permisos base)
- `LeadStagesSeeder` (etapas del kanban)
- `InternalUserSeeder` (usuario admin interno)

**Credenciales admin (dev/local)**
- Email: `admin@crmatlantis.local`
- Password: `Atlantis2025!`

### RBAC (roles, permisos y menú)

**Middleware Spatie (Laravel 12)**
- En Laravel 12 se registran aliases en `bootstrap/app.php`:
	- `permission`, `role`, `role_or_permission`

**Permisos base**
- `users.view|create|update|delete`
- `roles.view|create|update|delete`
- `leads.view|create|update|delete`

**Permisos de menú (visibilidad Sidebar)**
- `menu.users`, `menu.roles`, `menu.leads`

**Roles base**
- `admin`: todos los permisos
- `dev`: todos los permisos
- `employee`: sin permisos (rol “base” para empezar y asignar granularmente)

> Nota: la visibilidad en el sidebar depende de `menu.*`, además de que las rutas están protegidas por `leads.view`, `users.view`, etc.

### Módulos

#### Usuarios

**UI**: `resources/js/components/UsersTable.vue`
- Tabla con búsqueda (debounce), paginación server-side y modales animados.
- Select de rol dinámico: consume `GET /users/role-options` (para mostrar roles reales existentes).

**Endpoints**
- `GET /users` (vista shell; requiere `users.view`)
- `GET /users/data` (JSON; requiere `users.view`)
- `GET /users/role-options` (JSON; requiere `users.view`)
- `POST /users` (requiere `users.create`)
- `PUT /users/{user}` (requiere `users.update`)
- `DELETE /users/{user}` (requiere `users.delete`)

#### Roles / Permisos

**UI**: `resources/js/components/RolesTable.vue`
- CRUD de roles.
- Asignación de permisos por checkbox.
- Sección “Módulos del menú” que controla permisos `menu.*`.

**Endpoints**
- `GET /roles` (vista shell; requiere `roles.view`)
- `GET /roles/data` (JSON; requiere `roles.view`)
- `GET /roles/permissions` (JSON; requiere `roles.view`)
- `POST /roles` (requiere `roles.create`)
- `PUT /roles/{role}` (requiere `roles.update`)
- `DELETE /roles/{role}` (requiere `roles.delete`)

#### Leads (Kanban)

**UI**: `resources/js/components/LeadsBoard.vue`
- Kanban por etapas (`lead_stages`).
- Drag & drop con actualización “optimista” (sin recargar el tablero, para evitar parpadeos).
- Modal “Lead rápido” inspirado en Kommo (adaptado a los campos actuales).

**Endpoints**
- `GET /leads` (vista shell; requiere `leads.view`)
- `GET /leads/board-data` (JSON; requiere `leads.view`)
- `POST /leads` (requiere `leads.create`)
- `PATCH /leads/{lead}/move-stage` (requiere `leads.update`)

**Modelo de datos (actual)**
- `lead_stages`: columnas del kanban (`key`, `name`, `sort_order`, `is_won`).
- `leads`: oportunidad/lead. `document_type` + `document_number` son opcionales.
- `customers`: entidad separada. Al mover un lead a una etapa `is_won=true`, se crea/relaciona un customer automáticamente (usando documento si existe).

### Notas de trabajo
- 2025-12-25: `docs/2025-12-25-notas.md`
- 2025-12-26: `docs/2025-12-26-notas.md`

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
