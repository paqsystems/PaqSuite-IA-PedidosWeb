# SPEC-101-12 — Tratativas y cierre

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | **Should** — tratativas (AMB-C01); cierre 98 cubierto en 101-04/05 |

## Objetivo

Circuito **Should** de tratativas sobre presupuestos **estado 99**; motivos de cierre en tablas nuevas.

## In scope (Should)

- CRUD mínimo tratativas (producto §16): comentario, resultado, fechas opcionales
- Tablas `pq_pedidosweb_tratativas`, `pq_pedidosweb_tratativas_resultados`, `pq_pedidosweb_motivos_cierre`
- UI simple de seguimiento (menú ítem 9)

## Cierre exitoso (conversión)

- Parámetro ERP **`CodMotivoCierreExitoso`** → `id_motivo` en catálogo `pq_pedidosweb_motivos_cierre` (`tipo_cierre = positivo`, `activo`).
- Aplicado en **HU-101-013** / `PedidoService` al convertir; sin selector de motivo en UI.

## Fuera de scope / decisiones cerradas

- **Sin cierre parcial/positivo** ni clasificación por renglones (AMB-C05)
- Cierre 99→98 por conversión, rechazo o cierre con motivo: **101-04** (no duplicar reglas aquí)
- **Sin DELETE** presupuesto

## Dependencias

- SPEC-101-04, SPEC-101-05

## HU relacionadas

HU-101-014 (tratativas Should); HU-101-013 (conversión Must en 04)

## Definición de listo

- [ ] Slice marcado Should: puede entregarse post E2E §9
- [ ] Si se implementa: tests + OpenAPI acotados
