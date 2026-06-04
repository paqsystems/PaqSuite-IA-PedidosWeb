# HU-101-020 — Logs de integración

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-020-logs-integracion |
| **SPEC origen** | [SPEC-101-08-logs-integracion](../../05-open-spec/101-PedidosWeb/SPEC-101-08-logs-integracion.md) |
| **Prioridad** | **Should** (AMB-C02) |
| **Estado** | Finalizado |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario de soporte o supervisor**,  
quiero **consultar logs de integración con el ERP**,  
para **diagnosticar fallas de sincronización**.

## Alcance

- Consulta filtrada por fecha, tipo, severidad
- Menú ítem 11 producto §8
- Persistencia en `pq_pedidosweb_logs_integracion`

## Criterios de aceptación

- [ ] **CA-01:** Endpoint y grilla con filtros básicos.
- [ ] **CA-02:** No bloquea E2E §9 si se implementa en iteración posterior.

## Veredicto B1

**Lista para TR** — prioridad Should.
