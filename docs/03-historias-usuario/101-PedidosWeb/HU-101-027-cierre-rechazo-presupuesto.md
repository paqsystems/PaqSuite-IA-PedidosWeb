# HU-101-027 — Cierre o rechazo de presupuesto (estado 98)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-027-cierre-rechazo-presupuesto |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md), [SPEC-101-12](../../05-open-spec/101-PedidosWeb/SPEC-101-12-tratativas-cierre.md) |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **cerrar o rechazar un presupuesto activo con motivo**,  
para **retirarlo del circuito activo sin borrarlo**.

## Reglas de negocio

1. Solo presupuesto **estado 99**.
2. Cabecera pasa a **98**; registro en `pq_pedidosweb_presupuestos_cierres`.
3. Rechazo exige **motivo negativo** elegido en UI desde `pq_pedidosweb_motivos_cierre` (**no** usa `CodMotivoCierreExitoso`; ese parámetro es solo para conversión exitosa — HU-101-013).
4. **Sin** cierre parcial/positivo (AMB-C05).
5. **Sin** eliminación física.
6. Distinto de conversión a pedido (HU-101-013) aunque ambos terminan en 98.

## Criterios de aceptación

- [ ] **CA-01:** Rechazo con motivo obligatorio deja presupuesto en 98.
- [ ] **CA-02:** Consulta cerrados (HU-101-016) muestra datos del cierre.
- [ ] **CA-03:** Presupuesto 98 no editable ni convertible desde consulta activos.

## Veredicto B1

**Lista para TR** (SPEC-101-04/05).
