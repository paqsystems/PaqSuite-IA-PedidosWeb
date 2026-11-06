# HU-101-055 — Emisión de reportes en informes

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-055-emision-reportes-informes |
| **SPEC origen** | [SPEC-101-11-consultas-ui-update](../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update.md) · [SPEC-101-07-consultas-api-update](../../05-open-spec/updates/101-PedidosWeb/SPEC-101-07-consultas-api-update.md) |
| **Épica** | 101 — PedidosWeb / consultas |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **TR relacionadas** | [TR-SPEC-101-11-consultas-ui-update](../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-11-consultas-ui-update.md) · [TR-SPEC-101-07-consultas-api-update](../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-07-consultas-api-update.md) |
| **Dependencias** | [HU-GEN-15-diseniador-reportes](../001-Generaliddes/HU-GEN-15-diseniador-reportes.md) |
| **Origen** | 00-ControlCalidad-PQ #19 — 03/11/2026 |

## Origen

Control de calidad PQ 03/11/2026 — ítem **GEN-15 · Emisión de reportes en informes PedidosWeb**.

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **usuario comercial**,  
quiero **emitir un reporte PDF** desde cada informe operativo,  
para **compartir o archivar** la misma información que veo en grilla, con formato profesional.

## Consultas en alcance

| Informe | HU base | `procedimiento` |
|---------|---------|-----------------|
| Presupuestos Ingresados | [HU-101-016](HU-101-016-consulta-presupuestos.md) | `pw_presupuestosingresados` |
| Pedidos Ingresados | [HU-101-015](HU-101-015-consulta-pedidos-ingresados.md) | `pw_pedidosingresados` |
| Pedidos Pendientes | [HU-101-017](HU-101-017-consulta-pedidos-pendientes.md) | `pw_pedidospendientes` |
| Detalle de Pedidos | [HU-101-028](HU-101-028-consulta-detalle-pedidos.md) | `pw_detallepedidos` |
| Deudas | [HU-101-021](HU-101-021-consulta-deuda.md) | `pw_deudaclientes` |
| Cheques | [HU-101-022](HU-101-022-consulta-cheques.md) | `pw_consultacheques` |
| Historial de Ventas | [HU-101-023](HU-101-023-historial-ventas.md) | `pw_historialventas` |
| Stock | [HU-101-018](HU-101-018-consulta-stock.md) | `pw_consultastock` |

## Criterios de aceptación

- [ ] **CA-01:** Cada consulta expone acción **Emitir reporte** con `data-testid="gridEmitReport"` (o convención TR).
- [ ] **CA-02:** Emisión respeta filtros activos (cliente, fechas historial, etc.) equivalentes al GET consulta.
- [ ] **CA-03:** Export Excel GEN-03 sigue operativo en las ocho pantallas.
- [ ] **CA-04:** Sin plantilla asociada → mensaje i18n claro; no descarga vacía.
- [ ] **CA-05:** Piloto E2E: pedidos ingresados + deuda emiten PDF con plantilla seed.
- [ ] **CA-06:** Web only en v1; native no muestra emitir.
