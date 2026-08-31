# HU-101-018 — Consulta de stock

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-018-consulta-stock |
| **SPEC origen** | [SPEC-101-07](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), [SPEC-101-11](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Producto** | [consulta-stock.md](../../02-producto/PedidosWeb/consulta-stock.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado (Parte I CC PQ #12) |
| **Última actualización** | 2026-08-31 |
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
5. **CC PQ #12:** La consulta de stock no lista artículos con `stockeable=false`. Kardex mobile / pivot (si aplica) usan el mismo dataset filtrado.

## Criterios de aceptación

- [x] **CA-01:** Búsqueda devuelve resultados paginados.
- [x] **CA-02:** Carátula muestra `fecha_proceso` única del lote.
- [x] **CA-03:** Grilla estándar GEN-03 + export Excel.
- [x] **CA-PVT-01:** Toggle grilla/pivot en `/consultas/stock`.
- [x] **CA-PVT-02:** Fórmulas disponible neto idénticas a producto.
- [x] **CA-PVT-03:** Columnas base opcionales en catálogo cuando artículo tiene `base`.
- [x] **CA-PVT-04:** Actualizar recarga dataset servidor en grilla y pivot.
- [x] **CA-CC12-S01:** La consulta de stock no lista artículos con `stockeable=false`.
- [x] **CA-CC12-S02:** Kardex mobile / pivot (si aplica) usan el mismo dataset filtrado.

## Historial CC PQ #4 (10/06/2026) — Parte I 16/06/2026

Unificación delta CC PQ #4 (archivo `*-update` eliminado en Parte I).

## Historial CC PQ #12 (28/08/2026) — Parte I 30/08/2026

Exclusión de artículos no stockeables en consulta stock (RN-5, CA-CC12-S01…S02). Unificación delta `HU-101-018-consulta-stock-update` (archivo eliminado en Parte I).

## Veredicto B1

**Lista para TR**.
