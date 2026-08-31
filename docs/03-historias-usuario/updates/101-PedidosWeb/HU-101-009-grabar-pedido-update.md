# HU-101-009 — Grabar pedido (update — sync leyendas)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-009-grabar-pedido](../../101-PedidosWeb/HU-101-009-grabar-pedido.md) |
| **SPEC update** | [SPEC-101-04-services-pedidos-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-04-services-pedidos-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Criterios de aceptación

- [ ] **CA-CC12-G01:** Al grabar pedido, si una leyenda N dirty y `ClienteLeyendaN=true`, actualiza `clientes.leyenda_N`.
- [ ] **CA-CC12-G02:** Si la leyenda no se modificó en la sesión, no actualiza el maestro (escenario d del CC).
