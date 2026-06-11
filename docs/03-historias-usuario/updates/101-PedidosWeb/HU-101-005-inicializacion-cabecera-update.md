# HU-101-005 — Inicialización cabecera (update CC PQ #5)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-005-inicializacion-cabecera](../../101-PedidosWeb/HU-101-005-inicializacion-cabecera.md) |
| **SPEC update** | [SPEC-101-10-pantalla-carga-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-10-pantalla-carga-update.md) |
| **Estado** | Finalizado |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #5, 09/06/2026 |
| **Última actualización** | 2026-06-09 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Finalizado |

## Narrativa (delta)

Como **usuario que carga un comprobante**, quiero ver en el listbox de artículos el disponible según stock ERP (`stock − comprometido`), **sin** descontar pedidos web en curso, para **alinear la información con el stock maestro** al elegir artículos.

## Criterios de aceptación

- [ ] **CA-CC5-01:** Browse de artículos (`GET /articulos` sin `codigos`) calcula `disponibleNeto = stock − comprometido` (artículo y base).
- [ ] **CA-CC5-02:** No se consulta `pq_pedidosweb_pedidos*` para el disponible del listbox de carga.
- [ ] **CA-CC5-03:** Display: `codigo - descripcion — Disp. X,XX` y `(Y,YY)` si hay base.
- [ ] **CA-CC5-04:** Consulta de stock mantiene disponible neto con `comprometido_web`.
