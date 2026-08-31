# SPEC-101-07 — Consultas API (update-01 — historial fechas + stock no stockeable)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-07-consultas-api-update-01 |
| **SPEC base** | [SPEC-101-07-consultas-api](../../101-PedidosWeb/SPEC-101-07-consultas-api.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-28 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12 — **28/08/2026** |
| **Dependencias** | [SPEC-101-02-modelos-update-02](SPEC-101-02-modelos-update-02.md); `DiasVentasDetalladas` |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

1. **Historial de ventas:** filtros opcionales `fecha_desde` / `fecha_hasta` sobre `fecha_emi`, **además** del techo `DiasVentasDetalladas` y del filtro de cliente/visibilidad.
2. **Consulta stock:** excluir artículos con `stockeable = false`.
3. **Deuda (soporte carga):** el listado existente filtrado por `cod_cliente` alcanza para el modal de carga; si hace falta resumen agregado, documentarlo en implementación sin nuevo permiso.

## In scope (delta)

### Historial — query

| Parámetro | Regla |
|-----------|--------|
| (ninguno) | Solo filtro `DiasVentasDetalladas` vigente |
| solo `fecha_desde` | `fecha_emi >= fecha_desde` (y resto de filtros vigentes) |
| solo `fecha_hasta` | `fecha_emi <= fecha_hasta` |
| ambos | rango inclusivo `[fecha_desde, fecha_hasta]` |

Defaults vacíos: no aplicar esos predicados. Formato fecha: `YYYY-MM-DD` (o ISO date).

### Stock

- `GET /consultas/stock`: no devolver filas cuyo artículo tenga `stockeable = false` (join a `pq_pedidosweb_articulos`).
- Artículos sin fila de artículo o `stockeable` null: tratar como stockeable (`true`) salvo decisión contraria en TR.

### Deuda para modal carga

- Reutilizar `GET /consultas/deuda?cod_cliente=…` (visibilidad GEN-02). Sin export/pivot en el consumidor UI de carga.

## Fuera de scope

- Eliminar `DiasVentasDetalladas`.
- Pivot deuda/stock.

## HU / TR derivadas

| Artefacto | Ruta |
|-----------|------|
| HU-101-023 | [HU-101-023-historial-ventas-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-023-historial-ventas-update.md) |
| HU-101-018 | [HU-101-018-consulta-stock-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-018-consulta-stock-update.md) |
| TR-101-07 | [TR-SPEC-101-07-consultas-api-update-01](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-07-consultas-api-update-01.md) |

## Definición de listo (update)

- [ ] Query historial con las 4 combinaciones de fechas.
- [ ] Stock sin no-stockeables.
- [ ] OpenAPI query params documentados.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 28/08/2026 | CC PQ #12 | Rango fechas historial + exclusión stock |
| 28/08/2026 | Parte G | Volcado SPEC-update-01 |
