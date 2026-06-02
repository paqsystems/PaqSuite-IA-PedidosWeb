# HU-101-024 — Conversión de pedido a presupuesto

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-024-conversion-pedido-presupuesto |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md), SPEC madre §5.1 |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **convertir un pedido no descargado en presupuesto con “Grabar presupuesto” en la pantalla de carga**,  
para **revertir la operación a cotización**.

## Reglas de negocio

1. Solo pedido **estado 0**; no en 1, 2, 99 ni **-1** bloqueado.
2. Resultado **presupuesto estado 99**.
3. Trazabilidad `cod_pedido_origen` (o equivalente).
4. Mismas validaciones que alta presupuesto.
5. Tratamiento del pedido origen según regla de negocio en TR (anular/eliminar 0 o mantener — **cerrar en TR** sin inventar: típicamente pedido origen pasa a estado que impida doble uso; producto §15.2 solo dice permitir conversión — TR debe leer producto).

From product §15.2: "Debe permitirse mientras el pedido no haya sido descargado" - doesn't say delete original. I'll note in HU: pedido origen queda no reutilizable — TR define si elimina 0 o marca; common pattern is delete 0 or leave - SPEC 5.1 says convert creates new presupuesto 99. I'll say: pedido origen deja de estar en 0 (eliminación o anulación según TR alineado a producto).

Actually re-read SPEC 5.1 - only about creating presupuesto 99 with traceability. I'll leave CA: pedido origen no disponible para edición posterior.

## Criterios de aceptación

- [ ] **CA-01:** Conversión desde pedido 0 crea presupuesto 99.
- [ ] **CA-02:** Intento desde pedido 1 → rechazo.
- [ ] **CA-03:** Trazabilidad al pedido origen consultable.

## Veredicto B1

**Lista para TR**.
