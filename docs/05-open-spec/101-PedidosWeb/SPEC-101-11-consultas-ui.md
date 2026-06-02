# SPEC-101-11 — Consultas UI

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |

## Objetivo

Pantallas de consulta con `DataGridDx`, layouts, exportación **Excel** y acciones por ícono.

## In scope

- Grillas: pedidos ingresados, pendientes, presupuestos 99, presupuestos 98 (solo lectura)
- Stock, deuda, cheques, historial (modal detalle ventas)
- Carátula con `fecha_proceso`
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
