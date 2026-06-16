# HU-101-021 — Consulta de deuda de clientes

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-021-consulta-deuda |
| **SPEC origen** | [SPEC-101-07](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), [SPEC-101-11](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Producto** | [consulta-deuda.md](../../02-producto/PedidosWeb/consulta-deuda.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado (Parte I — CC PQ #4) |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **consultar deuda por cliente o de todos mis clientes**,  
para **evaluar situación crediticia** y **analizar saldos en vista pivot** cuando el tenant lo habilite.

## Reglas de negocio

1. Por cliente o todos según perfil (visibilidad GEN-02).
2. Columnas y origen BD: **[consulta-deuda.md](../../02-producto/PedidosWeb/consulta-deuda.md)** (fuente de verdad).
3. `fecha_proceso` en carátula (`metadata.fecha_proceso`).
4. **CC PQ #4:** pivot opcional (`CONSULTA_DEUDA`); dataset = mismo listado API; visibilidad GEN-02 preservada en pivot.

## Criterios de aceptación

- [x] **CA-01:** Vendedor ve solo cartera asignada.
- [x] **CA-02:** Cliente ve solo su deuda.
- [x] **CA-03:** Export Excel disponible.
- [x] **CA-PVT-01:** Toggle grilla/pivot operativo en `/consultas/deuda` con flags activos.
- [x] **CA-PVT-02:** Visibilidad GEN-02 preservada en pivot.
- [x] **CA-PVT-03:** Export/layouts grilla sin regresión.
- [x] **CA-PVT-04:** Pivot totaliza `saldo` con agregaciones numéricas.
- [x] **CA-PVT-05:** Diseños pivot guardables.

## Historial CC PQ #4 (10/06/2026) — Parte I 16/06/2026

Unificación delta CC PQ #4 (archivo `*-update` eliminado en Parte I).

## Veredicto B1

**Lista para TR**.
