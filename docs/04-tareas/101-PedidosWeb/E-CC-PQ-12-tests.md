# E — CC PQ #12 (28/08/2026) — Evidencia tests

## Alcance

Parte **E** previa a **F** / **I** sobre las 6 correcciones del CC #12:

1. Saldo deuda en carga + modal comprobantes  
2. Equivalencia unidades / precio neto modal + mail Bultos/Unidades  
3. Artículos no stockeables (columna, param, listbox, consulta stock)  
4. Sync leyendas dirty al grabar  
5. Colores informe deuda  
6. Rango fechas historial ventas (+ OpenAPI)

**Fecha ejecución:** 28/08/2026  
**Entorno:** Local — SQL Server (`Ankas_del_sur` vía `.env` de test)  
**Rama / HEAD:** `v1.1.1` @ `9802436`

---

## Backend — PHPUnit (filtro CC #12)

```text
php artisan test --filter="HistorialVentasConsultaServiceTest|PedidoServiceLeyendasSyncTest|ComprobanteMailServiceTest::detalleMailConservaCantidadYBultosUnidadesSeparados|StockConsultaServiceTest::testListarExcluyeArticulosNoStockeables|PqParametrosGralPedidosWebSeedTest::seedIncluyeIncluyeArticulosNoStockeablesBooleano|OpenApiDocumentationTest"
```

### Tests nuevos / extendidos CC #12

| Archivo | Cobertura |
|---------|-----------|
| `HistorialVentasConsultaServiceTest.php` (4) | `fecha_desde`/`fecha_hasta`: rango, solo desde, solo hasta, fallback `DiasVentasDetalladas` |
| `PedidoServiceLeyendasSyncTest.php` (2) | Sync `leyenda_N` solo si `leyenda_N_dirty` + `ClienteLeyendaN` habilitado |
| `ComprobanteMailServiceTest.php` (+1) | `cantidad` y `cantidad_venta` separados en `detalle` del mail |
| `StockConsultaServiceTest.php` (+1) | Excluye artículos con `stockeable = 0` del listado |
| `PqParametrosGralPedidosWebSeedTest.php` (+1) | Seed `IncluyeArticulosNoStockeables` (tipo B, default false) |
| `OpenApiDocumentationTest.php` (+2 asserts) | Parámetros `fecha_desde` / `fecha_hasta` en `GET /consultas/historial-ventas` |

**OpenAPI:** anotaciones en `PedidosWebOpenApiPaths.php` + `php artisan l5-swagger:generate`.

**AC cubiertos (automático):** filtros historial (ítem 6), mail dual cantidad (ítem 2), leyendas dirty (ítem 4), no-stockeables stock/seed (ítem 3), contrato OpenAPI historial.

### Observación entorno

| Test | Resultado típico local |
|------|------------------------|
| Historial, Mail, Seed, OpenAPI | **Pass** |
| `PedidoServiceLeyendasSyncTest` | **Skip** si `pq_pedidosweb_clientes` (maestro ERP con `cod_client`) no está en la BD de test |
| `StockConsultaServiceTest::testListarExcluyeArticulosNoStockeables` | **Skip** si falta `pq_pedidosweb_stock` o columna `stockeable` |

No implica DROP ni cambio de esquema: inserts acotados con rollback por `DatabaseTransactions` del `TestCase` cuando las tablas existen.

---

## Frontend — Vitest

```text
npx vitest run src/features/consultas/utils/deudaPresentacion.test.ts src/features/pedidos/utils/leyendasDirtySession.test.ts src/features/pedidos/utils/cargaCatalogos.test.ts
```

| Archivo | Cobertura |
|---------|-----------|
| `deudaPresentacion.test.ts` (6) | Tonos saldo: favor (verde), pendiente (negro), vencido (rojo) — ítem 1 / 5 |
| `leyendasDirtySession.test.ts` (4) | Snapshot, dirty por sesión, map API `leyenda_N_dirty` — ítem 4 |
| `cargaCatalogos.test.ts` (+1) | `stockeable: false` → etiqueta sin disponible — ítem 3 |

**Resultado:** 15 tests passed (3 archivos).

**No automatizado en E (cubre F):** `CargaDeudaSaldoPanel`, modal renglón equivalencia/precio neto, `DeudaPage` `onCellPrepared`, `HistorialVentasPage` DateBox, mobile kardex deuda.

---

## Veredicto Parte E

**Aprobado** — batería CC #12 ejecutada; 11+ PHPUnit relevantes passed en filtro; 15 Vitest passed.

**Observaciones:** 2–3 casos PHPUnit pueden quedar en **skip** en BDs parciales (sin maestro clientes o sin stock). Re-ejecutar en entorno con tablas `pq_pedidosweb_*` completas antes de F si se requiere evidencia verde total.

**Pendiente Parte F (manual PQ):** checklist en [F-CC-PQ-12-cierre-formal.md](../04-tareas/101-PedidosWeb/F-CC-PQ-12-cierre-formal.md) — requiere BD activa (`php scripts/smoke-cc-pq-12-f.php`).

**Deploy (recordatorio, no ejecutado en E):** `php artisan migrate --force` y/o `backend/scripts/sql/alter-pq-pedidosweb-stockeable.sql` para columna `stockeable`.
