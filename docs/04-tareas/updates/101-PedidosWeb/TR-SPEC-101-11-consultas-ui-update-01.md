# TR-SPEC-101-11 — Consultas UI (update-01 — CC PQ #12)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-11-consultas-ui](../../101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md) |
| **SPEC update** | [SPEC-101-11-consultas-ui-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. `DeudaPage` / `DataGridDx`: `onCellPrepared` (o equivalente) para color de `saldo`.
2. `HistorialVentasPage`: DateBox desde/hasta; pasar a `fetchHistorialVentas` / fetch paginado.
3. Verificar stock page sin filas no-stockeables (API).
4. Tests Vitest / E2E mínimos de filtros y estilos.

## AC técnicos

- [ ] **AC-CC12-T-U1:** Colores deuda.
- [ ] **AC-CC12-T-U2:** Filtros fecha historial.
- [ ] **AC-CC12-T-U3:** Stock coherente.
