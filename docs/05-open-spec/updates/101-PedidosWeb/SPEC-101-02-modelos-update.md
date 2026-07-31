# SPEC-101-02 — Modelos (update — equivalencia_ventas / cantidad_venta)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-02-modelos-update |
| **SPEC base** | [SPEC-101-02-modelos](../../101-PedidosWeb/SPEC-101-02-modelos.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-07-30 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10 — **30/07/2026** |
| **Dependencias** | Migración aditiva (sin DROP); SPEC-001-04-update (`CargaUnidadesVenta`) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Extender el modelo de datos con equivalencia de unidades de venta en artículos y cantidad de venta en el detalle de pedidos/presupuestos.

## Nomenclatura canónica (Parte G)

| Concepto | Nombre canónico | Nota CC |
|----------|-----------------|---------|
| Equivalencia en artículos | `equivalencia_ventas` (plural) | CC usa también `equivalencia_venta` en fórmulas → **unificar a plural** |
| Cantidad venta en detalle | `cantidad_venta` | — |
| Tabla detalle | `pq_pedidosweb_pedidosdetalle` | CC dice `PQ_PEDIDOSWEB_DETALLEPEDIDOS` (nombre informal) |

## In scope (delta)

### `pq_pedidosweb_articulos`

| Columna | Tipo | Default | Regla |
|--------|------|---------|-------|
| `equivalencia_ventas` | decimal | `1` | Si valor leído es `0` o nulo en runtime → tratar como **1** al convertir |

### `pq_pedidosweb_pedidosdetalle`

| Columna | Tipo | Default / backfill | Regla |
|--------|------|--------------------|-------|
| `cantidad_venta` | decimal | igual a `cantidad` en filas existentes | Persistir siempre junto con `cantidad` |

### Modelos Eloquent

- Casts decimal; fillable/guarded según convención vigente.
- Sin lógica de conversión en el modelo (va a services / capa carga).

## Fuera de scope

- DROP / recreate de tablas.
- Cambiar semántica de `cantidad` como base de importes.

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| TR modelos | [TR-SPEC-101-02-modelos-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-02-modelos-update.md) |
| Consumo UI/API | SPEC-101-10 / 101-04 / 101-07 (updates) |

## Definición de listo (update)

- [ ] Migración/SQL idempotente `ADD` columnas si no existen.
- [ ] Backfill `cantidad_venta = cantidad` donde null.
- [ ] Modelos/casts actualizados; tests de esquema o feature mínimos.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 30/07/2026 | CC PQ #10 | Columnas `equivalencia_ventas` + `cantidad_venta` |
| 30/07/2026 | Parte G | Volcado SPEC-update |
