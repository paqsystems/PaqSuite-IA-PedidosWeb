# HU-101-023 — Historial de ventas

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-023-historial-ventas |
| **SPEC origen** | [SPEC-101-07](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), SPEC madre §5.2 |
| **Prioridad** | Must |
| **Estado** | Finalizado (Parte I CC PQ #12) |
| **Última actualización** | 2026-08-30 (Parte I — CC PQ #12) |
| **B1** | Enriquecida (2026-06-01) |
| **Dependencias** | Parámetro `DiasVentasDetalladas` (SPEC-001-04) |

## Narrativa

Como **usuario comercial**,  
quiero **ver el historial de ventas del período configurado**,  
para **analizar comportamiento del cliente**.

## Reglas de negocio

Fuente de verdad: **[consulta-historial-ventas.md](../../02-producto/PedidosWeb/consulta-historial-ventas.md)**.

1. Período = parámetro **`DiasVentasDetalladas`** (`fecha_emi >= hoy - N días).
2. Columnas según tabla ERP `pq_pedidosweb_ventadetallada` (excepto `fecha_proceso`, `id_gva53`).
3. Detalle en **modal** DevExtreme (misma fila, todas las columnas).
4. `fecha_proceso` en carátula.
5. **CC PQ #12:** UI con fecha desde / hasta (default vacío). Sin fechas → solo filtro `DiasVentasDetalladas` (+ cliente/visibilidad). Solo desde → filtra hacia adelante; solo hasta → hacia atrás; ambas → rango.

## Criterios de aceptación

- [ ] **CA-01:** Listado respeta días del parámetro (o default documentado en TR).
- [ ] **CA-02:** Doble clic o acción ver abre modal con detalle.
- [ ] **CA-03:** Export Excel desde grilla principal.
- [ ] **CA-CC12-H01:** UI con fecha desde / hasta (default vacío).
- [ ] **CA-CC12-H02:** Sin fechas → solo filtro `DiasVentasDetalladas` (+ cliente/visibilidad).
- [ ] **CA-CC12-H03:** Solo desde → filtra hacia adelante; solo hasta → hacia atrás; ambas → rango.

## Historial CC PQ #12 (28/08/2026) — Parte I 30/08/2026

Filtro por rango de fechas desde/hasta (RN-5, CA-CC12-H01…H03). Unificación delta `HU-101-023-historial-ventas-update` (archivo eliminado en Parte I).

## Veredicto B1

**Lista para TR**.
