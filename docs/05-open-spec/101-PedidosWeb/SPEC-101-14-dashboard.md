# SPEC-101-14 — Dashboard operativo

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) §4.1 |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |

## Objetivo

Ocho indicadores §4.1 del SPEC madre; reemplazar/extender `GET /dashboard/resumen` demo (GEN-02).

## In scope

- Q/$ presupuestos activos (99)
- Q/$ pedidos ingresados y pendientes (0/1) con regla **-1** abajo
- Top cliente por $ presupuestos activos y por $ pedidos ingresados
- Visibilidad por perfil
- Una moneda por tenant

## Regla estado -1 en KPI (AMB-C09)

Incluir comprobantes **estado -1** en indicadores de pedidos ingresados **salvo** exclusión paramétrica:

> **Excluir** del conteo si `fechahora_ultima_actividad + MinutosWeb >= fechahora_actual`  
> (modificación **-1** activa; misma regla que HU-101-011).

Los **estado 0** sin bloqueo activo siempre cuentan.

## Fuera de scope

- Indicadores conceptual §19 (ranking motivos, CORE, etc.)

## Dependencias

- SPEC-101-07 o queries dedicadas en service
- Parámetro `MinutosWeb`

## HU relacionadas

HU-101-025

## Definición de listo

- [ ] 8 indicadores coherentes con datos seed/E2E
- [ ] Tests feature + E2E dashboard
