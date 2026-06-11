# HU-101-021 — Consulta de deuda (update — pivot)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-021-consulta-deuda-update |
| **HU base** | [HU-101-021-consulta-deuda](../../101-PedidosWeb/HU-101-021-consulta-deuda.md) |
| **SPEC update** | [SPEC-101-11-consultas-ui-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #4 — **10/06/2026** |
| **TR update** | [TR-SPEC-101-11-consultas-ui-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-11-consultas-ui-update.md) (Bloque pivot — deuda) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Narrativa (delta)

Como **usuario comercial**, quiero **ver deuda en tabla dinámica**, para **concentrar saldos por cliente, tipo de comprobante y vencimiento**.

## Alcance incluido (delta)

- Migrar `DeudaPage` a `ConsultaGrillaPivotShell`.
- `consultaId`: `CONSULTA_DEUDA`; `proceso`/`gridId`: `pw_deuda`; permiso host `pw_deudaclientes`.
- Columnas pivotables: `codCliente`, `razonSocial`, `tipo`, `numero`, `fecha`, `vencimiento`, `saldo`.
- `pivotBase`: filas cliente + tipo; valor `saldo` sum.

## Criterios de aceptación (delta)

- [ ] **CA-PVT-01:** Toggle grilla/pivot operativo en `/consultas/deuda`.
- [ ] **CA-PVT-02:** Visibilidad GEN-02 preservada (solo clientes visibles).
- [ ] **CA-PVT-03:** Export/layouts grilla sin regresión.
- [ ] **CA-PVT-04:** Pivot totaliza `saldo` con agregaciones numéricas.
- [ ] **CA-PVT-05:** Diseños pivot guardables.
