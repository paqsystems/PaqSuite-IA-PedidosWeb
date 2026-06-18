# HU-101-018 — Consulta de stock

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-018-consulta-stock |
| **SPEC origen** | [SPEC-101-07](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), [SPEC-101-11](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Producto** | [consulta-stock.md](../../02-producto/PedidosWeb/consulta-stock.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado (Parte I — CC PQ #4) |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **consultar stock por artículo**,  
para **decidir cantidades en cargas y atención al cliente**, y **pivotar disponible neto por artículo** cuando el tenant lo habilite.

## Reglas de negocio

1. No restringida por cliente; búsqueda por código/descripción y opción “todos”.
2. Campos y fórmulas: **[consulta-stock.md](../../02-producto/PedidosWeb/consulta-stock.md)** (fuente de verdad).
3. `fecha_proceso` en carátula (producto §17.7).
4. **CC PQ #4:** pivot opcional (`CONSULTA_STOCK`); fórmulas `disponibleNeto` idénticas a grilla; búsqueda `q` vía refresh servidor.

## Criterios de aceptación

- [x] **CA-01:** Búsqueda devuelve resultados paginados.
- [x] **CA-02:** Carátula muestra `fecha_proceso` única del lote.
- [x] **CA-03:** Grilla estándar GEN-03 + export Excel.
- [x] **CA-PVT-01:** Toggle grilla/pivot en `/consultas/stock`.
- [x] **CA-PVT-02:** Fórmulas disponible neto idénticas a producto.
- [x] **CA-PVT-03:** Columnas base opcionales en catálogo cuando artículo tiene `base`.
- [x] **CA-PVT-04:** Actualizar recarga dataset servidor en grilla y pivot.

## Historial CC PQ #4 (10/06/2026) — Parte I 16/06/2026

Unificación delta CC PQ #4 (archivo `*-update` eliminado en Parte I).

## Veredicto B1

**Lista para TR**.
