# HU-101-022 — Consulta de cheques en cartera

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-022-consulta-cheques |
| **SPEC origen** | [SPEC-101-07](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), SPEC madre §5.2 |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **consultar cheques en cartera o aplicados a futuro**,  
para **informar al cliente**.

## Reglas de negocio

1. Cheques con fecha **posterior al día** según producto §17.5.
2. Por cliente o todos según perfil.
3. `fecha_proceso` en carátula.

## Criterios de aceptación

- [ ] **CA-01:** Filtro por cliente respeta visibilidad.
- [ ] **CA-02:** Carátula con `fecha_proceso`.

## Veredicto B1

**Lista para TR**.
