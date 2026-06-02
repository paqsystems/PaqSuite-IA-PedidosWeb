# SPEC-101-07 — Consultas API

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |

## Objetivo

Endpoints de consulta con visibilidad, paginación y preparación para exportación **Excel** (GEN-03).

## In scope

- Pedidos ingresados (0; -1 según reglas consulta producto §17.1)
- Pedidos pendientes (1)
- Presupuestos activos (99) y cerrados (98)
- Stock, deuda, cheques, historial (§5.2 madre)
- `fecha_proceso` en metadata de respuesta para carátula UI
- Filtros básicos + paginación

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
