# HU-101-018 — Consulta de stock (update — pivot)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-018-consulta-stock-update |
| **HU base** | [HU-101-018-consulta-stock](../../101-PedidosWeb/HU-101-018-consulta-stock.md) |
| **SPEC update** | [SPEC-101-11-consultas-ui-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #4 — **10/06/2026** |
| **TR update** | [TR-SPEC-101-11-consultas-ui-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-11-consultas-ui-update.md) (Bloque pivot — stock) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Narrativa (delta)

Como **usuario comercial**, quiero **pivotar stock y disponible neto por artículo**, para **detectar faltantes y concentraciones** más allá de la grilla plana.

## Alcance incluido (delta)

- Migrar `StockPage` a `ConsultaGrillaPivotShell`.
- `consultaId`: `CONSULTA_STOCK`; `proceso`/`gridId`: `pw_stock`.
- Métricas pivot: `stock`, `comprometido`, `comprometidoWeb`, `disponibleNeto`, columnas base cuando aplique.
- Sin filtro por cliente (igual que grilla); búsqueda `q` vía refresh grilla/pivot data.
- `pivotBase`: filas `codArticulo` + `descripcion`; valores `disponibleNeto` sum.

## Criterios de aceptación (delta)

- [ ] **CA-PVT-01:** Toggle grilla/pivot en `/consultas/stock`.
- [ ] **CA-PVT-02:** Fórmulas disponible neto idénticas a [consulta-stock.md](../../../02-producto/PedidosWeb/consulta-stock.md).
- [ ] **CA-PVT-03:** Columnas base opcionales en catálogo cuando artículo tiene `base`.
- [ ] **CA-PVT-04:** Actualizar recarga dataset servidor (no solo cliente).
