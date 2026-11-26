# Plan de implementación D1 — SPEC-101-25 — Exportación comprobantes ERP

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-11-26 |
| **Parte** | D1 — `ai-planning-mode` |
| **C1** | [F-101-25-cierre-c1](F-101-25-cierre-c1-exportacion-comprobantes-erp.md) — **Apto con observaciones** |
| **TR** | [25a](TR-SPEC-101-25-integracion-export-plataforma.md) · [25b](TR-SPEC-101-25-integracion-export-datos.md) |
| **HU / SPEC** | [HU-057](../../03-historias-usuario/101-PedidosWeb/HU-101-057-integracion-export-comprobantes-plataforma.md) · [HU-058](../../03-historias-usuario/101-PedidosWeb/HU-101-058-integracion-export-comprobantes-datos.md) · [SPEC-101-25](../../05-open-spec/101-PedidosWeb/SPEC-101-25-exportacion-comprobantes-erp.md) |
| **Jira** | [PIAPEDWEB-54](https://paqsystems.atlassian.net/browse/PIAPEDWEB-54) · [58](https://paqsystems.atlassian.net/browse/PIAPEDWEB-58) · [59](https://paqsystems.atlassian.net/browse/PIAPEDWEB-59) |
| **Estado del plan** | **Listo para Parte D** |
| **Orden** | **25a → 25b** (25a puede usar repositorio fake hasta cablear SP) |

---

## Alcance entendido

Implementar **un único endpoint** de solo lectura:

```http
GET /api/v1/integracion/comprobantes
```

- Auth: Sanctum + `paq.tenant` + middleware `integracion.importacion` (`pw_integracion_importacion`).
- Respuesta síncrona envelope **GEN-20** (`error: 0`), paginación y `items[]` con `cabecera`, `renglones`, `cierre`.
- Query obligatoria `estadoRangos` (repetido y/o CSV); filtros opcionales; **sin** visibilidad portal vendedor.
- Lectura de negocio **solo vía SP** `pq_sp_integracion_export_comprobantes_list` (dos result sets).
- OpenAPI, i18n `integracion.export.*`, log Should `export_comprobantes`, rate limit **`integracion-export`**.

**Fuera de alcance:** frontend/mobile, POST ack ERP, GEN-36, cambios en `/consultas/*`, `ConsultaListadoService` en runtime del endpoint.

---

## Fuentes leídas

- SPEC-101-25, A1, B1, C, C1.
- HU-101-057, HU-101-058 (B1).
- TR 25a, 25b (C1).
- Producto: [APIs-Exportacion-comprobantes-erp.md](../../02-producto/PedidosWeb/APIs-Exportacion-comprobantes-erp.md).
- SoT columnas: [consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md) §2, [consulta-detalle-pedidos.md](../../02-producto/PedidosWeb/consulta-detalle-pedidos.md) §4.
- Código referencia integración 101-22/101-23:
  - `EnsureIntegracionImportacion`, `routes/api.php`, `RouteServiceProvider` (limiters maestros/transacciones).
  - `SeedsIntegracionMaestrosFeatureData`, `IntegracionMaestroPlatformTest`.
  - `ConsultaListadoService::mapComprobanteItem` / `mapPresupuestoCierre` (contrato de shape, **no** invocar).
  - `ConsultaFechaProcesoFormatter`, `ConsultaPaginacion`, `LogIntegracionService`.
  - `lang/es/integracion.php` (patrón claves maestro/transacción).
  - Modelo `PqPedidoswebPedidoCabecera` (columnas integración y orígenes).
- Deploy SQL patrón: `backend/scripts/deploy-integracion-maestros-sqlserver.php`, `scripts/sql/integracion-transacciones/`.

---

## Decisiones D1

| ID | Decisión |
|----|----------|
| D1-25-01 | Reutilizar **infra** integración existente: middleware, ability, tenant, envelope `ApiResponse`, trait de tests `SeedsIntegracionMaestrosFeatureData`; no duplicar auth. |
| D1-25-02 | Namespace nuevo: `App\Services\PedidosWeb\Integracion\Export\` (no mezclar con Maestros/Transacciones). |
| D1-25-03 | Limiter **`integracion-export`** en `RouteServiceProvider` (clonar cuerpo de `integracion-maestros`). |
| D1-25-04 | Binding `ExportComprobantesRepositoryInterface` → `StoredProcedureExportComprobantesRepository` en `PedidosWebServiceProvider`; tests 25a usan **fake** hasta 25b. |
| D1-25-05 | **Dos result sets** vía PDO: helper interno `SqlServerStoredProcedureReader::executeMultipleResultSets()` (o equivalente mínimo en el repository) — el repo hoy no tiene precedente multi-rowset; encapsular en una sola clase. |
| D1-25-06 | SP: predicado estado **OR** entre rangos; **dedup** por `cod_pedido` en la consulta paginada; `COUNT` con mismo predicado vía `@total OUTPUT`; **sin** `WHERE IN` de listas runtime en PHP (renglones del page solo en RS2 dentro del SP). |
| D1-25-07 | Joins cabecera en SP alineados a consulta cabecera (clientes, vendedor, perfil, condventa, transporte, listaprecios, dirección entrega); renglones con artículo para descripción/especial; cierre **LEFT JOIN** `pq_pedidosweb_presupuestos_cierres` + motivo solo para filas `estado = 98` (columnas en RS1 o join condicional documentado en SQL). |
| D1-25-08 | Mapper PHP: tipos JSON como consultas (`moneda` int, fechas ISO8601, `nroPedidoTango` **string** `""` si null); campos orígenes solo si columna no null (`origenComprobante`, `codPedidoOrigen`, `codPresupuestoOrigen`). |
| D1-25-09 | Errores validación: `FormRequest` → 422 + claves `integracion.export.*`; códigos `error` envelope alineados a convención existente (validación ~1000). |
| D1-25-10 | OpenAPI: nuevo archivo `OpenApiIntegracionExportPaths.php` + schemas en `OpenApiGetsSchemas.php` o archivo dedicado export (seguir patrón `OpenApiIntegracionTransaccionesPaths.php`). |
| D1-25-11 | i18n: bloque `'export' => [...]` en `lang/es/integracion.php` (y locales producto si el host replica integracion en otros idiomas — mínimo **es** en D). |
| D1-25-12 | Log: `LogIntegracionService::registrar` con `tipo = export_comprobantes`; envolver en try/catch en writer — no afectar 200. |
| D1-25-13 | Permiso documentado: mismo ability **`pw_integracion_importacion`** que maestros/transacciones; matriz OpenAPI + comentario en controller. |

---

## Impacto esperado

### Base de datos

- Carpeta nueva: `backend/scripts/sql/integracion-export/`.
  - `pq_sp_integracion_export_comprobantes_list.sql`
  - `00-deploy-all.sql`
- Script PHP: `backend/scripts/deploy-integracion-export-sqlserver.php` (análogo maestros).
- SP idempotente `CREATE OR ALTER PROCEDURE`.
- Lecturas con `WITH (NOLOCK)` en tablas de negocio/joins (regla 75).
- **No** migrations Laravel obligatorias salvo documentación en runbook; deploy vía script (como 101-22/23).

### Backend

#### Reutilizar

| Pieza | Uso |
|-------|-----|
| `EnsureIntegracionImportacion` | Middleware ruta |
| `ApiResponse` / envelope GEN-20 | Controller |
| `ConsultaFechaProcesoFormatter` | `metadata.fechaProceso` |
| `ConsultaPaginacion` | Defaults validación |
| `LogIntegracionService` | Log export |
| `SeedsIntegracionMaestrosFeatureData` | Feature tests auth |

#### Crear (25a — plataforma)

```text
app/Http/Controllers/Api/V1/PedidosWeb/IntegracionExportComprobantesController.php
app/Http/Requests/PedidosWeb/IntegracionExportComprobantesRequest.php
app/Services/PedidosWeb/Integracion/Export/EstadoRangosParser.php
app/Services/PedidosWeb/Integracion/Export/ExportComprobantesQuery.php
app/Services/PedidosWeb/Integracion/Export/ExportComprobantesService.php
app/Services/PedidosWeb/Integracion/Export/ExportComprobantesRepositoryInterface.php
app/Services/PedidosWeb/Integracion/Export/IntegracionExportLogWriter.php
app/Support/PedidosWeb/IntegracionExportFechaRange.php
app/Services/PedidosWeb/Integracion/Export/FakeExportComprobantesRepository.php  # solo tests/bootstrap inicial
```

#### Crear (25b — datos)

```text
app/Services/PedidosWeb/Integracion/Export/StoredProcedureExportComprobantesRepository.php
app/Services/PedidosWeb/Integracion/Export/IntegracionExportComprobanteMapper.php
app/Services/PedidosWeb/Integracion/Export/Dto/ExportComprobantesPage.php
app/Services/PedidosWeb/Integracion/Export/Dto/ExportComprobantesItem.php
app/Support/PedidosWeb/SqlServerStoredProcedureMultiResultReader.php  # si no existe
```

#### Tocar

- `routes/api.php` — GET `integracion/comprobantes`.
- `app/Providers/RouteServiceProvider.php` — limiter `integracion-export`.
- `app/Providers/PedidosWebServiceProvider.php` — bindings.
- `lang/es/integracion.php` — claves `export.*`.
- `app/OpenApi/OpenApiIntegracionExportPaths.php` (nuevo) + registro en swagger scan si aplica.
- `tests/Feature/OpenApiDocumentationTest.php` — assert path nuevo (si el test enumera integración).

### Frontend

Sin cambios.

### Mobile

Sin cambios (excluido por SPEC).

### Integración

Contrato servidor ERP consumidor; documentar en runbook junto a maestros/transacciones (opcional en mismo PR que D).

### Tests

```text
tests/Unit/PedidosWeb/Integracion/Export/EstadoRangosParserTest.php
tests/Unit/PedidosWeb/Integracion/Export/IntegracionExportComprobanteMapperTest.php
tests/Feature/Api/PedidosWeb/IntegracionExportComprobantesPlatformTest.php
tests/Feature/Api/PedidosWeb/IntegracionExportComprobantesDataTest.php
tests/Fixtures/PedidosWeb/IntegracionExportComprobantesFixture.php
```

- Plataforma: mock repository + `withoutMiddleware(ThrottleRequests::class)` como maestros.
- Datos: `@group integracion`; skip si falta SP/tabla; fixture inserta cabeceras -1,0,1,98,99 + renglones + cierre 98.

### Documentación

- TR 25a/25b: firmas SP y checklist OpenAPI cabecera al cerrar D.
- `docs/06-operacion/` o runbook integración: línea deploy `deploy-integracion-export-sqlserver.php` (sin ejecutar en ERP compartido sin consentimiento).
- Cierre D/E: [F-101-25-cierre-formal](F-101-25-cierre-formal.md) · F1 [D-VERIFICACION-101-25](D-VERIFICACION-101-25-exportacion-comprobantes-erp.md).

### DevOps

- Deploy SP en entorno dev/QA dedicado; **no** `Ankas_del_sur` sin consentimiento explícito.
- Sin cambios Vercel/Forge salvo mención en notas de release si hay script SQL nuevo.

---

## Orden de trabajo (Parte D)

### Fase 1 — 25a plataforma (entregable parcial testeable)

1. Rate limiter `integracion-export` + ruta + binding fake repository.
2. `IntegracionExportFechaRange` + unit tests fechas ART (límites inclusivos).
3. `EstadoRangosParser` + `IntegracionExportComprobantesRequest` (422, alias `page_size`).
4. `ExportComprobantesService` + controller + i18n `integracion.export.*`.
5. `IntegracionExportLogWriter` (Should, no rompe 200).
6. `IntegracionExportComprobantesPlatformTest` (401, 403 vendedor, 422, 200 mock vacío, `page_size`, rate limit config smoke).
7. OpenAPI path + schemas envelope/query (cabecera/renglón **stub** o ref parcial; completar en fase 2).

### Fase 2 — 25b datos (cierre funcional)

1. Redactar SP con joins y predicados; validar columnas físicas contra modelo/migrations.
2. `deploy-integracion-export-sqlserver.php` + `00-deploy-all.sql`.
3. `SqlServerStoredProcedureMultiResultReader` + `StoredProcedureExportComprobantesRepository`.
4. `IntegracionExportComprobanteMapper` (+ unit con arrays fake).
5. Sustituir fake por SP en `PedidosWebServiceProvider`.
6. `IntegracionExportComprobantesFixture` + `IntegracionExportComprobantesDataTest` (CA HU-058).
7. Completar OpenAPI `IntegracionExportComprobanteCabecera` / `Renglon` (checklist §2 consulta cabecera + §4 detalle).
8. Re-ejecutar platform tests contra SP real (`@group integracion` opcional unificado).

### Fase 3 — Cierre D

- `php artisan test` rutas anteriores + OpenAPI test.
- Actualizar TR con nombres definitivos de columnas SP.
- Comentario Jira D1 en 58/59/54.

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Primer SP con **dos result sets** en el host | Helper PDO único + test integración acotado; documentar en repository |
| SP pesado (joins × pageSize) | Paginación obligatoria; pageSize máx 1000; NOLOCK lecturas |
| Drift columnas detalle vs consulta | SoT `consulta-detalle-pedidos.md` §4; revisar DDL antes del SELECT |
| Overlap rangos estado | `DISTINCT cod_pedido` + OR; test CA-03/CA-10 |
| JSON rangos mal escapado | `json_encode` desde PHP tipado; validar en SP con `OPENJSON` |
| Tests sin SQL Server | Mock/fake en 25a; skip documentado en 25b |
| Fecha cabecera vs timezone | Filtro en columna `c.fecha` con datetime2 ART desde PHP |

---

## Tests a ejecutar (D / E)

```powershell
cd backend
php artisan test tests/Unit/PedidosWeb/Integracion/Export
php artisan test tests/Feature/Api/PedidosWeb/IntegracionExportComprobantesPlatformTest.php
# Con SP desplegado:
php artisan test tests/Feature/Api/PedidosWeb/IntegracionExportComprobantesDataTest.php --group=integracion
php artisan test tests/Feature/OpenApiDocumentationTest.php
```

---

## Dudas / bloqueos

| ID | Tema | Estado |
|----|------|--------|
| D1-25-Q01 | Columnas cierre en RS1 vs join lazy en PHP | **Cerrar en D:** preferir JOIN en SP para cabeceras 98; mapper arma `cierre` desde columnas prefijadas |
| D1-25-Q02 | Motivo cierre — tabla física | Usar relación existente `presupuestoCierre.motivo` como guía; confirmar nombre en `pq_pedidosweb_presupuestos_cierres` / catálogo motivos en SQL |
| D1-25-Q03 | `descripcionArticulo` congelada vs artículo | Regla consulta-detalle §4: preferir `d.descripcion_articulo` si existe columna |

Ningún bloqueo impide iniciar **Fase 1**.

---

## Checklist OpenAPI cabecera (D1 entregable)

Completar en PR de fase 2 contra [consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md) §2:

- [ ] Todos los campos §2 (excepto `puede*`, `guidSufijo`)
- [ ] MUST integración: `talPedidoTango`, `nroPedidoTango`, `codUsuarioWeb`
- [ ] SHOULD orígenes si en BD
- [ ] Siempre `codPedido` (no solo `codPresupuesto`)

---

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR-25:** **Sí**.
- No UI/mobile/Excel/cron.
- No escritura ERP ni confirmación descarga.
- No filtro visibilidad vendedor.
- No refactor global de `ConsultaListadoService`.
- No operaciones destructivas de BD sin consentimiento.

---

## Veredicto D1

**Plan listo para Parte D.** Iniciar implementación por **Fase 1 (25a)**; **Fase 2 (25b)** depende del deploy SP en entorno de prueba autorizado.