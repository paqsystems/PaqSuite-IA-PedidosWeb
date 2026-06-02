# TR-SPEC-101-11 — Consultas UI (DataGrid y export Excel)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-015](../../03-historias-usuario/101-PedidosWeb/HU-101-015-consulta-pedidos-ingresados.md) … [HU-101-018](../../03-historias-usuario/101-PedidosWeb/HU-101-018-consulta-stock.md); [HU-101-021](../../03-historias-usuario/101-PedidosWeb/HU-101-021-consulta-deuda.md) … [HU-101-023](../../03-historias-usuario/101-PedidosWeb/HU-101-023-historial-ventas.md) |
| **SPEC relacionada** | [SPEC-101-11-consultas-ui](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | TR-SPEC-101-07-consultas-api; TR-SPEC-101-09-frontend-base; [TR-GEN-03-grillas-listados](../001-Generaliddes/TR-GEN-03-grillas-listados.md); [TR-GEN-03-exportaciones](../001-Generaliddes/TR-GEN-03-exportaciones.md); [TR-GEN-03-layouts-grilla](../001-Generaliddes/TR-GEN-03-layouts-grilla.md) |
| **Estado** | Pendiente de Revisión — **Bloques 1–2 + Bloque 4 export mock** |
| **Última actualización** | 2026-06-02 |

**Origen:** HU-101-015, 016, 017, 018, 021, 022, 023  
**Referencia SPEC:** [SPEC-101-11-consultas-ui](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)  
**Patrón grilla i18n:** `docs/00-contexto/_mono/03-ui-transversal/patron-i18n-grilla-devextreme.md`

---

## 1) HU Refinada (resumen)

### Título
Pantallas de consulta con `DataGridDx`, layouts persistentes, exportación Excel (GEN-03) y acciones por ícono según permisos.

### Narrativa
Como **usuario comercial**, quiero **consultar pedidos, presupuestos, stock, deuda, cheques e historial en grillas estándar**, para **operar y exportar la vista visible sin PDF en MVP**.

### In scope / Out of scope
- **In scope:** grillas pedidos ingresados (0/-1), pendientes (1), presupuestos activos (99) y cerrados (98), stock, deuda, cheques, historial (modal detalle); carátula `fecha_proceso`; export Excel GEN-03; acciones ver/editar/eliminar según permisos (**eliminar** solo pedido estado 0); íconos + tooltip i18n.
- **Out of scope:** PDF (SPEC-001-06); implementación API (TR-SPEC-101-07); lógica de negocio en controllers.

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Cada consulta Must expone pantalla con `DataGridDx`, filtros, agrupación y totales donde aplique GEN-03.
- **AC-02:** Export Excel habilitado según [TR-GEN-03-exportaciones](../001-Generaliddes/TR-GEN-03-exportaciones.md): botón deshabilitado si grilla vacía; básica/formateada; respeta filtros/orden/layout activo.
- **AC-03:** Pedidos ingresados (HU-015): acciones ver, editar, eliminar (solo 0), copiar; sin eliminar en -1 salvo regla producto.
- **AC-04:** Presupuestos activos (HU-016): solo 99 — ver, editar, convertir, cerrar/rechazar, copiar; **sin DELETE**; cerrados (98) solo lectura + detalle cierre.
- **AC-05:** Pedidos pendientes (HU-017): solo lectura + ver + export; sin edición en UI.
- **AC-06:** Stock/deuda/cheques/historial (HU-018, 021–023): carátula `fecha_proceso`; historial abre modal DX con detalle (HU-023).
- **AC-07:** Layouts por `proceso` + `grid_id` si `gridLayoutsEnabled` (GEN-03).
- **AC-08:** Usuario sin permiso no ve acciones restringidas (403 vía API al intentar).
- **AC-09:** ≥ 2 E2E por consulta crítica **o** suite agrupada documentada (pedidos ingresados + presupuestos activos mínimo).
- **AC-10:** Textos grilla vía i18n (`grid.dx.*` + props); `data-testid` estables.

### Escenarios Gherkin

```gherkin
Feature: Consulta pedidos ingresados UI

  Scenario: Export Excel con datos
    Given pedidos ingresados visibles en grilla
    When exporta Excel formateado
    Then descarga archivo con columnas de la vista actual

  Scenario: Grilla vacía sin export
    Given listado sin filas
    Then el botón Exportar está deshabilitado

Feature: Presupuestos activos vs cerrados

  Scenario: Activos solo estado 99
    Given presupuestos 99 y 98 en base
    When abre consulta activos
    Then solo muestra estado 99

  Scenario: Cerrados sin eliminar
    When abre consulta cerrados
    Then no existe acción eliminar
```

---

## 3) Reglas de Negocio

1. **RN-01:** Visibilidad cliente/vendedor/supervisor igual que API (SPEC-101-06 / GEN-02).
2. **RN-02:** Pedidos ingresados: estados **0** y **-1** según producto §17.1; regla -1 activa coherente con HU-101-011 en acciones edición.
3. **RN-03:** Presupuesto: **sin DELETE** en ninguna grilla.
4. **RN-04:** PDF fuera de MVP; no botón PDF en toolbar.
5. **RN-05:** `fecha_proceso` en carátula desde metadata API (`resultado.meta.fecha_proceso` o convención cerrada en 101-07).
6. **RN-06:** Historial: período `DiasVentasDetalladas` (API); detalle solo en modal.
7. **RN-07:** Export: alcance página actual salvo documentación explícita por proceso (GEN-03 RN-05).

---

## 4) Impacto en Datos

### Tablas afectadas
- Ninguna DDL en frontend; consumo de endpoints TR-SPEC-101-07
- Layouts: tablas GEN-03 `grid_layouts` (existentes)

### Seed mínimo para tests
- Datos QA: pedidos 0/1/-1, presupuestos 99/98, stock, deuda, cheques, ventas según perfiles `cliente.mvp`, `vendedor.acotado.mvp`, `supervisor.mvp`

---

## 5) Contratos de API y OpenAPI

> API definida en **TR-SPEC-101-07-consultas-api**. Esta TR consume sin duplicar contratos.

### 5.1 Endpoints consumidos (referencia)

| Consulta | Método | Path (propuesto — alinear en 101-07) |
|----------|--------|--------------------------------------|
| Pedidos ingresados | GET | `/api/v1/pedidos/ingresados` |
| Pedidos pendientes | GET | `/api/v1/pedidos/pendientes` |
| Presupuestos activos | GET | `/api/v1/presupuestos/activos` |
| Presupuestos cerrados | GET | `/api/v1/presupuestos/cerrados` |
| Stock | GET | `/api/v1/consultas/stock` |
| Deuda | GET | `/api/v1/consultas/deuda` |
| Cheques | GET | `/api/v1/consultas/cheques` |
| Historial ventas | GET | `/api/v1/consultas/historial-ventas` |
| Detalle historial | GET | `/api/v1/consultas/historial-ventas/{id}/detalle` |

### 5.2 Detalle por operación

Ver TR-SPEC-101-07 para request/response, 401, 403 y envelope.

**UI — metadata carátula:** mostrar `fecha_proceso` del `resultado` cuando API la incluya.

### 5.3 Actualización matriz permisos

- [ ] Coherente con filas introducidas en TR-SPEC-101-07 (sin filas nuevas solo UI)

---

## 6) Cambios Frontend

### Pantallas / componentes
| Proceso / menú | Componente | `grid_id` sugerido |
|----------------|------------|-------------------|
| Pedidos ingresados | `PedidosIngresadosPage` | `pw_pedidosingresados` |
| Pedidos pendientes | `PedidosPendientesPage` | `pw_pedidospendientes` |
| Presup. activos | `PresupuestosActivosPage` | `pw_presupuestosactivos` |
| Presup. cerrados | `PresupuestosCerradosPage` | `pw_presupuestoscerrados` |
| Stock | `ConsultaStockPage` | `pw_stock` |
| Deuda | `ConsultaDeudaPage` | `pw_deuda` |
| Cheques | `ConsultaChequesPage` | `pw_cheques` |
| Historial | `HistorialVentasPage` + `Popup` detalle | `pw_historialventas` |

- Toolbar: layouts, export (`gridExportExcel`), acciones por fila (DevExtreme `Button` / columna acciones)
- Controles DX: filtros `TextBox`, `SelectBox`, `DateBox` — no HTML nativo final

### data-testid sugeridos
- `consultaPedidosIngresadosGrid`, `consultaPresupuestosActivosGrid`
- `gridExportExcel` (GEN-03)
- `consultaHistorialDetallePopup`
- Por acción: `rowActionVer`, `rowActionEditar`, `rowActionEliminar` vía `elementAttr`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Páginas pedidos/presup (015–017) | AC-03, AC-04, AC-05 |
| T2 | Frontend | Stock, deuda, cheques, historial (018, 021–023) | AC-06 |
| T3 | Frontend | Integrar export + layouts GEN-03 | AC-02, AC-07 |
| T4 | Frontend | i18n grilla + acciones | AC-10 |
| T5 | Tests | E2E suite consultas (≥ 2 críticas o agrupada) | AC-09 |
| T6 | Docs | Mapa proceso ↔ grid_id ↔ API | Trazabilidad HU |

---

## 8) Estrategia de Tests

- **Unit:** Mapeo columnas; helpers formato moneda.
- **Integration:** N/A (API en 101-07).
- **E2E:** (1) pedidos ingresados feliz + export; (2) sin permiso edición o grilla vacía export deshabilitado; (3) presupuestos activos vs cerrados; opcional historial modal.

---

## 9) Riesgos y Edge Cases

- Desalineación columnas UI vs API → contrato DTO compartido.
- Muchas grillas → reutilizar `ConsultaGridShell` si existe en 101-09.
- `-1` editable solo dentro ventana `MinutosWeb` — deshabilitar acción en UI si API rechaza.

---

## 10) Checklist final

### Checklist del slice (Bloque 2 — pedidos/presupuestos)
- [x] AC-03 — pedidos ingresados: ver/editar/eliminar(0)/copiar cableados → `/pedidos/carga`
- [x] AC-04 — presupuestos activos: ver/editar/convertir/cerrar/copiar; cerrados solo lectura + detalle cierre
- [x] AC-05 — pedidos pendientes: solo ver
- [x] AC-08 — acciones visibles según flags API (`puede*`)
- [ ] AC-02, AC-09 — E2E export consultas API real (tanda 2)

### Checklist del slice (Bloque 1 — gestión)
- [x] AC-01 — pantallas stock/deuda/cheques/historial con `DataGridDx`
- [x] AC-06 — carátula `fecha_proceso`; historial modal detalle (línea seleccionada)
- [x] AC-07 — layouts `proceso` + `grid_id` por consulta gestión
- [x] AC-10 — i18n columnas + `data-testid` estables
- [x] AC-02 — E2E export Excel mock (`gridExportExcel` en pedidos ingresados)
- [ ] AC-03…05, AC-08, AC-09 — pedidos/presupuestos (Bloques 2–3)

### Checklist del slice (completo épica)
- [ ] AC cumplidos
- [ ] 8 consultas Must con UI
- [ ] Export Excel operativo
- [ ] ≥ 2 E2E documentados

### Checklist normas transversales

- [ ] Endpoints consumidos documentados en 101-07 con policy
- [ ] Matriz coherente (101-07)
- [ ] Envelope respetado en cliente API
- [ ] X-Paq-Cliente en cliente HTTP
- [ ] Sin ampliación de alcance (PDF, DELETE presupuesto)

---

## Archivos creados/modificados

### Bloque 2 (2026-06-02) — pedidos/presupuestos
- `frontend/src/features/consultas/api/consultaApi.ts` — mapeo comprobantes + paths `?estado=99|98`
- `frontend/src/features/consultas/hooks/useComprobanteConsultaActions.ts`
- `frontend/src/features/pedidos/pages/PedidosIngresadosPage.tsx`, `PedidosPendientesPage.tsx`
- `frontend/src/features/presupuestos/pages/PresupuestosPage.tsx`
- `frontend/src/features/presupuestos/components/PresupuestoCierreDialog.tsx`, `PresupuestoCierreDetalleDialog.tsx`
- `frontend/src/features/presupuestos/api/presupuestoApi.ts`
- `frontend/src/features/pedidos/api/comprobanteApi.ts` — `eliminarPedido`
- `frontend/src/features/pedidos/pages/PedidosCargaPage.tsx` — modo `convertir`

### Bloque 1 (2026-06-02) — gestión
- `frontend/src/features/consultas/api/consultaApi.ts` — mapeo API↔UI + `metadata`
- `frontend/src/features/consultas/pages/DeudaPage.tsx`, `StockPage.tsx`, `ChequesPage.tsx`
- `frontend/src/features/consultas/pages/HistorialVentasPage.tsx` — carátula + modal detalle
- `frontend/src/features/consultas/components/ConsultaGridPage.tsx` (consumido)
- `frontend/src/locales/es.json`, `en.json` — `consultas.historialPeriodo`, `consultas.column.descripcion`

### Frontend (implementación previa)
- Páginas consulta bajo `frontend/src/features/consultas/`
- Rutas menú producto §8
- Hooks/queries por endpoint 101-07

### Docs
- Mapa grid_id ↔ proceso: `pw_stock`, `pw_deuda`, `pw_cheques`, `pw_historialventas`
