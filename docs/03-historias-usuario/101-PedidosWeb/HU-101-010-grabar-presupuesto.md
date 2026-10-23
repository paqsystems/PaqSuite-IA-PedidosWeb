# HU-101-010 — Grabación de presupuesto (estado 99)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-010-grabar-presupuesto |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md), [SPEC-101-10](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **Última actualización** | 2026-09-13 (Parte I) |
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
5. **CC PQ #12:** Misma regla de sync de leyendas dirty que HU-101-009 al grabar presupuesto.
6. **RN-CC13-GP01:** Aplicar el mismo recorte a 60 caracteres Unicode que HU-101-009 al grabar presupuesto; una leyenda larga no provoca error 4xx.

## Criterios de aceptación

- [ ] **CA-01:** Presupuesto grabado queda en estado 99.
- [ ] **CA-02:** Visible en consulta presupuestos activos (HU-101-016).
- [ ] **CA-03:** No existe acción eliminar presupuesto en UI/API.
- [x] **CA-CC12-GP01:** Misma regla de sync de leyendas dirty que HU-101-009-update al grabar presupuesto.
- [ ] **CA-CC13-GP01:** Grabar presupuesto con leyenda de 61 caracteres → persiste recortada a 60.

## Historial CC PQ #13 (01/09/2026) — Parte I 13/09/2026

Recorte defensivo de leyendas a 60 caracteres al grabar presupuesto, en paridad con pedido (RN-CC13-GP01, CA-CC13-GP01). Unificación de `HU-101-010-grabar-presupuesto-update`.

## Historial CC PQ #12 (28/08/2026) — Parte I 30/08/2026

Sync de leyendas dirty al grabar presupuesto (RN-5, CA-CC12-GP01). Unificación delta `HU-101-010-grabar-presupuesto-update` (archivo eliminado en Parte I).

## Veredicto B1

**Lista para TR** (SPEC-101-04/05/10).
