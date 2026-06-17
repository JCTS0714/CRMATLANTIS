# Plan de Simplificacion del Proyecto - CRM Atlantis

**Ultima actualizacion:** 29 Mayo 2026  
**Estado:** En ejecucion  
**Objetivo general:** reducir complejidad accidental, limpiar artefactos no fuente, endurecer validaciones automatizadas y dejar una base ordenada para refactors posteriores sin romper el flujo actual de despliegue.

---

## Resumen ejecutivo

El plan ya avanzo bien en higiene de repositorio, validaciones base y CI. La siguiente etapa no es seguir agregando herramientas, sino cerrar deuda estructural visible en frontend/backend y cubrir con tests los flujos de negocio que hoy sostienen la operacion.

### Estado por fase

| Fase | Estado | Resultado actual | Pendiente clave |
| --- | --- | --- | --- |
| 1. Higiene del repositorio | ✅ Completada | `.gitignore` ampliado y artefactos locales removidos | Mantener disciplina, sin trabajo extra inmediato |
| 2. Automatizacion de calidad minima | 🟡 Parcial | Existen comandos repetibles para sintaxis, tests, typecheck y build | Agregar analisis estatico PHP conservador y cerrar deuda de formato |
| 3. CI basica util | 🟡 Parcial | GitHub Actions ejecuta sintaxis, typecheck, build, tests, migracion y `permissions:sync` | Separar checks por jobs y endurecer gating cuando el lint deje de fallar por deuda historica |
| 4. Orden estructural incremental | 🟡 En progreso | Backend avanzado por dominios; frontend aun mezcla componentes grandes y reutilizables | Consolidar estructura modular y extraer logica repetida |
| 5. Cobertura de dominio | 🔴 Pendiente | Hay validaciones tecnicas, pero falta cobertura funcional suficiente | Priorizar permisos, leads, customers, importadores e integraciones criticas |

---

## Restricciones y reglas de trabajo

- `public/build/` se versiona porque el despliegue actual depende de assets compilados en local.
- `phpunit.xml` usa SQLite en memoria; los tests no deben depender de SQL especifico de MySQL.
- Existen cambios locales fuera de este plan; no se tocan durante la simplificacion.
- El objetivo es simplificar sin introducir una reescritura total: se priorizan cambios incrementales, seguros y con beneficio visible.

---

## Alcance del plan

Este plan cubre cuatro superficies del proyecto:

1. **Repositorio y operacion diaria**: basura local, scripts repetibles y comandos estables.
2. **Calidad automatizada**: sintaxis, pruebas, typecheck, build y analisis estatico conservador.
3. **Estructura de codigo**: orden por dominio en backend y frontend.
4. **Cobertura funcional**: proteger con tests los flujos que mas cuestan cuando se rompen.

No cubre una migracion completa de arquitectura, una SPA nueva con router/SSR, ni un reemplazo del proceso actual de deploy.

---

## Fase 1. Higiene del repositorio

**Estado:** ✅ Completada

### Acciones ejecutadas

- Ampliacion de `.gitignore` para artefactos locales, backups, binarios y salidas de diagnostico.
- Eliminacion de archivos de debug/prueba sueltos en raiz.
- Eliminacion de binarios, backups y residuos de ejecucion local como `__pycache__`.

### Resultado esperado

- La raiz queda enfocada en codigo y configuracion.
- Los artefactos basura dejan de aparecer como nuevos archivos.

### Mantenimiento

- Mantener indices locales, backups y scratch files fuera del repo.
- Si vuelve a aparecer una clase nueva de artefacto, agregar la regla de ignore en el mismo cambio que lo detecta.

---

## Fase 2. Automatizacion de calidad minima

**Estado:** 🟡 Parcial

### Lo ya resuelto

- Existen comandos estables en `composer.json` y `package.json` para:
  - `composer syntax`
  - `composer test`
  - `composer quality`
  - `npm run typecheck`
  - `npm run build`
  - `npm run check`
- Se agrego chequeo de sintaxis PHP para bloquear parse errors sin depender del lint global.

### Lo pendiente para cerrar la fase

1. **Analisis estatico PHP conservador**
   - Incorporar una configuracion inicial que ataque errores reales de tipos, firmas y nullability sin abrir cientos de falsos positivos.
   - El objetivo no es “dejar todo perfecto”, sino tener un baseline util y ejecutable en CI.

2. **Cierre progresivo de deuda de formato**
   - `composer run lint` existe, pero aun falla por deuda historica amplia.
   - No debe usarse como gate global hasta que el backend legacy mas sensible quede normalizado.

3. **Comando local recomendado**
   - Mantener `composer quality` como verificacion corta de rutina.
   - Cuando se incorpore analisis estatico, sumarlo ahi si el costo sigue siendo razonable para uso local.

### Criterio de salida

- El proyecto valida sintaxis, pruebas, frontend y analisis estatico basico con comandos repetibles.
- El equipo tiene un chequeo local corto y confiable antes de push.

---

## Fase 3. CI basica util

**Estado:** 🟡 Parcial

### Lo ya resuelto

- GitHub Actions ejecuta:
  - instalacion de dependencias
  - chequeo de sintaxis PHP
  - typecheck frontend
  - build frontend
  - tests
  - migracion sobre SQLite
  - `php artisan permissions:sync`
- Se preservo `permissions:sync` como chequeo especifico del dominio RBAC.

### Mejoras pendientes

1. **Separar jobs por responsabilidad**
   - `quality-php`
   - `quality-frontend`
   - `permissions-sync`
   - Esto reduce tiempo de feedback y aisla fallas.

2. **Endurecer reglas de merge**
   - Convertir lint PHP en obligatorio cuando la deuda historica ya no lo haga inutil.
   - Mantener build y tests como gates minimos de PR.

3. **Optimizar tiempos**
   - Revisar caches de Composer/npm y artefactos si el pipeline empieza a crecer.

### Criterio de salida

- Un PR detecta roturas basicas antes de merge.
- Los checks fallan por problemas accionables, no por ruido sistematico.

---

## Fase 4. Orden estructural incremental

**Estado:** 🟡 En progreso

La simplificacion estructural debe continuar sin “big bang”. La referencia no es mover carpetas por moverlas, sino bajar acoplamiento y hacer mas predecible el codigo.

### Backend

**Avance actual**

- Ya existe un camino de reorganizacion por dominio en controllers y servicios.
- Se mejoraron DTOs, FormRequests, modelos, middleware, providers, repositories y varios controllers seguros.

**Pendientes prioritarios**

1. Reagrupar lo restante por dominio (`Leads`, `Customers`, `Integrations`, `Calendar`, `Campaigns`, `Inbox` si aplica).
2. Mantener controladores delgados y mover orquestacion a servicios/actions solo cuando la extraccion reduzca complejidad real.
3. Extraer consultas repetidas a scopes, query objects o repositories donde ya exista reuse claro.
4. Reducir zonas de integraciones con cambios locales o estructura mixta sin tocar codigo ajeno en curso.

**Definicion de listo en backend**

- El modulo queda ubicable por dominio sin recorrer toda la aplicacion.
- La validacion vive en `FormRequest` cuando corresponde.
- Las consultas repetidas no siguen duplicadas entre controllers.

### Frontend

**Avance actual**

- El frontend compila y tiene typecheck operativo.
- Sigue existiendo mezcla de componentes de modulo con reutilizables dentro de `resources/js/components`.
- Vite reporta imports duplicados estatico/dinamico en componentes cargados tambien por `DynamicModuleHost.vue`.

**Pendientes prioritarios**

1. Reorganizar `resources/js/components` por modulo y mover compartidos a `shared/`.
2. Extraer logica repetida de tablas, filtros, paginacion, carga y modales a composables/componentes base.
3. Reducir componentes gigantes separando vista, estado y adaptadores HTTP.
4. Resolver los imports duplicados dinamicos/estaticos para bajar ruido del build y evitar ambiguedad de carga.

**Definicion de listo en frontend**

- Los componentes nuevos se ubican por modulo desde el inicio.
- La reutilizacion comun vive en `shared/` o `composables/`.
- Los componentes mas grandes dejan de concentrar estado, vista y networking en un solo archivo.

---

## Fase 5. Cobertura de dominio

**Estado:** 🔴 Pendiente

La cobertura debe proteger primero aquello que rompe operacion, permisos o datos. La meta inicial no es porcentaje, sino blindaje de flujos.

### Prioridad de tests

1. Permisos y acceso a modulos.
2. Flujo de leads y cambio de etapa.
3. Creacion automatica de customers desde leads ganados.
4. Importadores CSV.
5. Integraciones y campanas criticas.

### Orden recomendado de implementacion

1. **Permisos/RBAC**
   - rutas protegidas
   - visibilidad de modulo
   - `permissions:sync`

2. **Leads**
   - board/table data
   - cambio de etapa
   - creacion de customer al ganar

3. **Importacion**
   - parseo tolerante
   - columnas opcionales/reales
   - reglas de negocio de estado y periodo

4. **Integraciones criticas**
   - lo que hoy impacta campañas, bandejas o sincronizaciones reales

### Criterio de salida

- Los flujos criticos del negocio tienen pruebas de regresion.
- Un cambio estructural ya no depende solo de revision manual.

---

## Hoja de ruta recomendada

### Iteracion 1. Cerrar ruido estructural visible

- Resolver imports duplicados relacionados con `DynamicModuleHost.vue`.
- Reordenar frontend por modulo/shared en las areas mas activas.
- Documentar la nueva convension de ubicacion cuando el movimiento quede estable.

### Iteracion 2. Blindar negocio central

- Crear tests de permisos, leads ganados y customers generados automaticamente.
- Cubrir importadores CSV con datasets representativos.

### Iteracion 3. Endurecer calidad sin ruido

- Incorporar analisis estatico PHP conservador.
- Rehabilitar gradualmente lint como gate real.
- Separar jobs de CI si el pipeline ya lo justifica.

---

## Metricas de exito

- Menos archivos basura/versionables fuera de codigo fuente.
- Un set de comandos de calidad que cualquier desarrollador puede correr sin pasos manuales raros.
- CI que falla por regresiones reales y no por deuda conocida sin plan.
- Menos componentes/controladores gigantes.
- Tests cubriendo los flujos donde mas duele una regresion.

---

## Tareas ejecutadas en esta etapa

- [x] Reglas de ignore ampliadas.
- [x] Artefactos basura eliminados del repo.
- [x] Scripts de calidad agregados.
- [x] CI extendida con tests, typecheck, build frontend y chequeos de migracion/permisos.
- [x] Chequeo de sintaxis PHP agregado para bloquear parse errors sin depender del lint global.
- [x] Seeders saneados: parse error eliminado y formato corregido.
- [x] Migraciones y rutas seguras formateadas sin tocar archivos con cambios locales previos.
- [x] DTOs y FormRequests formateados y revalidados con sintaxis y tests.
- [x] Models formateados y revalidados con sintaxis y tests.
- [x] Consola, middleware, jobs, mail, notifications, providers, repositories, servicios seguros y scripts PHP de soporte formateados y revalidados.
- [x] Controllers seguros formateados y revalidados, excluyendo solo integraciones con cambios locales previos.

---

## Deuda detectada durante la ejecucion

- Se elimino `database/seeders/PermissionModuleDemosSeeder.php` porque era una plantilla rota no referenciada que introducia un parse error evitable.
- `composer run lint` sigue fallando por deuda historica de formato en parte del backend legacy.
- `npm run build` compila correctamente, pero Vite reporta imports duplicados estatico/dinamico en componentes cargados tambien por `DynamicModuleHost.vue`.

---

## Backlog recomendado despues de cerrar este plan

- Mover indices FAISS a `storage/app/ai/` o a un workspace separado y parametrizar su ruta.
- Separar `scripts/` en `deploy/`, `diagnostics/`, `docs/` y `setup/`.
- Limpiar `README.md` para dejar solo onboarding y operacion real del CRM.

---

## Definicion de cierre del plan

Este plan puede considerarse cerrado cuando se cumplan estas condiciones:

1. La fase 2 tenga analisis estatico PHP basico operativo y `composer quality` estable.
2. La fase 3 tenga CI util como gate real de PR, sin checks ruidosos.
3. La fase 4 deje una estructura modular clara al menos en las areas activas del proyecto.
4. La fase 5 cubra con tests los flujos de permisos, leads, customers e importacion.