# Cierre F — CC PQ #12 (28/08/2026) — Deuda, unidades, stockeables, leyendas, colores, historial

## Alcance

Verificación **F1 + F** (openspec-05 / agent-verification-guide) sobre las 6 correcciones del Control de Calidad #12:

| # | Tema | Updates principales |
|---|------|---------------------|
| 1 | Saldo deuda en carga + modal | SPEC/TR-101-10-01, HU-004-01, HU-005 |
| 2 | Unidades venta modal + mail Bultos/Unidades + precio neto | SPEC/TR-101-10-01, SPEC/TR-101-13-01, HU-006-01, HU-008, HU-019-01 |
| 3 | No stockeables | SPEC-001-04-01, SPEC/TR-101-02-02, SPEC/TR-101-07-01, TR-GEN-04-01, HU-GEN-04-01, HU-018 |
| 4 | Sync leyendas dirty al grabar | SPEC/TR-101-04-01, HU-009/010/011 |
| 5 | Colores informe deuda | SPEC/TR-101-11-01, HU-021 |
| 6 | Rango fechas historial ventas | SPEC/TR-101-07-01, SPEC/TR-101-11-01, HU-023 |

**Fecha verificación F1:** 29/08/2026  
**Parte E:** [E-CC-PQ-12-tests.md](E-CC-PQ-12-tests.md) (28/08/2026)  
**Rama / HEAD:** `v1.1.1` @ `9802436`  
**Script smoke HTTP:** `backend/scripts/smoke-cc-pq-12-f.php` (para re-ejecutar cuando BD responda)

---

## F1 — Verificación agente (código + tests)

**Resultado F1:** **Aprobado con observaciones**

### Ítem 1 — Saldo deuda en carga

| AC / RN | Evidencia | Estado |
|---------|-----------|--------|
| Saldo verde ≤0 / negro pendiente / rojo vencido | `deudaPresentacion.ts` + `CargaDeudaSaldoPanel.tsx` + CSS `consultasShared.css` | OK |
| Panel tras elegir cliente (web + mobile) | `PedidosCargaPage.tsx`, `PedidosCargaMobilePage.tsx` | OK |
| Icono modal si saldo ≠ 0 | `cargaDeudaDetalleOpen`, Popup sin export | OK |
| `data-testid` estables | `carga-deuda-panel`, `cargaDeudaSaldo`, `carga-deuda-detalle-popup` | OK |
| API deuda por cliente | `fetchDeudaPorCliente` en `consultaApi.ts` | OK (smoke HTTP pendiente BD) |

### Ítem 2 — Modal renglón + mail

| AC / RN | Evidencia | Estado |
|---------|-----------|--------|
| Equivalencia unidades si `CargaUnidadesVenta` | `PedidosCargaRenglonEditDialog.tsx` — `unidadesStockEquivalentes` | OK |
| Precio unitario neto en modal | `calcularPrecioNetoUnitario` + label i18n | OK |
| Mail columnas Bultos / Unidades | `comprobante-notification-body.blade.php` + `mail.php` (5 locales) | OK |
| `cantidad` y `cantidad_venta` no colapsados | `ComprobanteMailService::buildViewData` + test PHPUnit | OK (E 28/08) |

### Ítem 3 — No stockeables

| AC / RN | Evidencia | Estado |
|---------|-----------|--------|
| Columna `stockeable` (default true) | migración + `alter-pq-pedidosweb-stockeable.sql` + modelo | OK |
| Parámetro `IncluyeArticulosNoStockeables` | seed JSON + i18n parametros 5 locales | OK |
| Listbox sin stock si `stockeable=false` | `cargaCatalogos.ts` + test Vitest | OK |
| Excluir de consulta stock | `StockConsultaService.php` + test PHPUnit | OK (E; skip si sin tabla) |
| API artículos expone `stockeable` | `ArticuloCargaLookupService.php` | OK |

### Ítem 4 — Leyendas dirty

| AC / RN | Evidencia | Estado |
|---------|-----------|--------|
| Snapshot al cargar cabecera | `leyendasDirtySession.ts` + ref en Page/Mobile hook | OK |
| Payload `leyendas_dirty` al grabar | `comprobanteApi.ts` + Page/Mobile | OK |
| BE sync solo dirty + `ClienteLeyendaN` | `PedidoService::syncClienteLeyendasSiDirty` | OK |
| No sync si no dirty en sesión (escenario pedido 100) | lógica FE flags + BE `filter_var` dirty | OK (código; sin E2E) |

### Ítem 5 — Colores informe deuda

| AC / RN | Evidencia | Estado |
|---------|-----------|--------|
| Saldo a favor verde / vencido rojo | `DeudaPage.tsx` `onCellPrepared` + `resolveDeudaSaldoCellTone` | OK |
| Infra grilla | `DataGridDx.tsx` prop `onCellPrepared` | OK |

### Ítem 6 — Historial fechas

| AC / RN | Evidencia | Estado |
|---------|-----------|--------|
| DateBox desde/hasta (web) | `HistorialVentasPage.tsx` + `data-testid` | OK |
| 4 combinaciones filtro BE | `HistorialVentasConsultaService::buildQuery` + tests PHPUnit | OK (E 28/08) |
| FE envía query params | `consultaApi.ts` `fecha_desde`/`fecha_hasta` | OK |
| OpenAPI documentado | `PedidosWebOpenApiPaths.php` + `api-docs.json` | OK (estático verificado) |

### Tests re-ejecutados en F (29/08/2026)

| Suite | Comando | Resultado |
|-------|---------|-----------|
| Vitest CC #12 | `npx vitest run …deudaPresentacion…leyendasDirty…cargaCatalogos…` | **15 passed** |
| PHPUnit CC #12 | filtro E | **No re-ejecutable** — `SQLSTATE[08001]` timeout ODBC SQL Server |
| Smoke HTTP | `php scripts/smoke-cc-pq-12-f.php` | **Bloqueado** — login 500 por mismo timeout |

**Referencia Parte E (28/08/2026):** 11 PHPUnit + 15 Vitest passed (ver [E-CC-PQ-12-tests.md](E-CC-PQ-12-tests.md)).

---

## F — Smoke HTTP / QA manual

**Entorno intentado:** `http://localhost:3010` → backend `http://127.0.0.1:8088` — tenant `desarrollo`

| Escenario | Resultado |
|-----------|-----------|
| `GET /health` | OK (sin auth) |
| Login + APIs autenticadas (deuda, historial fechas, stock, artículos, parámetros) | **No ejecutado** — timeout conexión SQL Server |
| OpenAPI UI | No verificado en vivo (BD caída impide bootstrap completo en tests Feature) |
| OpenAPI JSON estático (`storage/api-docs/api-docs.json`) | OK — `fecha_desde` / `fecha_hasta` presentes en historial-ventas |

### Checklist QA manual PQ (pendiente con BD disponible)

| # | Escenario | Resultado PQ |
|---|-----------|--------------|
| 1 | Carga pedido: elegir cliente → saldo coloreado + modal comprobantes | Pendiente |
| 2 | `CargaUnidadesVenta=true`: modal renglón muestra unidades equivalentes y precio neto | Pendiente |
| 3 | Grabar pedido → mail con columnas Bultos y Unidades distintas | Pendiente |
| 4 | Artículo `stockeable=0`: listbox sin disponible; ausente en consulta stock | Pendiente |
| 5 | Modificar leyenda1 y grabar → actualiza maestro; editar pedido viejo sin tocar leyenda → no pisa maestro | Pendiente |
| 6 | Informe deuda: saldos a favor verde, vencidos rojo | Pendiente |
| 7 | Historial ventas: filtros fecha vacío / solo desde / solo hasta / rango | Pendiente |

**Resultado QA manual PQ:** **Pendiente** (bloqueado por conectividad BD en sesión 29/08/2026).

---

## F — Verificación documental (TR ↔ SPEC ↔ HU)

| Documento | Alineado | Nota |
|-----------|----------|------|
| 6 familias SPEC-update / HU-update / TR-update | Sí | Volcado G 28/08/2026 |
| `E-CC-PQ-12-tests.md` | Sí | Parte E aprobada |
| `00-ControlCalidad-PQ.md` #12 | Sí | Tabla ciclo actualizada |
| OpenAPI historial-ventas | Sí | Params fecha en spec generado |

### Observaciones no bloqueantes

| ID | Tema | Destino |
|----|------|---------|
| OBS-F-01 | Smoke HTTP + PHPUnit con BD caída | Re-ejecutar `smoke-cc-pq-12-f.php` y filtro E cuando SQL Server responda |
| OBS-F-02 | QA manual PQ checklist § arriba | PQ en `localhost:3010` tras migrate/SQL `stockeable` |
| OBS-F-03 | Historial mobile sin DateBox (solo web) | Aceptado en alcance TR-update (web informes) |
| OBS-F-04 | Deploy `migrate` + `alter-pq-pedidosweb-stockeable.sql` en tenants | Runbook deploy pre-prod |

---

## Veredicto final

| Control | F1 (agente) | F (smoke HTTP) | F (manual PQ) |
|---------|-------------|----------------|---------------|
| CC #12 (28/08/2026) | **Aprobado con observaciones** | **Pendiente** (BD) | **Pendiente** (checklist) |

**Estado CC #12:** **Implementado (G+D+E 28/08/2026; F1 29/08/2026).** Pendiente smoke PQ con BD activa y **Parte I**.

**Recomendación:** Con SQL Server disponible, ejecutar en orden:

```text
php artisan migrate --force
php scripts/smoke-cc-pq-12-f.php
php artisan test --filter="HistorialVentasConsultaServiceTest|PedidoServiceLeyendasSyncTest|StockConsultaServiceTest::testListarExcluye"
```

Luego completar checklist QA manual y marcar F (manual PQ) como **Aprobado** en esta misma acta.
