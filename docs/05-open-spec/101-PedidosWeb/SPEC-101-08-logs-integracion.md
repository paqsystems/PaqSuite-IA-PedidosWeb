# SPEC-101-08 — Logs de integración

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | **Should** (AMB-C02, 2026-06-01) |

## Objetivo

Persistir y consultar logs de integración ERP/portal para soporte operativo.

## In scope

- Tabla/modelo `pq_pedidosweb_logs_integracion`
- Servicio de logging en puntos acordados en TR
- Endpoint consulta con filtros fecha / tipo / severidad
- Grilla UI (menú ítem 11) si se implementa el slice

## Fuera de scope

- No bloquea cierre E2E §9 del SPEC madre si se difiere

## Dependencias

- SPEC-101-02

## HU relacionadas

HU-101-020

## Definición de listo

- [ ] Persistencia + consulta filtrada
- [ ] Tests feature del endpoint
