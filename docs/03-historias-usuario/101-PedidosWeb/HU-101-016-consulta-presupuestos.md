# HU-101-016 — Consulta de presupuestos (activos y cerrados)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-016-consulta-presupuestos |
| **SPEC origen** | [SPEC-101-07](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), [SPEC-101-11](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **listar presupuestos activos y consultar cerrados por separado**,  
para **operar sobre 99 y auditar 98**.

## Reglas de negocio

1. **Activos:** solo estado **99** — ver, editar, convertir, cerrar/rechazar, copiar, tratativas (Should).
2. **Cerrados:** solo estado **98** — solo lectura + detalle cierre.
3. **Sin DELETE** presupuesto.
4. Dos grillas o pestañas/procesos de menú distintos.
5. Columnas cabecera: **[consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md)**.

## Criterios de aceptación

- [ ] **CA-01:** Listado activos muestra solo 99 del universo visible.
- [ ] **CA-02:** Listado cerrados muestra 98 con motivo/tipo desde `presupuestos_cierres`.
- [ ] **CA-03:** No hay acción eliminar en ninguna grilla de presupuesto.

## Veredicto B1

**Lista para TR** (SPEC-101-07/11).
