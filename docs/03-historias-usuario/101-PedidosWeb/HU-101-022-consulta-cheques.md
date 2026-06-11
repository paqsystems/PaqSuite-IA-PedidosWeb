# HU-101-022 — Consulta de cheques en cartera

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-022-consulta-cheques |
| **SPEC origen** | [SPEC-101-07](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), SPEC madre §5.2 |
| **Prioridad** | Must |
| **Estado** | En Control Calidad |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **consultar cheques en cartera o aplicados a futuro**,  
para **informar al cliente**.

## Reglas de negocio

Fuente de verdad columnas, joins y contrato API/UI: **[consulta-cheques.md](../../02-producto/PedidosWeb/consulta-cheques.md)**.

1. Cheques con `fecha` **≥ día actual** (cartera o aplicados a futuro) según producto §17.5.
2. Por cliente o todos según perfil (universo visible).
3. Columnas: interno, número, cliente, nombre (`clientes.nombre`), banco, fecha, importe, origen, estado.
4. `fecha_proceso` en carátula.

## Criterios de aceptación

- [ ] **CA-01:** Filtro por cliente respeta visibilidad.
- [ ] **CA-02:** Carátula con `fecha_proceso`.

## Veredicto B1

**Lista para TR**.
