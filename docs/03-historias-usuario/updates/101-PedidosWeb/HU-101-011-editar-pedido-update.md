# HU-101-011 — Editar pedido (update — no reescribir leyendas sin dirty)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-011-editar-pedido](../../101-PedidosWeb/HU-101-011-editar-pedido.md) |
| **SPEC update** | [SPEC-101-04-services-pedidos-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-04-services-pedidos-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Criterios de aceptación

- [ ] **CA-CC12-E01:** En edición, snapshot al abrir; grabar sin tocar leyenda no modifica maestro aunque el texto del pedido difiera del maestro actual.
- [ ] **CA-CC12-E02:** Si el usuario modifica la leyenda en la sesión de edición, sí actualiza el maestro (si `ClienteLeyendaN`).
