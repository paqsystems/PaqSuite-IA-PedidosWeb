# SPEC-101-07 — Consultas API (update — cantidad_venta)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-07-consultas-api-update |
| **SPEC base** | [SPEC-101-07-consultas-api](../../101-PedidosWeb/SPEC-101-07-consultas-api.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-07-30 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10 — **30/07/2026** (corrección: Detalle de Pedidos) |
| **Dependencias** | [SPEC-101-02-update](SPEC-101-02-modelos-update.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Exponer el atributo **`cantidad_venta`** (`cantidadVenta` en JSON) en la consulta **Detalle de Pedidos**, sin alterar el resto de atributos existentes.

## Alcance confirmado (PQ 30/07/2026)

| Superficie | Acción |
|------------|--------|
| **Consulta detalle de pedidos** (`GET .../consultas/detalle-pedidos`, HU-101-028) | **Must** — agregar `cantidadVenta` junto a `cantidad` |
| Listados cabecera (ingresados / pendientes / presupuestos) | **Fuera de alcance** |

## In scope (delta)

- API detalle pedidos: campo `cantidadVenta` decimal desde `pq_pedidosweb_pedidosdetalle.cantidad_venta`.
- Metadata de columnas si aplica al contrato de consultas.
- Producto: actualizar `consulta-detalle-pedidos.md`.

## Fuera de scope

- Columnas nuevas en pedidos ingresados, pendientes o presupuestos (cabecera).
- Cambiar filtros, estados o permisos de las consultas.
- Pivot: agregar `cantidad_venta` como métrica opcional solo si se toca catálogo pivot en la misma entrega (Should; no bloqueante).

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| HU-101-028 | [HU-101-028-consulta-detalle-pedidos-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos-update.md) |
| TR API | [TR-SPEC-101-07-consultas-api-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-07-consultas-api-update.md) |

## Definición de listo (update)

- [ ] `cantidadVenta` en respuesta detalle-pedidos.
- [ ] Test API mínimo.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 30/07/2026 | CC PQ #10 | `cantidad_venta` en informes |
| 30/07/2026 | Parte G | Volcado SPEC-update |
| 30/07/2026 | PQ | Corrección: solo Detalle de Pedidos (no cabecera) |
