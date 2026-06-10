# HU-101-010 — Grabación de presupuesto (estado 99)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-010-grabar-presupuesto |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md), [SPEC-101-10](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario autorizado**,  
quiero **grabar con el botón “Grabar presupuesto” en la pantalla única de carga**,  
para **dejar un presupuesto activo (99), actualizarlo o convertir un pedido no descargado**.

## Reglas de negocio

1. Acción disparada por botón **Grabar presupuesto** en SPEC-101-10.
2. Resultado según origen: **alta** → **99** nuevo; **edición 99** → **99** mismo código; **pedido 0** → presupuesto **99** nuevo (HU-101-024) con trazabilidad `cod_pedido_origen`.
3. **Sin DELETE** físico de presupuestos (solo cierre **98**).
4. Mismas validaciones que pedido; mail (HU-101-019).

## Criterios de aceptación

- [ ] **CA-01:** Presupuesto grabado queda en estado 99.
- [ ] **CA-02:** Visible en consulta presupuestos activos (HU-101-016).
- [ ] **CA-03:** No existe acción eliminar presupuesto en UI/API.

## Veredicto B1

**Lista para TR** (SPEC-101-04/05/10).
