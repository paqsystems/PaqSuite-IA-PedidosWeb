# TR-SPEC-101-07 — Consultas API (update-01 — historial fechas + stock)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-07-consultas-api](../../101-PedidosWeb/TR-SPEC-101-07-consultas-api.md) |
| **SPEC update** | [SPEC-101-07-consultas-api-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-07-consultas-api-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. `HistorialVentasConsultaService`: query `fecha_desde` / `fecha_hasta` opcionales sobre `fecha_emi`.
2. `StockConsultaService`: excluir `stockeable = 0`.
3. OpenAPI params + feature tests.

## AC técnicos

- [ ] **AC-CC12-T-A1:** Cuatro combinaciones de fechas historial.
- [ ] **AC-CC12-T-A2:** Stock sin no-stockeables.
- [ ] **AC-CC12-T-A3:** OpenAPI actualizado.
