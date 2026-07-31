# TR-SPEC-101-07 — Consultas API (update — cantidadVenta)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-07-consultas-api](../../101-PedidosWeb/TR-SPEC-101-07-consultas-api.md) |
| **HU update** | [HU-101-028-consulta-detalle-pedidos-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos-update.md) |
| **SPEC update** | [SPEC-101-07-consultas-api-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-07-consultas-api-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. Incluir `cantidad_venta` → `cantidadVenta` en query/mapper de detalle-pedidos.
2. Actualizar `consulta-detalle-pedidos.md`.
3. PHPUnit/feature assert campo presente.

## AC técnicos

- [ ] **AC-CC10-T-API1:** Respuesta detalle incluye `cantidadVenta`.
