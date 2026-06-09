# SPEC-101-11 — Consultas UI

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Especificado |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-06-09 (Parte I — CC PQ #1) |

## Objetivo

Pantallas de consulta con `DataGridDx`, layouts, exportación **Excel** y acciones por ícono.

## In scope

- Grillas: pedidos ingresados, pendientes, presupuestos 99, presupuestos 98 (solo lectura)
- Stock, deuda, cheques, historial (modal detalle ventas)
- Carátula con `fecha_proceso` (**formato `dd/MM/yyyy HH:mm`**, i18n, sin segundos)
- Columna **nombre comercial** del cliente en consultas cabecera (015/016/017/028)
- Ícono **Actualizar** (tooltip i18n) en informes de consulta — recarga datos de grilla
- Acción **Copiar** en pedidos pendientes (mismo patrón HU-101-026)
- Columna **Precio neto unitario** en detalle pedidos (HU-101-028)
- Export Excel (GEN-03); acciones ver/editar/eliminar según permisos (**eliminar** solo pedido 0)
- PDF: **fuera** — ver SPEC-001-06

## Fuera de scope

- PDF en MVP
- Lógica API (101-07)

## Dependencias

- SPEC-101-07, SPEC-101-09, GEN-03

## HU relacionadas

HU-101-015…018, HU-101-021…023

## Definición de listo

- [ ] ≥ 2 E2E por consulta crítica o suite agrupada documentada
- [ ] Layouts y export operativos
- [x] CC PQ #1: nombre comercial, carátula fecha, actualizar, copiar pendientes, precio neto detalle

## Historial de cambios

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 04/06/2026 | CC PQ #1 | Consultas UI: columnas, refresh, copiar pendientes |
| 09/06/2026 | Parte I | Unificación `SPEC-101-11-consultas-ui-update` |
