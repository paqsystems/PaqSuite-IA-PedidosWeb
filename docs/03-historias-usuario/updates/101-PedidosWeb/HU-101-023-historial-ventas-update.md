# HU-101-023 — Historial ventas (update — rango de fechas)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-023-historial-ventas](../../101-PedidosWeb/HU-101-023-historial-ventas.md) |
| **SPEC update** | [SPEC-101-07-consultas-api-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-07-consultas-api-update-01.md) · [SPEC-101-11-consultas-ui-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Criterios de aceptación

- [ ] **CA-CC12-H01:** UI con fecha desde / hasta (default vacío).
- [ ] **CA-CC12-H02:** Sin fechas → solo filtro `DiasVentasDetalladas` (+ cliente/visibilidad).
- [ ] **CA-CC12-H03:** Solo desde → filtra hacia adelante; solo hasta → hacia atrás; ambas → rango.
