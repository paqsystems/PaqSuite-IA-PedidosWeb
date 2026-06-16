# HU-101-022 — Consulta de cheques en cartera

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-022-consulta-cheques |
| **SPEC origen** | [SPEC-101-07](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), [SPEC-101-11](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Producto** | [consulta-cheques.md](../../02-producto/PedidosWeb/consulta-cheques.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado (Parte I — CC PQ #4) |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **consultar cheques en cartera o aplicados a futuro**,  
para **informar al cliente** y **analizar importes por cliente, banco y estado en pivot**.

## Reglas de negocio

Fuente de verdad columnas, joins y contrato API/UI: **[consulta-cheques.md](../../02-producto/PedidosWeb/consulta-cheques.md)**.

1. Cheques con `fecha` **≥ día actual** (cartera o aplicados a futuro) según producto §17.5.
2. Por cliente o todos según perfil (universo visible).
3. Columnas: interno, número, cliente, nombre (`clientes.nombre`), banco, fecha, importe, origen, estado.
4. `fecha_proceso` en carátula.
5. **CC PQ #4:** pivot opcional (`CONSULTA_CHEQUES`); filtro negocio `fecha >= hoy` se mantiene en dataset pivot.

## Criterios de aceptación

- [x] **CA-01:** Filtro por cliente respeta visibilidad.
- [x] **CA-02:** Carátula con `fecha_proceso`.
- [x] **CA-PVT-01:** Toggle grilla/pivot en `/consultas/cheques`.
- [x] **CA-PVT-02:** Filtro cartera/futuro intacto en pivot.
- [x] **CA-PVT-03:** Campos string (banco, estado) totalizables con count/min/max.
- [x] **CA-PVT-04:** Export grilla y pivot según GEN-03 / GEN-08.

## Historial CC PQ #4 (10/06/2026) — Parte I 16/06/2026

Unificación delta CC PQ #4 (archivo `*-update` eliminado en Parte I).

## Veredicto B1

**Lista para TR**.
