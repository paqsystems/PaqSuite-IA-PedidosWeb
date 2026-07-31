# HU-101-029 — Proceso Excel pedido individual (update — cantidad)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-029-proceso-excel-pedido-individual](../../101-PedidosWeb/HU-101-029-proceso-excel-pedido-individual.md) |
| **SPEC update** | [SPEC-101-16-importacion-pedido-individual-excel-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Criterios de aceptación

- [ ] **CA-CC10-X01:** Columna Excel `cantidad` se interpreta según `CargaUnidadesVenta` (misma regla que modal renglón).
- [ ] **CA-CC10-X02:** Renglones hidratados tienen `cantidad` y `cantidad_venta` coherentes; importes desde `cantidad`.
