# HU-101-017 — Consulta de pedidos pendientes ERP

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-017-consulta-pedidos-pendientes |
| **SPEC origen** | [SPEC-101-07](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), [SPEC-101-11](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **ver pedidos pendientes en ERP (estado 1)**,  
para **hacer seguimiento sin modificarlos**.

## Reglas de negocio

1. Solo estado **1**; **sin** edición ni eliminación (producto §17.3).
2. Solo consulta y export Excel.
3. Columnas cabecera: **[consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md)**.

## Criterios de aceptación

- [ ] **CA-01:** Grilla solo lectura con acciones ver/export.
- [ ] **CA-02:** Intentos de edición vía API → 422/403.

## Veredicto B1

**Lista para TR**.
