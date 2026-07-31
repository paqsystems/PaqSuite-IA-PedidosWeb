# TR-SPEC-101-02 — Modelos (update — columnas CC #10)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-02-modelos](../../101-PedidosWeb/TR-SPEC-101-02-modelos.md) |
| **SPEC update** | [SPEC-101-02-modelos-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-02-modelos-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. Migración/SQL idempotente:
   - `pq_pedidosweb_articulos.equivalencia_ventas` decimal NOT NULL default 1 (o nullable + default runtime 1).
   - `pq_pedidosweb_pedidosdetalle.cantidad_venta` decimal; backfill = `cantidad`.
2. Actualizar modelos Eloquent / casts.
3. Actualizar bootstrap schema dev si aplica (sin DROP).

## AC técnicos

- [ ] **AC-CC10-T-M1:** Columnas existen en esquema target.
- [ ] **AC-CC10-T-M2:** Backfill `cantidad_venta` OK.
