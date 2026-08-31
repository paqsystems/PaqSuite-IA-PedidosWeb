# HU-101-018 — Consulta stock (update — excluir no stockeables)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-018-consulta-stock](../../101-PedidosWeb/HU-101-018-consulta-stock.md) |
| **SPEC update** | [SPEC-101-07-consultas-api-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-07-consultas-api-update-01.md) · [SPEC-101-11-consultas-ui-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Criterios de aceptación

- [ ] **CA-CC12-S01:** La consulta de stock no lista artículos con `stockeable=false`.
- [ ] **CA-CC12-S02:** Kardex mobile / pivot (si aplica) usan el mismo dataset filtrado.
