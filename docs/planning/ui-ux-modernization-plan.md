# Plan de Modernizacion UI/UX y Frontend - CRM Atlantis

**Ultima actualizacion:** 30 Mayo 2026  
**Estado:** En ejecucion  
**Objetivo general:** convertir el CRM en una interfaz mas coherente, moderna y mantenible sin reescribir la aplicacion, ejecutando mejoras incrementales sobre shell, sistema visual, modulos densos y estructura frontend.

---

## Resumen ejecutivo

La base tecnica del proyecto es estable: `vue-tsc` y `phpstan` pasan, el build compila y la SPA actual funciona. La mayor deuda no esta en fallos inmediatos, sino en tres frentes:

1. shell SPA con demasiada logica manual en `App.vue`
2. sistema visual poco gobernado por tokens/componentes base
3. modulos operativos con densidad alta y jerarquia visual inconsistente

La estrategia acordada es documentar una hoja de ruta ejecutable y avanzar por iteraciones pequenas con validacion real (`npm run typecheck` + `npm run build`).

---

## Diagnostico consolidado

### Fortalezas actuales

- stack estable: Laravel + Vue 3 + Vite + Tailwind
- shell SPA funcional con modulos reales de negocio
- componentes base reutilizables ya presentes
- backend razonablemente ordenado por dominio
- build y typecheck limpios

### Problemas principales

- `resources/js/components/App.vue` concentra demasiada logica de ruteo/acciones del shell
- `resources/css/app.css` estaba demasiado vacio para sostener una identidad consistente
- `resources/js/utils/designSystem.js` existia, pero gobernaba poco la UI real
- modulos como inbox, postventa, scrum y settings mezclan buena funcionalidad con jerarquia visual irregular
- la experiencia movil del shell no estaba cerrada correctamente

---

## Direccion visual objetivo

### Lenguaje recomendado

- estilo enterprise calmado
- superficies limpias, respiradas y con contraste alto
- sidebar oscura como ancla de marca
- azul como primario operativo y verde como acento de estado positivo
- tipografia de interfaz mas tecnica y legible para datos densos
- motion sutil: 180-220ms, sin animaciones decorativas continuas

### Principios de ejecucion

1. Accesibilidad primero.
2. Claridad operativa antes que decoracion.
3. Reutilizacion base antes que “arreglos pantalla por pantalla”.
4. Validacion ejecutable despues de cada iteracion.

---

## Estado por fase

| Fase | Estado | Resultado actual | Pendiente clave |
| --- | --- | --- | --- |
| 1. Shell y base visual | ✅ Completada | sidebar movil real, overlay, ARIA mejorado, tokens y tipografia globales | seguir consumiendo el sistema visual en pantallas |
| 2. Tablas y primitivas base | ✅ Completada | `GenericTable` y `TableColumnsDropdown` con mejor jerarquia; `UsersTable` alineado | propagar el patron a mas vistas |
| 3. Settings y superficies secundarias | ✅ Completada | configuracion y notificaciones con lenguaje enterprise | extender el mismo tono a mas modulos |
| 4. Inbox real | 🟡 En progreso | superficie superior, plantillas y accion masiva ya alineadas; quedan diferencias menores entre tabs | terminar de converger diagnosticos y detalles secundarios |
| 5. Dashboard y postventa | ✅ Completada | dashboard superior y postventa principal ya alineados al nuevo lenguaje | mantener consistencia en vistas relacionadas |
| 6. Estructura SPA | 🟡 En progreso | `App.vue` ya usa un registro incremental de vistas para componente, titulo y subtitulo | seguir desacoplando acciones/eventos globales cuando convenga |

---

## Trabajo ya ejecutado

### 1. Shell y navegacion

**Archivos tocados**

- `resources/js/components/App.vue`
- `resources/js/components/layout/Header.vue`
- `resources/js/components/layout/Sidebar.vue`

**Cambios aplicados**

- sidebar movil con estado Vue real
- overlay y bloqueo de scroll del body
- mejora de `aria-expanded` y `aria-current`
- separacion correcta entre colapso desktop y apertura movil

### 2. Sistema visual global

**Archivo tocado**

- `resources/css/app.css`

**Cambios aplicados**

- tipografia base
- tokens globales de color, sombras y foco
- scrollbars y seleccion de texto
- `prefers-reduced-motion`

### 3. Tablas y componentes base

**Archivos tocados**

- `resources/js/components/base/GenericTable.vue`
- `resources/js/components/base/TableColumnsDropdown.vue`
- `resources/js/components/UsersTable.vue`

**Cambios aplicados**

- toolbar mas clara
- empty state con mejor narrativa
- paginacion mas ordenada
- selector de columnas mas consistente
- consumidor alineado con el patron base

### 4. Settings

**Archivos tocados**

- `resources/js/components/settings/SettingsView.vue`
- `resources/js/components/settings/NotificationSettings.vue`

**Cambios aplicados**

- tono mas enterprise
- eliminacion de señales visuales informales
- mejor cabecera, cards y agrupacion

### 5. Inbox real

**Archivo tocado**

- `resources/js/components/inbox/InvoiceDispatchInbox.vue`

**Cambios aplicados**

- nueva cabecera de modulo
- KPIs mas claros
- separacion entre acciones y filtros
- resumen de seleccion
- tabs con mejor jerarquia visual

### 6. Dashboard

**Archivo tocado**

- `resources/js/components/DashboardView.vue`

**Cambios aplicados**

- nueva cabecera superior con narrativa de modulo
- filtro de periodo integrado como bloque operativo
- KPIs con mejor jerarquia visual y lectura rapida
- alineacion del tono visual con inbox y settings

### 7. Postventa clientes

**Archivo tocado**

- `resources/js/components/PostventaCustomersTable.vue`

**Cambios aplicados**

- nueva cabecera operativa con metricas de cartera
- toolbar de acciones y busqueda con mejor jerarquia
- filtros avanzados reorganizados como bloque legible
- estado vacio y paginacion alineados al nuevo sistema visual

### 8. Scrum

**Archivo tocado**

- `resources/js/components/ScrumTasksView.vue`

**Cambios aplicados**

- nueva cabecera de seguimiento diario
- CTA visible para crear tareas
- filtros y rango temporal mas claros
- columnas kanban y vista lista mas consistentes con el lenguaje actual

### 9. Leads secundarios

**Archivos tocados**

- `resources/js/components/leads/WaitingLeadsList.vue`
- `resources/js/components/leads/LostLeadsList.vue`

**Cambios aplicados**

- cabeceras operativas para espera y desistidos
- metricas rapidas y control de paginacion mejor integrado
- tablas con estados vacios mas claros
- consistencia visual con postventa, dashboard e inbox

### 10. Inbox interno

**Archivo tocado**

- `resources/js/components/inbox/InvoiceDispatchInbox.vue`

**Cambios aplicados**

- pestaña de plantillas reorganizada como flujo de trabajo
- preview y alcance de aplicacion separados del editor
- accion masiva de envios elevada como bloque operativo
- mejor convergencia visual con el preview UX

### 11. Estructura SPA incremental

**Archivo tocado**

- `resources/js/components/App.vue`

**Cambios aplicados**

- registro incremental de vistas estaticas
- resolucion unificada de componente, titulo y subtitulo
- reduccion de condicionales duplicadas en render y metadata
- base lista para seguir desacoplando el shell sin introducir router completo de golpe

---

## Orden de ejecucion recomendado

### Iteracion 1. Cerrar superficies principales

1. **Cierre visual opcional**
   - segunda pasada de dashboard o inbox solo si la revision manual detecta fricciones reales

2. **Shell incremental**
   - desacoplar acciones globales de `App.vue` cuando haya retorno claro

3. **QA de producto**
   - revisar flujos clave en navegador con datos reales y contrastar con el preview UX

### Iteracion 2. Llevar inbox hacia el preview

1. reorganizar bloques internos por flujo natural
2. acercar plantilla, diagnostico y accion masiva al concepto del preview
3. reducir cambio de contexto entre tabs

### Iteracion 3. Consolidar arquitectura frontend

1. reducir condicionales de `App.vue`
2. introducir un registro de vistas o un router incremental
3. desacoplar eventos globales donde sea viable

---

## Criterio de terminado por fase

### Una fase UI se considera lista cuando:

- mejora una superficie real del producto
- mantiene logica intacta o reduce riesgo
- pasa `npm run typecheck`
- pasa `npm run build`
- deja un patron reutilizable para la siguiente vista

---

## Registro de validacion

### Validaciones utilizadas en esta linea de trabajo

- `npm run typecheck`
- `npm run build`
- `composer analyse --no-interaction`

### Estado actual

- typecheck frontend: OK
- build frontend: OK
- analisis estatico backend: OK

---

## Siguiente paso a ejecutar

**Paso activo:** cerrar validacion frontend y dejar la linea de modernizacion lista para QA visual con datos reales.

**Hipotesis local:** la mayor parte del valor ya esta capturada; una ultima verificacion ejecutable y una revision manual deberian ser suficientes para decidir si hace falta una segunda pasada menor o si el plan puede darse por cerrado.