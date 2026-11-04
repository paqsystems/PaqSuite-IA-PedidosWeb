# TR-SPEC-101-11 — Consultas UI (update CC PQ #19 — emisión)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-055-emision-reportes-informes](../../03-historias-usuario/101-PedidosWeb/HU-101-055-emision-reportes-informes.md) |
| **SPEC** | [SPEC-101-11-consultas-ui-update](../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update.md) |
| **TR base** | [TR-SPEC-101-11-consultas-ui](../../101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md) |
| **Estado** | Pendiente |
| **Origen** | 00-ControlCalidad-PQ #19 — 03/11/2026 |

---

## 1) Alcance FE

Integrar componente **Emitir reporte** (GEN-15) en:

- `PresupuestosIngresadosPage`, `PedidosIngresadosPage`, `PedidosPendientesPage`, `DetallePedidosPage`
- `DeudaPage`, `ChequesPage`, `HistorialVentasPage`, `StockPage`

Patrón: toolbar proceso (`toolbarEnd`), icono + tooltip i18n `emission.emitReport`, `data-testid="gridEmitReport"`.

Props mínimas al diálogo SDK: `procedimiento`, `consultaId`, snapshot filtros (desde state página / hook consulta).

## 2) AC

- **AC-CC19-UI-01:** Botón visible en 8 pantallas (flag env `EMISSION_ENABLED` opcional, default true post-deploy).
- **AC-CC19-UI-02:** Historial ventas pasa `fecha_desde`/`fecha_hasta` al emitir.
- **AC-CC19-UI-03:** Deuda/colores y pivot CC #4 no regresionan.
- **AC-CC19-UI-04:** Vitest por página piloto (mock emit API).

## 3) Dependencia

Completar [TR-GEN-15-diseniador-reportes](../../001-Generaliddes/TR-GEN-15-diseniador-reportes.md) piloto plantilla antes de rollout 8 informes.
