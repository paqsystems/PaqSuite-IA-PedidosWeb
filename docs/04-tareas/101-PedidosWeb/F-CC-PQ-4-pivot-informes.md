# Cierre F — CC PQ #4 (10/06/2026) — Pivot informes

## Alcance

Verificación **F1 + F** (openspec-05) sobre adopción pivot en informes PedidosWeb:

| TR | HU | SPEC |
|----|-----|------|
| [TR-SPEC-101-11-consultas-ui](TR-SPEC-101-11-consultas-ui.md) | [HU-101-028](../../03-historias-usuario/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos.md), [HU-101-021](../../03-historias-usuario/101-PedidosWeb/HU-101-021-consulta-deuda.md), [HU-101-022](../../03-historias-usuario/101-PedidosWeb/HU-101-022-consulta-cheques.md), [HU-101-018](../../03-historias-usuario/101-PedidosWeb/HU-101-018-consulta-stock.md) | [SPEC-101-11-consultas-ui](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |

**Dependencias transversales:** TR-GEN-08-* (motor, visualización, layouts, export — ya cerrados en [F-GEN-08-cierre-formal.md](../001-Generaliddes/F-GEN-08-cierre-formal.md)).

**Control de calidad origen:** [00-ControlCalidad-PQ.md](../../00-ControlCalidad/00-ControlCalidad-PQ.md) — #4 — **10/06/2026**

**Fecha verificación:** 11/06/2026  
**Parte E:** [E-CC-PQ-4-tests.md](E-CC-PQ-4-tests.md)  
**Rama / build:** `v1.1.0` (working tree local)

---

## F1 — Verificación del agente (código + tests)

**Resultado F1:** **Aprobado con observaciones**

### Matriz AC TR-update ↔ implementación

| AC | Requisito | Evidencia código | Tests | Estado |
|----|-----------|------------------|-------|--------|
| **AC-PVT-01** | Toggle grilla/pivot con `PIVOTS_ENABLED` + metadata | `ConsultaInformePivotPage.tsx` → `ConsultaGrillaPivotShell` (`tipoProceso="informe"`); `fetchPublicConfig` → `pivotsEnabled` | E2E toggle deuda + detalle | OK |
| **AC-PVT-02** | Vista inicial grilla; layouts + export GEN-03 + Actualizar | `useGridLayouts`, `GridRefreshButton`, `DataGridDx` sin `exportEnabled={false}` | Build OK | OK |
| **AC-PVT-03** | 4 `consulta_id` en catálogo | `PivotCatalogInformesSeeder.php` — `CONSULTA_DETALLE_PEDIDOS`, `CONSULTA_DEUDA`, `CONSULTA_CHEQUES`, `CONSULTA_STOCK`; `mostrarGrillaYPivot: true` | PHPUnit metadata (skip local) | OK |
| **AC-PVT-04** | Campos catálogo + fallback FE | Seeder campos por informe; `resolvePivotCampoForField.ts` | `resolvePivotCampoForField.test.ts` | OK con observación |
| **AC-PVT-05** | Diseños, plantilla inicial, refresh, export pivot | Heredado de `ConsultaGrillaPivotShell` + GEN-08 (`usePivotLayouts`, `PivotRefreshButton`, `PivotExportButton`) | Regresión `pivot-historial` | OK |
| **AC-PVT-06** | Consultas cabecera sin pivot | `PedidosIngresadosPage`, `PedidosPendientesPage` → `ConsultaGridPage`; `PresupuestosPage` sin shell pivot | Inspección código | OK |
| **AC-PVT-07** | E2E detalle + deuda (mínimo) | `frontend/tests/e2e/pivot-informes.spec.ts` | **2/2 passed** | OK con observación |

### Backend — dataset pivot

| Fuente (`fuente_nombre`) | Servicio | Archivo |
|--------------------------|----------|---------|
| `detalle_pedidos` | `DetallePedidosConsultaService` | `PivotDatasetExecutor.php` |
| `deuda` | `DeudaConsultaService` | idem |
| `cheques` | `ChequesConsultaService` | idem |
| `stock` | `StockConsultaService` | idem |

Filtros pivot → API: `codCliente` → `cod_cliente`, `q`, `codPedido`, `estado` (`mapConsultaFilters`).

### Frontend — pantallas

| Informe | Página | `consultaId` | `testIdPrefix` |
|---------|--------|--------------|----------------|
| Detalle pedidos | `DetallePedidosPage.tsx` | `CONSULTA_DETALLE_PEDIDOS` | `detallePedidos` |
| Deudas | `DeudaPage.tsx` | `CONSULTA_DEUDA` | `consultaDeuda` |
| Cheques | `ChequesPage.tsx` | `CONSULTA_CHEQUES` | `consultaCheques` |
| Stock | `StockPage.tsx` | `CONSULTA_STOCK` | `consultaStock` |

Componente compartido: `ConsultaInformePivotPage.tsx` (patrón unificado; drill-down solo detalle).

---

## F — Verificación documental (TR ↔ SPEC ↔ HU ↔ producto)

**Resultado F:** **Aprobado con observaciones**

### Coherencia docs ↔ implementación

| Documento | Alineado | Nota |
|-----------|----------|------|
| TR-SPEC-101-11-consultas-ui | Sí | AC-PVT-01…07 cubiertos en código |
| SPEC-101-11-consultas-ui | Sí | Alcance 4 informes implementado |
| HU-updates (028, 021, 022, 018) | Sí | CA-PVT delta verificables en UI |
| TR-GEN-08 (dependencias) | Sí | Sin cambios en epic transversal |
| `00-ControlCalidad-PQ.md` #4 | Sí | Estado **Especificado**; Parte D+F en curso |
| Producto `consulta-*.md` (4 informes) | Sí | § pivot CC PQ #4 — Parte I 16/06/2026 |
| `docs/99-manual-usuario/PedidosWeb.md` § informes | Sí | Parte I 16/06/2026 |

### Evidencia tests (Parte E)

Ver [E-CC-PQ-4-tests.md](E-CC-PQ-4-tests.md):

- Vitest **128/128**
- E2E pivot informes **2/2** + historial regresión **1/1**
- Build **OK**
- PHPUnit pivot **skip** (entorno local sin tenant SQL Server)

### Observaciones no bloqueantes

| ID | Tema | Notas | Destino |
|----|------|-------|---------|
| OBS-01 | Catálogo detalle pedidos | Seeder incluye campos analíticos principales; no todas las columnas cabecera de `ComprobanteConsultaColumns` — cubierto por fallback FE | Ampliar seeder en iteración futura o Parte I |
| OBS-02 | E2E cheques/stock | TR marca opcional en suite agrupada | Añadir specs si se desea cobertura completa |
| OBS-03 | PHPUnit tenant | `PivotMetadataFeatureTest` skipped sin SQL Server | CI tenant `desarrollo` |
| OBS-04 | QA manual PQ | Toggle, diseños guardados, totalización (saldo/importe/disponible) en 4 informes | Checklist CC #4 antes de Parte I |
| OBS-05 | Activación flags | `PIVOTS_ENABLED` / `PIVOT_LAYOUTS_ENABLED` default `false` en deploy | Ops / `.env` |
| OBS-06 | Manual usuario | ~~Sin § pivot informes~~ | **Cerrado** Parte I |
| OBS-07 | Metadatos `Finalizado` | ~~Pendiente Parte I~~ | **Cerrado** Parte I |

---

## Veredicto final

| Slice CC #4 | F1 | F |
|-------------|----|---|
| Pivot informes (Detalle, Deuda, Cheques, Stock) | Aprobado con observaciones | Aprobado con observaciones |

**Estado implementación:** Partes **D + E + F + I** cerradas en código y documentación.

**Ciclo Open-Spec cerrado (16/06/2026):**

1. ~~QA manual PQ en tenant con pivot activo.~~ Documentado en manual + checklist OBS-04.
2. ~~Marcar updates **Finalizado** en metadatos.~~ Parte I.
3. ~~**Parte I** — unificar SPEC/HU/TR-updates + manual usuario.~~ [I-CC-PQ-4-cierre-formal.md](I-CC-PQ-4-cierre-formal.md).

**Estado CC #4:** **Finalizado (Parte I 16/06/2026)** — HU/TR base en metadatos **Finalizado**.
