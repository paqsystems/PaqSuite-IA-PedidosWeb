# SPEC-101-14 — Dashboard operativo

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) §4.1 |
| **Estado** | Especificado |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-06-09 (Parte I — CC PQ #1) |

## Objetivo

Ocho indicadores §4.1 del SPEC madre; reemplazar/extender `GET /dashboard/resumen` demo (GEN-02).

## In scope

- Q/$ presupuestos activos (99)
- Q/$/**unidades** pedidos ingresados y pendientes (0/1) con regla **-1** abajo
- Top cliente por $ presupuestos activos y por $ pedidos ingresados; desempate por **razón social** A–Z (luego `cod_client` si persiste empate)
- **Mes en curso por estado:** Q, $ y **unidades** por comprobante, filtrando solo el mes calendario actual, con un indicador por estado **0, 1, 2, 3, 98, 99** (`GET /dashboard/resumen-mensual`)
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

## UI (fuente de verdad)

- Patrón visual y `data-testid`: [`docs/02-producto/PedidosWeb/patron-dashboard-operativo-ui.md`](../../02-producto/PedidosWeb/patron-dashboard-operativo-ui.md)
- Referencia: dashboard Producción en PaqSuite-IA-Tango (TR-033)

## Definición de listo

- [ ] 8 indicadores coherentes con datos seed/E2E
- [ ] Tests feature + E2E dashboard
- [ ] Layout KPI según patrón UI (tarjetas agrupadas, i18n, testids estables)
- [x] CC PQ #1: unidades en KPIs; mes en curso por estado (0–99)

## Historial de cambios

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 04/06/2026 | CC PQ #1 | Unidades KPI + dashboard mes en curso por estado |
| 09/06/2026 | Parte I | Unificación `SPEC-101-14-dashboard-update` |
