# SPEC-101-11 — Consultas UI (update — columna cantidad_venta)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-11-consultas-ui-update |
| **SPEC base** | [SPEC-101-11-consultas-ui](../../101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-07-30 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10 — **30/07/2026** (corrección: Detalle de Pedidos) |
| **Dependencias** | [SPEC-101-07-update](SPEC-101-07-consultas-api-update.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Agregar en UI la columna **cantidad venta** en **Detalle de Pedidos**, alineada al contrato API del SPEC-101-07-update.

## In scope (delta)

- `DetallePedidosConsultaColumns` (y kardex mobile si aplica): columna `cantidadVenta` (decimal `#,##0.00`), i18n `consultas.detalle.column.cantidadVenta`.
- Visible inicialmente sugerida junto a `cantidad` (no reemplaza `cantidad`).

## Fuera de scope

- Columnas nuevas en listados de cabecera (ingresados / pendientes / presupuestos).
- Pivot obligatorio de `cantidadVenta`.
- Cambiar acciones de consulta.

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| HU-101-028 | [HU-101-028-consulta-detalle-pedidos-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos-update.md) |
| TR UI | [TR-SPEC-101-11-consultas-ui-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-11-consultas-ui-update.md) |

## Definición de listo (update)

- [ ] Columna en grilla detalle + i18n.
- [ ] Mobile kardex muestra el campo si el proceso está en native.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 30/07/2026 | CC PQ #10 | Columna UI `cantidadVenta` |
| 30/07/2026 | Parte G | Volcado SPEC-update |
| 30/07/2026 | PQ | Corrección: solo Detalle de Pedidos |
