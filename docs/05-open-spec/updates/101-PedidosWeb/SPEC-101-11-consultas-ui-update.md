# SPEC-101-11 — Consultas UI (update CC PQ #19 — emisión)

| Campo | Valor |
|-------|--------|
| **SPEC base** | [SPEC-101-11-consultas-ui](../../101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #19, 03/11/2026 |
| **Última actualización** | 03/11/2026 (Parte G) |

## Delta de alcance

Incorporar acción **Emitir reporte** (GEN-15) en las ocho consultas listadas, en toolbar de proceso (patrón informes: `toolbarEnd` junto a Actualizar / export Excel), sin eliminar GEN-03 ni pivot GEN-12.

### Informes (proceso / ruta)

| Informe | `procedimiento` | Ruta UI |
|---------|-----------------|---------|
| Presupuestos Ingresados | `pw_presupuestosingresados` | `/presupuestos/ingresados` |
| Pedidos Ingresados | `pw_pedidosingresados` | `/pedidos/ingresados` |
| Pedidos Pendientes | `pw_pedidospendientes` | `/pedidos/pendientes` |
| Detalle de Pedidos | `pw_detallepedidos` | `/pedidos/detalle` |
| Deudas | `pw_deudaclientes` | `/consultas/deuda` |
| Cheques | `pw_consultacheques` | `/consultas/cheques` |
| Historial de Ventas | `pw_historialventas` | `/consultas/historial` |
| Stock | `pw_consultastock` | `/consultas/stock` |

### Reglas UI

1. **Emitir** abre diálogo GEN-15 con plantillas asociadas al `consultaId` / `procedimiento`; datos = mismos filtros y visibilidad que la grilla visible (re-fetch servidor si el contrato GEN-15 lo exige).
2. i18n: claves `emission.*` / catálogo SDK; tooltip y botón accesibles (`data-testid`: `gridEmitReport` por proceso o sufijo estable).
3. Deshabilitar emitir si grilla vacía o sin plantilla publicada (mensaje i18n).
4. Vista **pivot** activa: emisión desde dataset de consulta acordado en TR (default: volver a grilla o emitir desde último fetch grilla — cerrar en TR-GEN-15 / TR-101-11-update).
5. **Mobile:** v1 CC #19 — emisión **solo web**; kardex sin botón emitir hasta HU/TR fase 2.

### Fuera de scope

- Diseñador (HU-GEN-15).
- Nuevas columnas de consulta.

### HU / TR

- [HU-101-055-emision-reportes-informes](../../../03-historias-usuario/101-PedidosWeb/HU-101-055-emision-reportes-informes.md)
- [TR-SPEC-101-11-consultas-ui-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-11-consultas-ui-update.md)
