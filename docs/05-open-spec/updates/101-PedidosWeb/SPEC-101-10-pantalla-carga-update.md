# SPEC-101-10 — Pantalla de carga (update CC PQ #5)

| Campo | Valor |
|-------|--------|
| **SPEC base** | [SPEC-101-10-pantalla-carga](../../101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Estado** | Finalizado |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #5, 09/06/2026 |
| **Última actualización** | 2026-06-09 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Finalizado |

## Cambio de alcance

En el **lookup/browse** de artículos de carga (`GET /api/v1/articulos` sin `codigos`), el texto informativo de disponibilidad **no** debe descontar pedidos web ingresados (`comprometido_web`).

| Contexto | Fórmula disponible |
|----------|-------------------|
| Listbox carga (browse) | `pq_pedidosweb_stock.stock − pq_pedidosweb_stock.comprometido` |
| Disponible base (si `articulos.base` no vacío) | Misma fórmula agregada por `base` en `pq_pedidosweb_stock` |
| Consulta de stock (`/consultas/stock`) | Sin cambio: `stock − comprometido − comprometido_web` |

## Display ítem listbox

`{codigo} - {descripcion} — Disp. {disponible}` y, si hay base, `({disponibleBase})`.

## Fuera de alcance

- Refresh batch por `codigos` en cambio de lista de precios (puede seguir sin mostrar disponible en UI).
- Consulta de stock y demás pantallas.
