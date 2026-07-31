# HU-101-028 — Consulta detalle de pedidos (update — cantidad_venta)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-028-consulta-detalle-pedidos](../../101-PedidosWeb/HU-101-028-consulta-detalle-pedidos.md) |
| **SPEC update** | [SPEC-101-07-consultas-api-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-07-consultas-api-update.md) · [SPEC-101-11-consultas-ui-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Narrativa (delta)

Como **usuario comercial**, quiero **ver `cantidad_venta` junto a `cantidad`** en el informe Detalle de Pedidos, para **analizar unidades de venta por renglón**.

## Criterios de aceptación

- [ ] **CA-CC10-D01:** API incluye `cantidadVenta`.
- [ ] **CA-CC10-D02:** Grilla (y kardex mobile si aplica) muestra columna cantidad venta; no elimina `cantidad`.
