# TR-SPEC-101-25a — Plataforma GET exportación comprobantes ERP

Jira: [PIAPEDWEB-58](https://paqsystems.atlassian.net/browse/PIAPEDWEB-58)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-057](../../03-historias-usuario/101-PedidosWeb/HU-101-057-integracion-export-comprobantes-plataforma.md) |
| **SPEC relacionada** | [SPEC-101-25](../../05-open-spec/101-PedidosWeb/SPEC-101-25-exportacion-comprobantes-erp.md) |
| **Épica** | [PIAPEDWEB-54](https://paqsystems.atlassian.net/browse/PIAPEDWEB-54) |
| **Prioridad** | **Must** |
| **Dependencias** | GEN-19 `paq.tenant` · GEN-20 `ApiResponse` · middleware `integracion.importacion` · [SPEC-101-08](../../05-open-spec/101-PedidosWeb/SPEC-101-08-logs-integracion.md) |
| **Estado** | **Finalizado** (2026-11-26) |
| **Plan D1** | [D1-PLAN-101-25](D1-PLAN-101-25-exportacion-comprobantes-erp.md) § Fase 1 |
| **Siguiente TR datos** | [TR-SPEC-101-25-integracion-export-datos](TR-SPEC-101-25-integracion-export-datos.md) (25b) |
| **Última actualización** | 2026-11-26 |

**Origen:** HU-101-057 · SPEC-101-25 · [APIs-Exportacion-comprobantes-erp.md](../../02-producto/PedidosWeb/APIs-Exportacion-comprobantes-erp.md)

---

## 1) Objetivo y alcance

Implementar la capa HTTP de:

```http
GET /api/v1/integracion/comprobantes
```

Respuesta **200** síncrona con envelope GEN-20; **sin** GEN-36.

### Incluido

- Ruta GET en grupo autenticado + `paq.tenant`.
- Middleware existente `integracion.importacion` (`EnsureIntegracionImportacion` → `pw_integracion_importacion`).
- `FormRequest` / validador de query (`estadoRangos`, paginación, filtros opcionales).
- Parser de `estadoRangos` (repetido + CSV → lista unificada de pares).
- `IntegracionExportComprobantesController` + `ExportComprobantesService` (delega en repositorio SP de 25b).
- Cálculo de `totalPages`, `metadata.fechaProceso`.
- Log **Should** `export_comprobantes`.
- Rate limit 60/min por user+tenant.
- i18n claves `integracion.export.*` en `backend/lang/*/integracion.php` (o archivo dedicado importado).
- OpenAPI tag **Integración exportación** + schemas envelope/resultado.
- Feature tests plataforma (auth, 422, vacío, alias `page_size`).

### Fuera de alcance

- Scripts SP y mapper campo a campo → [TR 25b](TR-SPEC-101-25-integracion-export-datos.md).
- POST confirmación descarga ERP.
- Cambios en `/consultas/*` y `ConsultaListadoService` en runtime del endpoint.

---

## 2) Criterios de aceptación (HU-101-057)

| CA HU | Verificación TR |
|-------|-----------------|
| CA-01 | Feature: token integración + tenant → 200, `error: 0`, keys `items/page/pageSize/total/totalPages/metadata` |
| CA-02…CA-05 | Feature con fixture 25b: filtros estado y extremos |
| CA-06…CA-07 | Feature: sin `estadoRangos` → 422; `1:0` → 422 |
| CA-08 | Feature: vendedor portal → 403 |
| CA-09 | OpenAPI path + query + `ApiEnvelopeIntegracionComprobantesExport` |
| CA-10 | Feature datos + plataforma: dedup mismo page |
| CA-11 | Feature: `page_size` = `pageSize` |
| CA-12 | Feature: sin datos → `total: 0`, `totalPages: 0` |
| CA-13…CA-15 | Feature parser CSV+repetido, fechas, AND `codCliente` |
| CA-16 | Feature o unit: log `export_comprobantes` (o `@group optional` si log falla no rompe 200) |
| CA-17 | Test rate limiter configurado (`integracion-export`) |

---

## 3) Contrato HTTP

### 3.1 Ruta y middleware

Registrar en `backend/routes/api.php` dentro del mismo bloque `auth:sanctum` + `paq.tenant` que integración:

```php
Route::get('/integracion/comprobantes', [IntegracionExportComprobantesController::class, 'index'])
    ->middleware(['integracion.importacion', 'throttle:integracion-export'])
    ->name('api.v1.integracion.comprobantes.index');
```

**Rate limit:** en `RouteServiceProvider::configureRateLimiting`, añadir `integracion-export` **idéntico** a `integracion-maestros` (60/min por `user id|tenant`).

### 3.2 Query parameters

| Parámetro | Validación Laravel / regla |
|-----------|---------------------------|
| `estadoRangos` | required, array (tras normalización); cada elemento regex `^-?\d+:-?\d+$`; parsear a `desde`/`hasta` int; `desde <= hasta`; ambos ∈ [-1, 99] |
| `page` | integer, min:1; default 1 |
| `pageSize` | integer, between:1,1000; default/máx vía `ConsultaPaginacion::DEFAULT_PAGE_SIZE` / `MAX_PAGE_SIZE`; alias `page_size` en `prepareForValidation` |
| `codCliente` | optional, string, max length según modelo |
| `codPedido` | optional, string/UUID según modelo |
| `fechaDesde` / `fechaHasta` | optional, date; parse ISO o `Y-m-d` → instantes ART día completo |

Normalización `estadoRangos`:

1. `$request->query('estadoRangos', [])` — si llega string único CSV, expandir por comas en pares adicionales.
2. Unir repetidos y CSV en **una** lista de pares antes de validar.

### 3.3 Respuesta 200

```json
{
  "error": 0,
  "respuesta": "integracion.export.ok",
  "resultado": {
    "items": [
      {
        "cabecera": { "codPedido": "…", "estado": 0, "fecha": "…" },
        "renglones": [],
        "cierre": null
      }
    ],
    "page": 1,
    "pageSize": 20,
    "total": 1250,
    "totalPages": 63,
    "metadata": {
      "fechaProceso": "2026-11-26T16:30:00-03:00"
    }
  }
}
```

- `metadata.fechaProceso`: `ConsultaFechaProcesoFormatter::now()` (marca de lectura; no fecha de cabecera).
- `totalPages`: `total === 0 ? 0 : (int) ceil(total / pageSize)`.

### 3.4 Matriz de errores

| Caso | HTTP | Clave `respuesta` (ej.) |
|------|------|-------------------------|
| Sin Bearer | 401 | `auth.unauthenticated` |
| Sin `pw_integracion_importacion` | 403 | `integracion.export.forbidden` |
| Tenant inválido | 400/404 | misma convención `ValidatePaqTenant` / maestros |
| Sin `estadoRangos` | 422 | `integracion.export.estadoRangosRequerido` |
| Par inválido / extremo / `desde>hasta` | 422 | `integracion.export.rangoInvalido` |
| `fechaDesde > fechaHasta` | 422 | `integracion.export.rangoFechasInvalido` |
| `page` / `pageSize` inválidos | 422 | `integracion.export.paginacionInvalida` |
| Fallo SP / 5xx | 500 | clave i18n genérica; sin SQL en cuerpo |

---

## 4) Diseño backend (capa plataforma)

| Componente | Responsabilidad |
|------------|-----------------|
| `IntegracionExportComprobantesController` | `index()` → request → service → `ApiResponse::success` |
| `IntegracionExportComprobantesRequest` | Reglas query; método `estadoRangoPairs(): array` |
| `EstadoRangosParser` | CSV + repetido → pares únicos ordenados (opcional class) |
| `ExportComprobantesQuery` | DTO inmutable: rangos, page, pageSize, filtros, timezone ART |
| `ExportComprobantesService` | Orquesta repositorio; arma envelope `resultado`; **sin SQL** |
| `ExportComprobantesRepositoryInterface` | `list(ExportComprobantesQuery): ExportComprobantesPage` |
| `StoredProcedureExportComprobantesRepository` | Implementación 25b |
| `IntegracionExportLogWriter` | `LogIntegracionService::registrar('export_comprobantes', …)` |

El controller **no** invoca `ConsultaListadoService`.

### 4.1 Fechas ART (decisión C1)

Clase **`App\Support\PedidosWeb\IntegracionExportFechaRange`**: timezone `America/Argentina/Buenos_Aires`; `YYYY-MM-DD` → inicio/fin de día inclusive; valores `datetime2` hacia el SP. No existe filtro fecha en consultas comprobantes hoy — **no** reutilizar `ConsultaListadoService`.

---

## 5) OpenAPI

- **Tag:** `Integración exportación`
- **Path:** `GET /api/v1/integracion/comprobantes`
- **Schemas:**
  - `IntegracionExportComprobantesQuery` (query params)
  - `IntegracionExportComprobanteItem` (`cabecera`, `renglones`, `cierre`)
  - `IntegracionExportComprobantesResultado`
  - `ApiEnvelopeIntegracionComprobantesExport`
- **Security:** Bearer + parameter header `X-Paq-Cliente`
- Anotaciones en controller y/o `PedidosWebOpenApiPaths.php`
- Ejemplo query: `estadoRangos=0:0&page=1&pageSize=50`

Detalle de propiedades de `cabecera`/`renglón`: referencia cruzada a schema 25b en OpenAPI.

---

## 6) Logging

| Campo log | Valor |
|-----------|--------|
| `tipo` | `export_comprobantes` |
| `severidad` | `info` en 200; `error` en 5xx/tenant |
| Payload mínimo | `page`, `pageSize`, `total`, resumen rangos (string), `codCliente`/`codPedido` si presentes |

Fallo al escribir log **no** debe cambiar HTTP 200 (Should).

---

## 7) Tests

```text
tests/Feature/Api/PedidosWeb/IntegracionExportComprobantesPlatformTest.php
tests/Unit/PedidosWeb/Integracion/EstadoRangosParserTest.php
```

Casos mínimos sin depender de SP real: mock `ExportComprobantesRepositoryInterface`.

Con BD/SP (post 25b o `@group integracion`):

- 200 vacío, alias `page_size`, CSV+rangos, 403 vendedor, 422 obligatorios.

---

## 8) Plan de implementación (orden D1)

1. i18n `integracion.export.*` + rate limiter `integracion-export`.
2. Request + parser rangos + DTO query.
3. Controller, ruta, service con repositorio fake.
4. Cablear repositorio SP (25b).
5. Log writer.
6. OpenAPI.
7. Suite feature plataforma.

---

## 9) Decisiones C1

| # | Tema | Decisión |
|---|------|----------|
| C1-01 | Throttle | **`integracion-export`** dedicado; 60/min `userId\|tenant` (igual que maestros) |
| C1-02 | Alias `page_size` | `prepareForValidation` + `ConsultaPaginacion` |
| C1-03 | `metadata.fechaProceso` | `ConsultaFechaProcesoFormatter::now()` |
| C1-04 | Fechas query | `IntegracionExportFechaRange` (ART) |
| C1-05 | Log | **Should**; test optional |
| C1-06 | Tenant HTTP | Misma convención integración maestros |

Cierre: [F-101-25-cierre-c1](F-101-25-cierre-c1-exportacion-comprobantes-erp.md).

## 10) Checklist normas transversales

- [x] Ruta con `integracion.importacion` + policy ability.
- [x] OpenAPI + matriz permisos endpoint ↔ `pw_integracion_importacion`.
- [x] Envelope `error: 0` entero en éxito.
- [x] `X-Paq-Cliente` documentado.
- [x] Tests 401/403.
- [x] Sin SQL en controller (regla 74).

## 11) Veredicto

| Fase | Estado |
|------|--------|
| C | Generada 2026-11-26 |
| C1 | **Apto con observaciones** — autoriza D1 |
| D / F1 / F / I | **Finalizado** 2026-11-26 — [F-101-25-cierre-formal](F-101-25-cierre-formal.md) |