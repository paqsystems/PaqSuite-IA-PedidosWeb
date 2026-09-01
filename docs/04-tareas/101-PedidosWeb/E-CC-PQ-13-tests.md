# E — CC PQ #13 (01/09/2026) — Evidencia tests

## Alcance

Parte **E** previa a **F** / **I** sobre el límite de **60 caracteres Unicode** en leyendas 1–5 de cabecera:

1. DDL `nvarchar(60)` en `pq_pedidosweb_pedidoscabecera` y `pq_pedidosweb_clientes`
2. Recorte al grabar pedido/presupuesto (API) — **sin** 4xx por longitud
3. UI carga (`ComprobanteLeyendasPie` `maxLength={60}`, web + native)
4. Excel individual y masivo: recortar, **sin** bajar `largo_maximo` del catálogo a 60
5. Asistente IA (`setCampoLibre` + extracto imagen + patch FE)

**Fecha ejecución:** 01/09/2026  
**Entorno:** Local — SQL Server (`paqsystems_pedidosweb_ankasdelsur` vía `backend/.env`)  
**Rama / HEAD:** `v1.1.1` @ `40aac3f` (working tree con D de #13, sin commit)

**TR-update:** [TR-SPEC-101-02](../updates/101-PedidosWeb/TR-SPEC-101-02-modelos-update.md) · [TR-SPEC-101-04](../updates/101-PedidosWeb/TR-SPEC-101-04-services-pedidos-update.md) · [TR-SPEC-101-10](../updates/101-PedidosWeb/TR-SPEC-101-10-pantalla-carga-update.md) · [TR-SPEC-101-16](../updates/101-PedidosWeb/TR-SPEC-101-16-proceso-excel-pedido-individual-update.md) · [TR-SPEC-101-21](../updates/101-PedidosWeb/TR-SPEC-101-21-proceso-excel-pedido-masivo-update.md) · [TR-SPEC-101-19](../updates/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones-update.md)

---

## Backend — PHPUnit (filtro CC #13)

```text
php vendor/bin/phpunit --testdox ^
  tests/Unit/Support/LeyendaCabeceraLimitsTest.php ^
  tests/Unit/Services/ExcelImport/PedidoIndividual/PedidoIndividualRowResolverLeyendasTest.php ^
  tests/Unit/Services/ExcelImport/PedidoMasivo/PedidoMasivoGroupAssemblerLeyendasTest.php ^
  tests/Unit/Services/ExcelImport/ExcelLeyendaCatalogoLargoTest.php ^
  tests/Unit/PedidosWeb/Services/CabeceraInicialServicePerfilTest.php ^
  tests/Unit/PedidosWeb/Services/PedidoServiceLeyendasSyncTest.php ^
  tests/Feature/PedidosWeb/LeyendasNvarchar60SchemaTest.php ^
  tests/Unit/Services/PedidosWeb/CargaAsistente/CargaAsistenteToolsTest.php ^
  tests/Unit/Services/PedidosWeb/CargaAsistente/CargaAsistenteImageExtractDeferredTest.php ^
  tests/Feature/OpenApiDocumentationTest.php ^
  --filter "Leyenda|leyenda|Leyendas|SetCampoLibreRecorta|SetCampoLibreConserva|BuildCabeceraStepsRecorta|columnasLeyenda|IncludesPedidosWebPaths|resolveLeyenda"
```

**Resultado:** 17 tests, 206 assertions — **OK** (1 deprecation PHPUnit, no bloqueante).

### Tests nuevos / extendidos CC #13

| Archivo | Cobertura |
|---------|-----------|
| `LeyendaCabeceraLimitsTest.php` (5) | null/vacío, 60, 61, unicode `ñ`, mapa snake/camel |
| `LeyendasNvarchar60SchemaTest.php` (1) | 10 columnas `leyenda_1..5` con `max_length = 120` (nvarchar 60) |
| `PedidoServiceLeyendasSyncTest.php` (+1) | Sync dirty recorta 61 → 60 |
| `CabeceraInicialServicePerfilTest.php` (+1) | `resolveLeyendaCliente` recorta a 60 |
| `PedidoIndividualRowResolverLeyendasTest.php` (1) | `enrichRow` recorta `leyenda1`/`leyenda2` |
| `PedidoMasivoGroupAssemblerLeyendasTest.php` (1) | `buildCabeceraFromRow` recorta `leyenda_1`/`leyenda_2` |
| `ExcelLeyendaCatalogoLargoTest.php` (1) | Catálogo seeder `largo_maximo = 255` (no 60) |
| `CargaAsistenteToolsTest.php` (+2) | `setCampoLibre` 61 → 60 sin `validationError`; 60 intacto |
| `CargaAsistenteImageExtractDeferredTest.php` (+1) | Extracto imagen: steps `leyenda1` recortada |
| `OpenApiDocumentationTest.php` (+2 asserts) | `ComprobanteCabeceraRequest.leyenda_1` / `leyenda_5` `maxLength=60` |

**OpenAPI:** anotaciones en `OpenApiSchemas.php` (`ComprobanteCabeceraRequest`) + `composer openapi` / `php artisan l5-swagger:generate`.

### Mapa AC → evidencia automática

| AC | Evidencia |
|----|-----------|
| AC-CC13-T-M1 | `LeyendasNvarchar60SchemaTest` (10 columnas, `max_length=120`) |
| AC-CC13-T-M2 | Migración D: `LEFT` + `ALTER` aplicada 01/09/2026; schema test confirma estado actual |
| AC-CC13-T-M3 | Revisión de código: `PedidosWebDevSchemaBootstrap` CREATE `nvarchar(60)` (clientes y cabecera) |
| AC-CC13-T-M4 | Script SQL idempotente (rama D); no re-ejecutado en E |
| AC-CC13-T-G1 | `PedidoService::grabarComprobante` llama `recortarLeyendasEnMapa`; helper unit |
| AC-CC13-T-G2 | Helper + sync recorte 61→60. Feature HTTP `comprobanteGrabarRecortaLeyendaLargaASesenta` **skip** (ver observación) |
| AC-CC13-T-G3 | OpenAPI `maxLength=60` en spec generada |
| AC-CC13-T-G4 | `resolveLeyendaClienteRecortaASesenta` |
| AC-CC13-T-C1/C2/C3 | Vitest `ComprobanteLeyendasPie` (`maxLength` + `leyenda-1..5` + `leyendas-pie`) |
| AC-CC13-T-C4 | Producto §9 (`pantalla-carga-comprobante-ui.md`) — revisión documental |
| AC-CC13-T-X1 | `ExcelLeyendaCatalogoLargoTest` (`largo_maximo=255`) |
| AC-CC13-T-X2 | `PedidoIndividualRowResolverLeyendasTest` |
| AC-CC13-T-M1 (masivo) / AC-CC13-T-M2 (masivo) | Catálogo 255 compartido + `PedidoMasivoGroupAssemblerLeyendasTest` |
| AC-CC13-T-A1 / A2 | `CargaAsistenteToolsTest` setCampoLibre 61 / 60 |
| AC-CC13-T-A3 | `testBuildCabeceraStepsRecortaLeyendaDeImagen` (recorte en steps hacia `setCampoLibre`) |

### Observación entorno

| Test | Resultado |
|------|-----------|
| Filtro PHPUnit CC #13 (17) | **Pass** |
| `PedidosWebEndpointsHappyPathTest::comprobanteGrabarRecortaLeyendaLargaASesenta` | **Skip** — `Tenant desarrollo / SQL Server no disponible para feature 200` (`paqsuite:seed-menus-mvp` / `paqsuite:seed-seguridad-mvp` no cierra 0 en este PHPUnit). No implica fallo del recorte: `grabarComprobante` aplica el helper antes de persistir. |

No hay DROP ni TRUNCATE. Schema test solo lee `sys.columns` con `NOLOCK`.

---

## Frontend — Vitest

```text
npx vitest run src/features/pedidos/utils/recortarLeyendaCabecera.test.ts src/features/pedidos/components/ComprobanteLeyendasPie.test.tsx src/features/pedidos/cargaAsistenteIa/utils/patchAsistenteCabecera.test.ts
```

| Archivo | Cobertura |
|---------|-----------|
| `recortarLeyendaCabecera.test.ts` (3) | null/vacío, 60, recorte 61 |
| `ComprobanteLeyendasPie.test.tsx` (1) | cinco TextBox `maxLength={60}`; `data-testid` `leyendas-pie` y `leyenda-1`…`leyenda-5` |
| `patchAsistenteCabecera.test.ts` (2) | patch IA recorta leyenda 61; conserva 60; observaciones sin recortar |

**Resultado:** 3 archivos, 6 tests passed.

Warning jsdom: `act(...)` no configurado en el entorno Vitest al renderizar el pie. No falla el assert.

**No automatizado en E (cubre F / smoke PQ):** tecleo real en DevExtreme (web y native), importación Excel de archivo `.xlsx` con leyenda > 60, turno asistente IA contra LLM.

---

## Veredicto Parte E

**Aprobado con observaciones** — 17 PHPUnit + 6 Vitest del filtro CC #13 en verde. Schema tenant = `nvarchar(60)`. OpenAPI documenta `maxLength=60`.

**Observaciones:**

1. Feature HTTP de grabar con 61 caracteres **skip** por seed de tenant en PHPUnit; cobertura de recorte al persistir vía helper + `PedidoService` + sync.
2. Idempotencia del script SQL (AC-CC13-T-M4) no re-ejecutada en E.
3. Smoke UI/Excel/IA end-to-end queda para **F**.

**Pendiente Parte F:** checklist formal (carga web `maxLength`, grabar, Excel, asistente) + OpenAPI UI `http://127.0.0.1:8088/api/documentation` (`ComprobanteCabeceraRequest.leyenda_N`).

**Deploy (recordatorio, no ejecutado en E):** en Forge `php artisan migrate --force` y/o `backend/scripts/sql/alter-pq-pedidosweb-leyendas-60.sql` (ya aplicado en el tenant local de D).
