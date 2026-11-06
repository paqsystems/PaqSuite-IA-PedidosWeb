# Plan de implementación D1 — TR-22a Plataforma importación maestros ERP

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-10-21 |
| **Parte** | D1 — `ai-planning-mode` |
| **C1** | [F-101-22-cierre-c1](F-101-22-cierre-c1-importacion-maestros-erp.md) — **Apto con observaciones** (sin corrección previa) |
| **TR** | [TR-SPEC-101-22-integracion-maestros-plataforma](TR-SPEC-101-22-integracion-maestros-plataforma.md) |
| **HU / SPEC** | [HU-101-046](../../03-historias-usuario/101-PedidosWeb/HU-101-046-integracion-maestros-plataforma.md) · [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Estado plan** | **Listo para Parte D (22a)** |
| **Olas siguientes** | 22b / 22c — planes D1 propios o extensión de este doc al cerrar 22a |

---

## Alcance entendido

Implementar la **capa transversal** POST `/api/v1/integracion/maestros/{recurso}`:

- Auth Sanctum + permiso `pw_integracion_importacion` (alta) + tenant `X-Paq-Cliente`.
- Validación de lote, reglas pre-handler (snapshot vacío, tope 1500, `modoNoSoportado`, anti-injection superficial).
- Orchestrator + registry + **stub handler** mínimo (smoke CA-01); recursos sin handler real → **404** hasta TR-22b/22c.
- Envelope GEN-20 + i18n `integracion.maestro.*`.
- Log `maestro_import` vía `LogIntegracionService`.
- Rate limit 60/min por user+tenant.
- OpenAPI schemas compartidos (14 recursos documentados).
- Runbook ops usuario técnico + comando provisión (sin auto-deploy).
- **Sin** SP de negocio, **sin** frontend, **sin** transacciones (101-23).

---

## Fuentes leídas

| Tipo | Documento / código |
|------|-------------------|
| SPEC / HU / TR | SPEC-101-22, HU-101-046, TR-22a |
| Producto | [APIs-Importacion-datos-maestros.md §4](../../02-producto/PedidosWeb/APIs-Importacion-datos-maestros.md) |
| Envelope | `App\Http\Responses\ApiResponse`, NORMAS TR §2 |
| Tenant | `ValidatePaqTenant` → **400** + `tenant.invalid` |
| Permisos | `VisibilityPermissionGuard`, patrón `IntegracionLogController` |
| Logs | `LogIntegracionService::registrar` (hoy Eloquent log — excepción TR-101-08 existente) |
| Rutas | `backend/routes/api.php` (grupo `auth:sanctum` + `paq.tenant`) |
| OpenAPI | `PedidosWebOpenApiPaths.php`, regla `openapi-resultado-estructura` |
| Tests ref. | `ImportacionMasivaStoreAuthFeatureTest`, `SeedsPedidosWebFeatureData`, `OpenApiDocumentationTest` |
| Seed MVP | `config/paqsuite_mvp.php`, `SeedSeguridadMvpCommand`, `paqsuite_visibility.php` |

---

## Impacto esperado

### Base de datos

| Cambio | Detalle |
|--------|---------|
| **Sin DDL** maestros | TR-22a explícito |
| Seguridad | Procedimiento `pw_integracion_importacion` en matriz MVP + filas `pq_rol_atributos` vía seed idempotente (**sin** `pq_menus`) |
| Logs | Reuso tabla `pq_pedidosweb_logs_integracion` (insert vía servicio existente) |

### Backend

| Área | Archivos / componentes (nuevos o tocados) |
|------|---------------------------------------------|
| Config | `config/integracion_maestros.php`, `config/paqsuite_visibility.php` (`integracionMaestros` → `pw_integracion_importacion`) |
| Rutas | `routes/api.php` — `POST integracion/maestros/{recurso}` + middleware throttle |
| Rate limit | `RouteServiceProvider::configureRateLimiting` — limiter `integracion-maestros` (60/min by `userId + tenant header`) |
| HTTP | `IntegracionMaestroController` |
| Middleware | `EnsureIntegracionImportacion` (delega a `VisibilityPermissionGuard::ensureAltaPermission`) |
| Request | `IntegracionMaestroLoteRequest` |
| Dominio | `IntegracionMaestroOrchestrator`, `IntegracionMaestroRegistry`, `IntegracionMaestroLoteContext`, `IntegracionMaestroItemResult`, `IntegracionMaestroHandlerInterface` |
| Stub | `IntegracionMaestroStubHandler` — registrado solo para `provincias` en 22a (CA-01); resto de claves **omitidas** del registry → 404 |
| Log | `IntegracionMaestroLogWriter` |
| Anti-injection | `IntegracionMaestroPayloadGuard` (recorre `items` string values) |
| i18n | `lang/es/integracion.php` (+ en/pt/fr/it si el producto exige paridad; mínimo **es** + claves en respuestas) |
| Provider | `PedidosWebServiceProvider` — singleton registry/orchestrator |
| Console | `paqsuite:provision-integracion-user` (crear usuario técnico + token Sanctum documentado) |
| Kernel | Registrar alias middleware si aplica |

**Decisión D1-01:** Reutilizar `VisibilityPermissionGuard` (no duplicar lógica de roles). Middleware traduce `AuthFlowException` a **403** + `integracion.maestro.forbidden` (TR), no `auth.noPermission`.

**Decisión D1-02:** Tenant inválido = comportamiento actual **400** + `tenant.invalid` (C1-M-101-22-06 cerrado).

**Decisión D1-03:** `modoNoSoportado` — orchestrator, tras resolver handler, si `modo === snapshot && !handler->supportsSnapshot()` → abortar lote **422** antes del loop (no invocar SP).

**Decisión D1-04:** Conteo 1500 — en FormRequest/orchestrator sobre `count(items)`; regla escalas (cabecera only) se aplica cuando el handler declare `countsNestedItems(): false` (interface opcional o default true); stub ignora.

**Decisión D1-05:** Códigos `error` envelope — usar rangos NORMAS (1000 validación 422, 3000 auth 401/403, 4000 not found 404).

### Frontend

**Ninguno** (TR-22a).

### Tests

| Capa | Archivo | Escenarios |
|------|---------|------------|
| Feature | `tests/Feature/Api/PedidosWeb/IntegracionMaestroPlatformTest.php` | 401, 403 (vendedor solo `pw_cargapedidos`), tenant inválido 400, delta vacío 200, snapshot vacío 422, recurso 404, >1500 422, envelope shape, snapshot en stub con supportsSnapshot true OK |
| Feature | `IntegracionMaestroModoNoSoportadoTest` (o mismo archivo) | Handler stub `supportsSnapshot=false` temporal en test |
| Unit | `tests/Unit/PedidosWeb/Integracion/IntegracionMaestroOrchestratorTest.php` | contadores, truncado 50 errores |
| Unit | `IntegracionMaestroPayloadGuardTest` | DROP en string rechazado |
| OpenAPI | Extender `OpenApiDocumentationTest` | path template + schemas envelope |
| Seed | Actualizar `SeedSeguridadMvpTest` si se agrega procedimiento a matriz |

Helpers test: rol/usuario con `pw_integracion_importacion` vía `pq_rol_atributos` (patrón importación masiva); `withoutMiddleware(ThrottleRequests::class)` o `RateLimiter::clear`.

### Documentación

| Doc | Acción |
|-----|--------|
| `docs/06-operacion/runbook-usuario-integracion-maestros.md` | **Crear** — token, headers, ejemplo curl, comando provisión |
| Matriz permisos | Fila en doc matriz MVP / TR §5.6 |
| TR-22a §10 checklist | Marcar en Parte D al implementar |

### DevOps

- Deploy: seed seguridad idempotente (`paqsuite:seed-seguridad-mvp`); **no** SP en 22a.
- PR aviso: permiso nuevo; smoke OpenAPI + feature tests.

---

## Orden de trabajo (Parte D — 22a)

1. **T22a-1 Permiso** — `paqsuite_visibility.php` + entrada en `paqsuite_mvp.php` (`procedimientos` / rol técnico sin menú); extender seeder seguridad para registrar procedimiento y asignación a rol `Supervisor` o usuario dedicado de prueba.
2. **T22a-2 Rutas** — Route + `EnsureIntegracionImportacion` + limiter `integracion-maestros`.
3. **T22a-3 Contrato** — `IntegracionMaestroLoteRequest`, DTOs, `IntegracionMaestroPayloadGuard`, lang keys.
4. **T22a-4 Core** — Registry (config), Orchestrator (loop, truncado errores, modoNoSoportado, snapshot vacío), StubHandler + bind `provincias`.
5. **T22a-5 Controller** — `store()` → orchestrator → `ApiResponse::success/error`; log writer post-resultado.
6. **T22a-6 OpenAPI** — Schemas en `OpenApiSchemas.php` + path en `PedidosWebOpenApiPaths.php` + anotaciones controller; regenerar spec en CI local si aplica.
7. **T22a-7 Tests** — Feature + unit + OpenAPI assertion.
8. **T22a-8 Ops** — Artisan `paqsuite:provision-integracion-user` + runbook.

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| LogIntegracionService usa Eloquent (regla 74) | Excepción heredada SPEC-101-08; no refactor en 22a |
| 13 recursos 404 hasta 22b | Documentado; OpenAPI enum 14; ERP debe esperar 22b |
| Anti-injection false positive | Solo keywords DDL + `INSERT INTO pq_`; revisar en D si aparece en QA |
| Tests dependen tablas seguridad | `markTestSkipped` + `ensureSeedSeguridadMvp()` |
| Rate limit flaky CI | Desactivar throttle en tests de plataforma |

---

## Tests a ejecutar (post-D)

```powershell
cd backend
php artisan test tests/Feature/Api/PedidosWeb/IntegracionMaestroPlatformTest.php
php artisan test tests/Unit/PedidosWeb/Integracion/IntegracionMaestroOrchestratorTest.php
php artisan test tests/Feature/OpenApiDocumentationTest.php
php artisan test tests/Feature/SeedSeguridadMvpTest.php
```

---

## Dudas / bloqueos

| ID | Tema | Resolución D1 |
|----|------|----------------|
| D1-01 | ¿Middleware vs solo controller? | Middleware `EnsureIntegracionImportacion` en ruta + controller sin auth duplicada |
| D1-02 | ¿Stub en qué recurso? | `provincias` único handler stub; demás 404 |
| D1-03 | ¿403 message key? | `integracion.maestro.forbidden` (TR) mapeado desde guard |
| D1-04 | ¿Paridad 5 idiomas backend lang? | Mínimo `es`; ampliar en mismo PR si el repo ya exige 5 locales en `lang/` |

**Sin bloqueos** para iniciar Parte D.

---

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR-22a:** **Sí**
- No se implementan handlers/SP de catálogo (22b) ni clientes (22c) en esta ola.
- No se toca frontend, mobile, Excel, transacciones ERP.
- No se ejecuta bootstrap destructivo ni DROP en BD compartida.

---

## Olas posteriores (referencia, no D1 detallado)

| Ola | TR | Entregable principal |
|-----|-----|----------------------|
| 2 | [22b](TR-SPEC-101-22-integracion-maestros-catalogo-articulos.md) | 10 handlers + SP SQL + OpenAPI paths literales |
| 3 | [22c](TR-SPEC-101-22-integracion-maestros-clientes-parametros.md) | 4 handlers + whitelist parámetros + SP |

Tras cerrar **Parte D** de 22a con tests verdes → D1 de 22b con el mismo skill.
