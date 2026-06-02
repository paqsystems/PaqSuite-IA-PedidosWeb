# HU-101-012 — Eliminación de pedido ingresado

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-012-eliminar-pedido |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario con permiso**,  
quiero **eliminar un pedido aún no descargado**,  
para **anular cargas erróneas**.

## Reglas de negocio

1. Solo estado **0**; borrado físico cabecera+detalle.
2. Parámetro `NOeliminaPedido` puede prohibir acción (SPEC-001-04).
3. **No** aplica a presupuestos (usar cierre 98).
4. Confirmación modal (patrón ABM/GEN-03).
5. **No** envía mail (producto §14).

## Criterios de aceptación

- [ ] **CA-01:** DELETE pedido 0 elimina registros y desaparece de consulta.
- [ ] **CA-02:** DELETE pedido 1/2 → error de negocio.
- [ ] **CA-03:** No hay endpoint DELETE presupuesto.

## Veredicto B1

**Lista para TR** (SPEC-101-05).
