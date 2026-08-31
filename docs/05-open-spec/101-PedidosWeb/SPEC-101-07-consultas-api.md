# SPEC-101-07 — Consultas API

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Finalizado (Parte I CC PQ #10/#11) |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-31 |

## Objetivo

Endpoints de consulta con visibilidad, paginación y preparación para exportación **Excel** (GEN-03).

## In scope

- Pedidos ingresados (0; -1 según reglas consulta producto §17.1)
- Pedidos pendientes (1)
- Presupuestos activos (99) y cerrados (98)
- Stock, deuda, cheques, historial (§5.2 madre)
- **Historial:** query opcionales `fecha_desde` / `fecha_hasta` (`YYYY-MM-DD`) sobre `fecha_emi`, **además** del techo `DiasVentasDetalladas`. Vacío = no aplicar ese predicado; solo desde / solo hasta / ambos (rango inclusivo).
- **Stock:** excluir artículos con `stockeable = false` (join a `pq_pedidosweb_articulos`; null/ausente = stockeable).
- **Deuda para modal de carga:** reutilizar `GET /consultas/deuda?cod_cliente=…` (visibilidad GEN-02); el consumidor de carga no usa export/pivot.
- `fecha_proceso` en metadata de respuesta para carátula UI
- Filtros básicos + paginación
- **Nombre comercial del cliente** (`nombreFantasia`) en listados de cabecera
- **`fecha_proceso`** en metadata: presentación UI `dd/MM/yyyy HH:mm` (i18n, sin segundos)
- **Precio neto unitario** (`precioNeto` desde `precio_neto`) en consulta detalle (HU-101-028)
- **Cantidad venta** (`cantidadVenta` desde `cantidad_venta`) en consulta **Detalle de Pedidos** (`GET .../consultas/detalle-pedidos`, HU-101-028) — CC PQ #10. **Fuera:** listados cabecera (ingresados / pendientes / presupuestos).

## Fuera de scope

- **PDF** — futuro [SPEC-001-06-emision](../001-Generaliddes/SPEC-001-06-emision.md) (AMB-C08)
- Pantallas React (101-11)

## Dependencias

- SPEC-101-03, policies 101-06
- Parámetro `DiasVentasDetalladas` (contexto SPEC-001-04 pendiente)

## HU relacionadas

HU-101-015…018, HU-101-021…023

## Definición de listo

- [ ] Feature tests por endpoint Must
- [ ] OpenAPI + matriz permisos
- [x] CC PQ #1: nombre comercial, `fecha_proceso` minutos, `precioNeto` detalle
- [x] CC PQ #12: rango fechas historial + exclusión no stockeables en stock
- [x] CC PQ #10: `cantidadVenta` en detalle pedidos

## Historial de cambios

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 04/06/2026 | CC PQ #1 | Columnas consulta cabecera + metadata + detalle |
| 09/06/2026 | Parte I | Unificación `SPEC-101-07-consultas-api-update` |
| 28/08/2026 | CC PQ #12 | Rango fechas historial + exclusión stock no stockeable |
| 30/08/2026 | Parte I | Unificación `SPEC-101-07-consultas-api-update-01` (CC PQ #12) |
| 30/07/2026 | CC PQ #10 | `cantidadVenta` en detalle pedidos (no cabecera) |
| 31/08/2026 | Parte I | Unificación `SPEC-101-07-consultas-api-update`. Sin updates abiertos |
