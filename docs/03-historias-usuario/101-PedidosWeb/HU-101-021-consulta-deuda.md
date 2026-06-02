# HU-101-021 — Consulta de deuda de clientes

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-021-consulta-deuda |
| **SPEC origen** | [SPEC-101-07](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), SPEC madre §5.2 |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **consultar deuda por cliente o de todos mis clientes**,  
para **evaluar situación crediticia**.

## Reglas de negocio

1. Por cliente o todos según perfil.
2. Comprobantes con saldo, vencimiento, saldo acumulado.
3. `fecha_proceso` en carátula.

## Criterios de aceptación

- [ ] **CA-01:** Vendedor ve solo cartera asignada.
- [ ] **CA-02:** Cliente ve solo su deuda.
- [ ] **CA-03:** Export Excel disponible.

## Veredicto B1

**Lista para TR**.
