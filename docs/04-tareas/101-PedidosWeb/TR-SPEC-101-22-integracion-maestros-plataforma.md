# TR-SPEC-101-22a — Plataforma API importación maestros ERP

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-046](../../03-historias-usuario/101-PedidosWeb/HU-101-046-integracion-maestros-plataforma.md) |
| **SPEC relacionada** | [SPEC-101-22-importacion-maestros-erp](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Épica** | APIs importación maestros (PIAPEDWEB-34) |
| **Prioridad** | **Must** |
| **Dependencias** | TR-SPEC-101-06 (policies); SPEC-101-08 / `LogIntegracionService`; GEN-19 `paq.tenant`; GEN-20 `ApiResponse` |
| **Estado** | **Parte D implementada** — pendiente E / F1 |
| **C1** | [F-101-22-cierre-c1](F-101-22-cierre-c1-importacion-maestros-erp.md) (2026-10-21) |
| **D1** | [D1-PLAN-101-22-integracion-maestros-plataforma](D1-PLAN-101-22-integracion-maestros-plataforma.md) (2026-10-21) |
| **Última actualización** | 2026-10-21 |

**Origen:** HU-101-046 · Producto §4 [APIs-Importacion-datos-maestros.md](../../02-producto/PedidosWeb/APIs-Importacion-datos-maestros.md)

---

## 1) HU refinada (resumen)

### Título
Capa transversal POST `/api/v1/integracion/maestros/{recurso}`: auth integración, validación de lote, envelope, logs y OpenAPI común.

### In scope / Out of scope

- **In:** permiso `pw_integracion_importacion`; middleware; controller único + registry de handlers; DTO lote/resultado; extensión logs; rate limit; OpenAPI schemas compartidos; feature tests transversales; runbook usuario técnico.
- **Out:** SP por tabla (TR-22b/22c); transacciones (101-23); frontend.

---

## 2) Criterios de aceptación (AC)

Heredados HU-101-046 (CA-01 … CA-14). Verificación mínima en D1:

| AC | Verificación |
|----|----------------|
| CA-01 | Feature: token integración POST recurso registrado (stub handler OK) |
| CA-02 | Feature: vendedor con `pw_cargapedidos` → 403 |
| CA-03 | Feature: tenant inválido → error sin filas nuevas en maestros |
| CA-04…CA-10 | Feature: envelope, delta vacío, snapshot vacío, 404, tope 1500, truncado errores |
| CA-11 | Unit/feature: log `maestro_import` tras POST |
| CA-12 | Doc/config rate limit |
| CA-13 | Suite `IntegracionMaestroPlatformTest` |
| CA-14 | OpenAPI tag + schemas en `/api/documentation` |

---

## 3) Reglas de negocio (implementación)

| RN HU | Implementación |
|-------|----------------|
| RN-01 | Middleware `EnsureIntegracionImportacion` o policy `IntegracionMaestroPolicy::import` → ability `pw_integracion_importacion` |
| RN-02 | Grupo rutas bajo `paq.tenant` existente |
| RN-03…RN-04 | `IntegracionMaestroLoteRequest` FormRequest |
| RN-05 | `ApiResponse::error` con claves `integracion.maestro.*` (lang `backend/lang/*/integracion.php`) |
| RN-06 | Validador anti-injection: rechazar strings que matcheen `/\b(DROP|TRUNCATE|ALTER|INSERT INTO pq_)\b/i` en valores string del JSON (defensa en profundidad) |
| RN-07 | Ignorar header `Idempotency-Key` |
| RN-08 | `count($items) > 1500` antes de handler (escalas: contar solo ítems cabecera) |
| RN-09 | `IntegracionMaestroLogWriter` → `LogIntegracionService::registrar('maestro_import', …)` |
| RN-10 | `auth:sanctum` + gate integración |

---

## 4) Impacto en datos

- **Sin DDL** de maestros en esta TR.
- **Seguridad:** seed idempotente ability `pw_integracion_importacion` en matriz MVP (`config/paqsuite_mvp.php` + seeder seguridad existente); **sin** fila en `pq_menus`.
- Usuario técnico: documentar en `docs/06-operacion/runbook-usuario-integracion-maestros.md` (crear en D1) — comando artisan `paqsuite:provision-integracion-user` (nombre acordado) o script SQL manual; **no** ejecutar en deploy automático.

---

## 5) Contratos API y OpenAPI

### 5.1 Rutas

Registrar en `backend/routes/api.php` dentro de `auth:sanctum` + `paq.tenant`:

```text
POST /api/v1/integracion/maestros/{recurso}
```

Middleware adicional: `throttle:integracion-maestros` (60/min por user+tenant).

`{recurso}` = clave del registry (`config/integracion_maestros.php` → `resources[]`).

### 5.2 Request body

```json
{
  "modo": "delta",
  "fechaProceso": "2026-10-21T12:00:00-03:00",
  "origen": "tango",
  "items": []
}
```

| Campo | Reglas |
|-------|--------|
| `modo` | required, in:`delta`,`snapshot` |
| `fechaProceso` | required, date ISO8601 |
| `origen` | optional, string max 50 |
| `items` | required array; max 1500 elementos (regla escalas en handler) |

Headers: `Authorization`, `X-Paq-Cliente`, `Content-Type: application/json`.

### 5.3 Response 200 (lote procesado)

Envelope GEN-20; `resultado`:

```json
{
  "recurso": "articulos",
  "modo": "delta",
  "recibidos": 3,
  "aplicados": 2,
  "omitidos": 0,
  "rechazados": 1,
  "errores": [{ "indice": 1, "clave": "X", "codigo": "integracion.maestro.itemInvalido" }]
}
```

Si `errores` > 50: devolver 50 + `respuesta` = `integracion.maestro.erroresTruncados`.

### 5.4 Errores HTTP

| Caso | HTTP | `respuesta` (ej.) |
|------|------|-------------------|
| Sin token | 401 | `auth.unauthenticated` |
| Sin permiso integración | 403 | `integracion.maestro.forbidden` |
| Tenant inválido | 400/404 | según `ValidatePaqTenant` |
| JSON inválido | 422 | `integracion.maestro.jsonInvalido` |
| `modo` inválido (no delta/snapshot) | 422 | `integracion.maestro.modoInvalido` |
| `modo=snapshot` en recurso con `supportsSnapshot=false` | 422 | `integracion.maestro.modoNoSoportado` |
| Recurso desconocido | 404 | `integracion.maestro.recursoDesconocido` |
| > 1500 ítems | 422 | `integracion.maestro.loteDemasiadoGrande` |
| Snapshot + items vacío | 422 | `integracion.maestro.snapshotVacio` |
| Fallo fatal pre-proceso | 500 | clave i18n; sin stack |

### 5.5 OpenAPI

- Tag: **Integración maestros**
- Schemas: `IntegracionMaestroLoteRequest`, `IntegracionMaestroItemError`, `IntegracionMaestroLoteResultado`, `ApiEnvelopeIntegracionMaestroLote`
- Path template con `{recurso}` enum de 14 valores (documentación); runtime valida registry
- `security`: Bearer + header `X-Paq-Cliente`
- Anotaciones en `IntegracionMaestroController` + `PedidosWebOpenApiPaths.php`

### 5.6 Matriz permisos

| Método | Path | Permiso |
|--------|------|---------|
| POST | `/api/v1/integracion/maestros/{recurso}` | `pw_integracion_importacion` |

---

## 6) Cambios backend (clases)

| Componente | Rol |
|------------|-----|
| `IntegracionMaestroController` | `store(string $recurso, IntegracionMaestroLoteRequest $request)` |
| `IntegracionMaestroOrchestrator` | Resuelve handler, loop ítems, agrega contadores |
| `IntegracionMaestroRegistry` | `recurso` → `IntegracionMaestroHandlerInterface` |
| `IntegracionMaestroHandlerInterface` | `supportsSnapshot(): bool`, `processItem(...)` |
| `IntegracionMaestroLoteContext` | modo, fechaProceso, origen, recurso |
| `IntegracionMaestroItemResult` | applied / omitted / rejected + error code |
| `IntegracionMaestroLogWriter` | severidad info/warning/error |
| `IntegracionMaestroLoteRequest` | validación cuerpo |
| `EnsureIntegracionImportacion` | middleware |
| `config/integracion_maestros.php` | mapa recursos → handler class + `supportsSnapshot` |

Handlers concretos se registran en TR-22b/22c; en D1 de 22a puede existir `IntegracionMaestroStubHandler` para tests.

Provider: bind registry en `PedidosWebServiceProvider`.

---

## 7) Plan de tareas

| ID | Descripción | DoD |
|----|-------------|-----|
| T22a-1 | Permiso + seed + config visibility | Matriz actualizada |
| T22a-2 | Middleware + routes + throttle | CA-02 |
| T22a-3 | Request + orchestrator + envelope | CA-04…CA-10 |
| T22a-4 | Log writer | CA-11 |
| T22a-5 | OpenAPI | CA-14 |
| T22a-6 | Feature tests platform | CA-13 |
| T22a-7 | Runbook usuario técnico | ops |

---

## 8) Estrategia de tests

- **Feature** `tests/Feature/Api/PedidosWeb/IntegracionMaestroPlatformTest.php`: 401, 403, delta vacío 200, snapshot vacío 422, recurso bad 404, envelope shape.
- **Unit** `IntegracionMaestroOrchestratorTest`: agregación contadores; truncado 50 errores.
- Usar usuario seed con permiso integración (factory o helper test).

---

## 9) Riesgos

- Handlers no registrados → 404 hasta TR-22b/22c.
- Rate limit en tests: usar `withoutMiddleware(ThrottleRequests::class)` o `RateLimiter::clear`.

---

## 10) Checklist D1 / D

- [x] Rutas + middleware + permiso seed (`pw_integracion_importacion`, user `integracion.erp.mvp`)
- [x] OpenAPI verificado (`ApiEnvelopeIntegracionMaestroLote`)
- [x] Feature tests (`IntegracionMaestroPlatformTest`; requieren SQL Server + seeds)
- [x] Sin Eloquent de negocio en controller (stub handlers; SP en 22b/22c)
- [x] Runbook [runbook-usuario-integracion-maestros.md](../../06-operacion/runbook-usuario-integracion-maestros.md)
