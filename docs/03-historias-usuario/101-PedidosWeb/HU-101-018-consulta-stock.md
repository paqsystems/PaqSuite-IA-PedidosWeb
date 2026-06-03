# HU-101-018 — Consulta de stock

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-018-consulta-stock |
| **SPEC origen** | [SPEC-101-07](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), [SPEC-101-11](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **consultar stock por artículo**,  
para **decidir cantidades en cargas y atención al cliente**.

## Reglas de negocio

1. No restringida por cliente; búsqueda por código/descripción y opción “todos”.
2. Campos y fórmulas: **[consulta-stock.md](../../02-producto/PedidosWeb/consulta-stock.md)** (fuente de verdad).
3. `fecha_proceso` en carátula (producto §17.7).

## Criterios de aceptación

- [ ] **CA-01:** Búsqueda devuelve resultados paginados.
- [ ] **CA-02:** Carátula muestra `fecha_proceso` única del lote.
- [ ] **CA-03:** Grilla estándar GEN-03 + export Excel.

## Veredicto B1

**Lista para TR**.
