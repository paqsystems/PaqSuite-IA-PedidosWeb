# HU-101-028 — Consulta detalle de pedidos (update — pivot)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-028-consulta-detalle-pedidos-update |
| **HU base** | [HU-101-028-consulta-detalle-pedidos](../../101-PedidosWeb/HU-101-028-consulta-detalle-pedidos.md) |
| **SPEC update** | [SPEC-101-11-consultas-ui-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #4 — **10/06/2026** |
| **TR update** | [TR-SPEC-101-11-consultas-ui-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-11-consultas-ui-update.md) (Bloque pivot — detalle) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Narrativa (delta)

Como **usuario comercial**, quiero **alternar entre grilla plana y vista pivot** en Detalle de pedidos, para **analizar cantidades e importes por cliente, artículo y período** sin perder el listado operativo actual.

## Alcance incluido (delta)

- Migrar `DetallePedidosPage` de `ConsultaGridPage` a `ConsultaGrillaPivotShell` (patrón `HistorialVentasPage`).
- `consultaId`: `CONSULTA_DETALLE_PEDIDOS`; `tipoProceso`: `informe`; `proceso`/`gridId`: `pw_detallepedidos`.
- Vista inicial **grilla**; toggle pivot según SPEC-001-08.
- Diseños pivot, plantilla inicial, refresh y export pivot (GEN-08).
- Drill-down opcional si `admite_drilldown` en catálogo.

## Fuera de alcance (delta)

- Acciones fila (sigue sin editar/eliminar).
- Pivot en otras consultas cabecera.

## Criterios de aceptación (delta)

- [ ] **CA-PVT-01:** Toggle grilla/pivot visible con permiso y flag `PIVOTS_ENABLED`.
- [ ] **CA-PVT-02:** Grilla conserva columnas y comportamiento CC previos (export, layouts, actualizar).
- [ ] **CA-PVT-03:** Pivot carga metadata `CONSULTA_DETALLE_PEDIDOS` y dataset desde API pivot data.
- [ ] **CA-PVT-04:** `pivotBase` sugiere cliente + artículo en filas; cantidad/importes en valores.
- [ ] **CA-PVT-05:** Clic derecho en valores permite cambiar agregación (number/string según tipo).
- [ ] **CA-PVT-06:** Guardar/cargar diseño pivot (`pq_pivots_config`) operativo.
- [ ] **CA-PVT-07:** ≥ 1 E2E: abrir detalle → toggle pivot → ver field panel.
