# Parte E — CC PQ #4 (10/06/2026) — Pivot informes

> **Verificación F:** [F-CC-PQ-4-pivot-informes.md](F-CC-PQ-4-pivot-informes.md)

## Alcance

Validación automatizada tras **Parte D** ([TR-SPEC-101-11-consultas-ui](TR-SPEC-101-11-consultas-ui.md)):

| Entregable | Foco |
|------------|------|
| Pivot informes 101 | `ConsultaInformePivotPage` + catálogo 4 consultas + `PivotDatasetExecutor` |

**Fecha ejecución:** 11/06/2026  
**Rama / build:** `v1.1.0` (working tree local)

---

## Backend — PHPUnit

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=PivotMetadataFeatureTest` | **11 skipped** — tenant `desarrollo` / SQL Server no disponible en `.env` local |

**Tests añadidos (CC #4):** `testInformesMetadataReturnsPivotEnabled` — data provider `CONSULTA_DETALLE_PEDIDOS`, `CONSULTA_DEUDA`, `CONSULTA_CHEQUES`, `CONSULTA_STOCK`.

**Nota:** Se ejecutan en CI/tenant con tablas `pq_pivots_*` y seeder `PivotCatalogPilotSeeder` (incluye `PivotCatalogInformesSeeder`).

---

## Frontend — Vitest

| Comando | Resultado |
|---------|-----------|
| `npm run test -- --run` | **128 passed** (43 archivos) |

**Relevantes pivot (sin regresión):** `resolvePivotCampoForField`, `resolvePivotAggregations`, `resolvePivotCoexistence`, `applyPivotBaseToFields`, `pivotExportUtils`.

---

## Frontend — Playwright E2E

| Comando | Resultado |
|---------|-----------|
| `npx playwright test pivot-informes.spec.ts` | **2 passed** |
| `npx playwright test pivot-informes.spec.ts pivot-historial.spec.ts` | **3 passed** |

| Escenario | Archivo |
|-----------|---------|
| Deuda toggle grilla → pivot | `pivot-informes.spec.ts` |
| Detalle pedidos toggle grilla → pivot | `pivot-informes.spec.ts` |
| Historial (regresión piloto) | `pivot-historial.spec.ts` |

**No automatizado en E2E:** Cheques, Stock (opcional según TR-update AC-PVT-07).

---

## Build

| Comando | Resultado |
|---------|-----------|
| `npm run build` | **OK** (tsc + vite) |

---

## Seeder (no destructivo)

| Comando | Resultado |
|---------|-----------|
| `php artisan db:seed --class=Database\Seeders\Pivots\PivotCatalogPilotSeeder` | **OK** — upsert 4 consultas informes |

---

## Resumen Parte E

| Capa | Estado |
|------|--------|
| Vitest | OK |
| E2E pivot informes (mínimo) | OK |
| Build frontend | OK |
| PHPUnit pivot feature | Skip entorno local (esperado) |

**Pendiente QA manual PQ:** toggle + diseños + totalización en tenant con `PIVOTS_ENABLED=true` y datos reales.
