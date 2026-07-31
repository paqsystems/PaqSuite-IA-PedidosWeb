# TR-SPEC-101-11 — Consultas UI (update — columna cantidadVenta)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-11-consultas-ui](../../101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md) |
| **HU update** | [HU-101-028-consulta-detalle-pedidos-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos-update.md) |
| **SPEC update** | [SPEC-101-11-consultas-ui-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. Columna `cantidadVenta` en `DetallePedidosConsultaColumns` (+ kardex mobile si aplica).
2. i18n `consultas.detalle.column.cantidadVenta` (es/en u otros locales activos).

## AC técnicos

- [ ] **AC-CC10-T-UI1:** Columna visible; formato decimal.
