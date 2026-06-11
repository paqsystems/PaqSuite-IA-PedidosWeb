# TR-SPEC-101-11 — Consultas UI (update — pivot informes)

| Campo | Valor |
|-------|--------|
| **ID** | TR-SPEC-101-11-consultas-ui-update |
| **TR base** | [TR-SPEC-101-11-consultas-ui](../../101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md) |
| **SPEC update** | [SPEC-101-11-consultas-ui-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #4 — **10/06/2026** |
| **Parte E** | [E-CC-PQ-4-tests.md](../../101-PedidosWeb/E-CC-PQ-4-tests.md) |
| **Parte F** | [F-CC-PQ-4-pivot-informes.md](../../101-PedidosWeb/F-CC-PQ-4-pivot-informes.md) (11/06/2026 — aprobado con observaciones) |
| **Dependencias** | TR-GEN-08-motor-metadata-pivots; TR-GEN-08-pivotgrid-visualizacion; TR-GEN-08-layouts-pivot; TR-GEN-08-exportacion-pivot |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## 1) Resumen

Adoptar `ConsultaGrillaPivotShell` en cuatro informes PedidosWeb, registrar catálogo pivot y mantener paridad con el piloto **Historial ventas** (`HistorialVentasPage`).

## 2) Criterios de aceptación (AC)

- **AC-PVT-01:** Cada pantalla lista expone toggle grilla/pivot con `PIVOTS_ENABLED` + metadata `pivot_habilitado`.
- **AC-PVT-02:** Vista inicial grilla; grilla conserva layouts, export Excel GEN-03 y botón Actualizar existentes.
- **AC-PVT-03:** Cuatro `consulta_id` en `pq_pivots_consultas` con `pivot_habilitado = true`, `mostrarGrillaYPivot = true` en `configuracion_general_json`.
- **AC-PVT-04:** `pq_pivots_campos` incluye **todas** las columnas del dataset JSON de cada informe (paridad Historial ventas + fallback sintético FE).
- **AC-PVT-05:** Diseños pivot (`pq_pivots_config`), plantilla inicial, `pivotRefresh`, export pivot (GEN-08).
- **AC-PVT-06:** Pedidos ingresados, pendientes, presupuestos **sin** shell pivot.
- **AC-PVT-07:** E2E: al menos detalle pedidos + deuda con toggle; opcional cheques/stock en suite agrupada.

## 3) Catálogo pivot (backend)

Extender `database/seeders/Pivots/PivotCatalogPilotSeeder.php` (o seeder dedicado `PivotCatalogInformesSeeder`) con:

| consulta_id | procedimiento_host | fuente_nombre | admite_drilldown |
|-------------|-------------------|---------------|------------------|
| `CONSULTA_DETALLE_PEDIDOS` | `pw_detallepedidos` | `detalle_pedidos` | true |
| `CONSULTA_DEUDA` | `pw_deudaclientes` | `deuda` | false |
| `CONSULTA_CHEQUES` | `pw_consultacheques` | `cheques` | false |
| `CONSULTA_STOCK` | `pw_consultastock` | `stock` | false |

**Pivot data:** reutilizar servicios existentes (`DetallePedidosConsultaService`, `DeudaConsultaService`, `ChequesConsultaService`, `StockConsultaService`) vía adaptador pivot data (mismo patrón historial — filas planas JSON camelCase).

**Campos:** mapear `dataField` API = columnas grilla actuales; `tipo_dato` coherente; roles incluyen `valor` (policy backend).

### pivotBase JSON (seed)

```json
// Detalle — ejemplo
{ "filas": ["codCliente", "razonSocial"], "columnas": [], "valores": [{"campoId": "cantidad", "agregacion": "sum"}], "mostrarSubtotales": true, "mostrarTotalesGenerales": true }
```

Ajustar por consulta según SPEC-update (deuda: saldo; cheques: importe; stock: disponibleNeto).

## 4) Frontend

### 4.1 Patrón por página

Referencia: `frontend/src/features/consultas/pages/HistorialVentasPage.tsx`.

| Página | Archivo | consultaId | testIdPrefix |
|--------|---------|------------|--------------|
| Detalle pedidos | `DetallePedidosPage.tsx` | `CONSULTA_DETALLE_PEDIDOS` | `detallePedidos` |
| Deuda | `DeudaPage.tsx` | `CONSULTA_DEUDA` | `consultaDeuda` |
| Cheques | `ChequesPage.tsx` | `CONSULTA_CHEQUES` | `consultaCheques` |
| Stock | `StockPage.tsx` | `CONSULTA_STOCK` | `consultaStock` |

Pasos por archivo:

1. Sustituir `ConsultaGridPage` por estructura `ConsultaGrillaPivotShell` + `DataGridDx` en `gridContent`.
2. Mantener `proceso`, `gridId`, columnas y `loadData` / `fetch*` actuales.
3. `refreshToken` + `GridRefreshButton` en toolbar (paridad informes).
4. `tipoProceso="informe"`.
5. Flags: respetar `PIVOTS_ENABLED`, `PIVOT_LAYOUTS_ENABLED`.

### 4.2 Sin cambios API listado

Endpoints `GET /api/v1/consultas/*` permanecen (SPEC-101-07). Pivot usa `GET/POST` metadata/data bajo `/api/v1/pivots/consultas/{consultaId}/…`.

## 5) Tests

| Capa | Acción |
|------|--------|
| PHPUnit | Feature metadata/data por cada `consulta_id` nuevo (skip tenant si aplica) |
| Vitest | Opcional: smoke map campos catálogo |
| Playwright | `pivot-detalle-pedidos.spec.ts` (toggle + field panel); extender mocks como `pivot-historial.spec.ts` |
| Manual PQ | CC #4: F5 + plantilla inicial + totalización precio/saldo/importe |

## 6) Orden de implementación (D)

1. Seeder catálogo + verificar metadata API.
2. Detalle pedidos (mayor complejidad columnas).
3. Deuda, Cheques, Stock (misma plantilla shell).
4. E2E + documentación manual si Parte I.

## 7) Fuera de alcance

- Cambios en `ConsultaGridPage` para consultas cabecera.
- Nuevo epic GEN-08 (ya cerrado F-GEN-08).

## 8) Archivos tocados (checklist)

- `backend/database/seeders/Pivots/PivotCatalogPilotSeeder.php` (o nuevo seeder + registro)
- `frontend/src/features/consultas/pages/DetallePedidosPage.tsx`
- `frontend/src/features/consultas/pages/DeudaPage.tsx`
- `frontend/src/features/consultas/pages/ChequesPage.tsx`
- `frontend/src/features/consultas/pages/StockPage.tsx`
- `frontend/tests/e2e/pivot-*.spec.ts` (nuevos o extendidos)

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 10/06/2026 | CC PQ #4 | TR adopción pivot 4 informes |
| 11/06/2026 | Parte G | Volcado TR-update |
| 11/06/2026 | Parte D | Implementación código |
| 11/06/2026 | Parte E + F | [E-CC-PQ-4-tests](../../101-PedidosWeb/E-CC-PQ-4-tests.md) · [F-CC-PQ-4-pivot-informes](../../101-PedidosWeb/F-CC-PQ-4-pivot-informes.md) — aprobado con observaciones |
