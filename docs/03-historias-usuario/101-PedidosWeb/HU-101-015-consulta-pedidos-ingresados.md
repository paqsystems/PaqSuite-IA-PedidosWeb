# HU-101-015 — Consulta de pedidos ingresados

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-015-consulta-pedidos-ingresados |
| **SPEC origen** | [SPEC-101-07-consultas-api](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), [SPEC-101-11-consultas-ui](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |
| **Dependencias** | HU-GEN-03-grillas-listados; HU-GEN-03-exportaciones |

## Narrativa

Como **usuario comercial**,  
quiero **consultar pedidos ingresados en una grilla**,  
para **ver, editar o eliminar según permisos**.

## Reglas de negocio

1. Estados **0** y **-1** cuando aplique control operativo (producto §17.1).
2. Visibilidad por perfil (cliente / vendedor / supervisor).
3. Columnas: todos los campos de cabecera según **[consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md)**; visibilidad inicial documentada allí.
4. Acciones: ver, editar, eliminar (pedido 0), copiar (HU-101-026) — íconos + tooltip.
5. Export **Excel** (GEN-03); PDF fuera MVP.

## Criterios de aceptación

- [ ] **CA-01:** Grilla `DataGridDx` con filtros, agrupación, totales.
- [ ] **CA-02:** Usuario sin permiso no ve acciones de edición/baja.
- [ ] **CA-03:** Layouts persistentes por `proceso`+`grid_id` si flag habilitado.
- [ ] **CA-04:** ≥ 2 E2E (feliz + sin permiso o vacío).

## Veredicto B1

**Lista para TR** (SPEC-101-07/11).
