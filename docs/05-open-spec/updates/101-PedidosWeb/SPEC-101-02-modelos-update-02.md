# SPEC-101-02 — Modelos (update-02 — artículo stockeable)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-02-modelos-update-02 |
| **SPEC base** | [SPEC-101-02-modelos](../../101-PedidosWeb/SPEC-101-02-modelos.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-28 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12 — **28/08/2026** |
| **Dependencias** | [SPEC-001-04-configuracion-global-update-01](../001-Generaliddes/SPEC-001-04-configuracion-global-update-01.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Identificar artículos **no stockeables** en `pq_pedidosweb_articulos` para que la búsqueda de artículos en carga no muestre stock y la consulta de stock los excluya.

## In scope (delta)

| Campo | Valor |
|-------|--------|
| **Tabla** | `pq_pedidosweb_articulos` |
| **Columna canónica** | `stockeable` |
| **Tipo** | bit / boolean |
| **Default** | `1` / `true` (stockeable) |
| **Semántica** | `false` = artículo no stockeable |

- Migración / ALTER idempotente + modelo Eloquent.
- Alimentación: sync ERP / archivo de artículos (fuera de PedidosWeb UI).
- **Supuesto documentado:** si el ERP ya usa otro nombre de columna, mapear en el adapter de sync; en PedidosWeb el contrato interno es `stockeable`.

## Fuera de scope

- ABM web del flag.
- Cambiar el parámetro `IncluyeArticulosNoStockeables` (informativo; SPEC-001-04-update-01).

## HU / TR derivadas

| Artefacto | Ruta |
|-----------|------|
| TR modelos | [TR-SPEC-101-02-modelos-update-02](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-02-modelos-update-02.md) |
| Consumo stock/carga | SPEC-101-07/10/11 update-01 + HU-101-018 / HU-101-005 |

## Definición de listo (update)

- [ ] Columna + default en esquema tenant.
- [ ] Modelo / OpenAPI artículo si se expone.
- [ ] Documentado en modelo de datos §3.4.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 28/08/2026 | CC PQ #12 | Flag `stockeable` en artículos |
| 28/08/2026 | Parte G | Volcado SPEC-update-02 |
