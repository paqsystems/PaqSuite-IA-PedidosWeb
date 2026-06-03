# TR-SPEC-101-11 — Consultas UI (DataGrid y export Excel)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-015](../../03-historias-usuario/101-PedidosWeb/HU-101-015-consulta-pedidos-ingresados.md) … [HU-101-023](../../03-historias-usuario/101-PedidosWeb/HU-101-023-historial-ventas.md), [HU-101-028](../../03-historias-usuario/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos.md) |
| **SPEC relacionada** | [SPEC-101-11-consultas-ui](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | TR-SPEC-101-07-consultas-api; TR-SPEC-101-09-frontend-base; [TR-GEN-03-grillas-listados](../001-Generaliddes/TR-GEN-03-grillas-listados.md); [TR-GEN-03-exportaciones](../001-Generaliddes/TR-GEN-03-exportaciones.md); [TR-GEN-03-layouts-grilla](../001-Generaliddes/TR-GEN-03-layouts-grilla.md) |
| **Estado** | **C1 cerrada** — Bloques 1–2 en D; **Bloque 3 apto para D1** (2026-06-03) |
| **Última actualización** | 2026-06-03 (C1 Bloque 3) |

**Origen:** HU-101-015, 016, 017, 018, 021, 022, 023, **028**  
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
- **In scope:** grillas pedidos ingresados (0/-1), pendientes (1), presupuestos activos (99) y cerrados (98), **detalle pedidos (cabecera+renglón)**, stock, deuda, cheques, historial (modal detalle); carátula `fecha_proceso`; export Excel GEN-03; acciones ver/editar/eliminar según permisos (**eliminar** solo pedido estado 0); íconos + tooltip i18n.
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
- **AC-11 (Bloque 3):** Detalle pedidos: grilla plana cabecera+detalle; estado texto i18n; sin acciones fila; export/layouts GEN-03.

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
5. **RN-05:** `fecha_proceso` en carátula desde **`resultado.metadata.fecha_proceso`** (convención TR-101-07 / `consultaApi.ts`).
6. **RN-06:** Historial: período `DiasVentasDetalladas` (API); detalle solo en modal.
7. **RN-07:** Export: alcance página actual salvo documentación explícita por proceso (GEN-03 RN-05).
8. **RN-08 (Bloque 3):** Detalle pedidos: reutilizar `ComprobanteConsultaColumns` + `DetallePedidosConsultaColumns`; `id` fila `{codPedido}-{renglon}`; columna `estado` con `customizeText` i18n; sin columna acciones.

---

## 3.1) Informe C1 — Bloque 3 detalle pedidos (2026-06-03)

**Fuentes revisadas:** HU-101-028, [consulta-detalle-pedidos.md](../../02-producto/PedidosWeb/consulta-detalle-pedidos.md), TR-SPEC-101-07 Bloque 3, `ComprobanteConsultaColumns.tsx` (`estadoCustomizeText`), `ConsultaGridPage.tsx`, `consultaApi.ts`, `paqsuite_mvp.php` (sin `pw_detallepedidos` aún).

> **Nota:** Bloques 1–2 pasaron C1/D1 en 2026-06-02. Esta revisión cubre **solo el delta Bloque 3**.

### Resultado general

- **Estado:** Apto con observaciones
- **Ambigüedades bloqueantes:** 0
- **Puede pasar a D1:** **Sí** — **después** de endpoint TR-101-07 Bloque 3

### Ambigüedades críticas

| ID | Tema | Riesgo | Estado | Resolución (→ D1) |
|----|------|--------|--------|-------------------|
| AMB-C01 | Columnas cabecera | Drift con consultas §17.1–17.3 | **Cerrado** (R-C1-01) | Reutilizar `ComprobanteConsultaColumns` + `extraColumns` detalle. |
| AMB-C02 | Menú seed | Ítem ausente | **Cerrado** (R-C1-02) | `pw_detallepedidos` bajo `grp_pedidos`; ruta `/pedidos/detalle`; orden **15** (tras pendientes). |
| AMB-C03 | Estado numérico vs texto | CA-06 HU-028 | **Cerrado** (R-C1-03) | Columna `estado` **visible** con `estadoCustomizeText` existente; numérica oculta en selector. |
| AMB-C04 | Conflicto nombre `descuento` | Colisión cabecera/detalle | **Cerrado** (R-C1-04) | Detalle usa `dataField=porcBonif`; caption i18n descuento renglón. |
| AMB-C05 | `id` fila grilla | Clave compuesta DX | **Cerrado** (R-C1-05) | `keyExpr` / `id` sintético `{codPedido}-{renglon}` en mapper cliente. |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M01 | Shell página | Reutilizar `ConsultaGridPage` (carátula, export, layouts). |
| AMB-M02 | Visibles iniciales detalle | Seguir producto §4: artículo, cantidad, descuento, precios e importes listados. |
| AMB-M03 | E2E Bloque 3 | ≥1 feliz carga grilla + 1 vacío o sin permiso (AC-09 parcial Bloque 3). |
| AMB-M04 | Filtros UI | `cod_cliente`, `cod_pedido`, `estado`, `q` vía toolbar DX (TextBox/SelectBox). |

### Contradicciones TR ↔ HU ↔ producto

| Contradicción | Resolución |
|---------------|------------|
| RN-05 antiguo `resultado.meta` vs código `metadata` | **Corregido:** usar `resultado.metadata.fecha_proceso`. |
| Producto §3 `estadoTexto` visible vs `ComprobanteConsultaColumns` estado oculto | En `DetallePedidosPage`, pasar prop o wrapper que deje `estado` **visible=true** (customizeText ya existe). |
| §5.1 paths pedidos legacy vs 101-07 | Consumir **`/api/v1/consultas/detalle-pedidos`**; no duplicar paths §5.1 obsoletos en implementación nueva. |

### Supuestos detectados

- Endpoint Bloque 3 disponible antes de merge UI (orden D1: 101-07 → 101-11).
- `fetchDetallePedidos` sigue envelope/paginación de `consultaApi.ts`.
- Export Excel GEN-03 hereda comportamiento de otras consultas vía `ConsultaGridPage`.

### Preguntas para decisión humana

(Ninguna bloqueante — cerradas en §3.2.)

### Veredicto C1

**Apto con observaciones para D1** (Bloque 3; depende TR-101-07 Bloque 3).

---

## 3.2) Resoluciones C1 — pre-D1 (Bloque 3)

| ID | Decisión |
|----|----------|
| R-C1-01 | `DetallePedidosConsultaColumns.tsx` exporta columnas detalle; compone con `ComprobanteConsultaColumns`. |
| R-C1-02 | Seed: `{ menuKey: 'detallePedidos', procedimiento: 'pw_detallepedidos', routeName: '/pedidos/detalle', orden: 15, parent: grp_pedidos }`. |
| R-C1-03 | Estado visible con `estadoCustomizeText`; prohibido mostrar solo número sin i18n. |
| R-C1-04 | `porcBonif` en API/UI; caption `consultas.detalle.column.descuento`. |
| R-C1-05 | Mapper cliente asigna `id: \`${codPedido}-${renglon}\`` para `DataGridDx`. |
| R-C1-06 | Sin columna acciones; sin hooks `useComprobanteConsultaActions`. |
| R-C1-07 | `proceso`/`grid_id`: `pw_detallepedidos` / `pw_detallepedidos`. |
| R-C1-08 | `data-testid`: `page-detalle-pedidos`, `consultaDetallePedidosGrid`. |

---

## 3.3) Plan D1 — Bloque 3 (2026-06-03)

### Alcance entendido

Página consulta solo lectura, columnas cabecera+detalle, menú/ruta, cliente API, i18n, layouts/export GEN-03, E2E mínimo. Sin acciones fila.

### Orden sugerido

```text
1. consultaApi.ts — tipos + fetchDetallePedidos + map id compuesto
2. DetallePedidosConsultaColumns.tsx
3. DetallePedidosPage.tsx (ConsultaGridPage)
4. Ruta React + lazy import
5. paqsuite_mvp.php ítem menú + frontend menú map si aplica
6. i18n consultas.detalle.column.*
7. E2E smoke (supervisor ve filas; sin acciones editar)
```

### Dependencias D1

- **TR-SPEC-101-07 Bloque 3** desplegado (endpoint operativo).
- TR-GEN-03-grillas-listados, exportaciones, layouts.

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
| Detalle pedidos | GET | `/api/v1/consultas/detalle-pedidos` |

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
| Stock | `StockPage` | `pw_stock` — columnas según [`consulta-stock.md`](../../02-producto/PedidosWeb/consulta-stock.md) |
| Deuda | `DeudaPage` | `pw_deuda` — columnas según [`consulta-deuda.md`](../../02-producto/PedidosWeb/consulta-deuda.md) |
| Cheques | `ChequesPage` | `pw_cheques` — columnas según [`consulta-cheques.md`](../../02-producto/PedidosWeb/consulta-cheques.md) |
| Historial | `HistorialVentasPage` + `Popup` detalle | `pw_historialventas` — columnas según [`consulta-historial-ventas.md`](../../02-producto/PedidosWeb/consulta-historial-ventas.md) |
| Detalle pedidos | `DetallePedidosPage` | `pw_detallepedidos` — [`consulta-detalle-pedidos.md`](../../02-producto/PedidosWeb/consulta-detalle-pedidos.md) |

- Toolbar: layouts, export (`gridExportExcel`), acciones por fila (DevExtreme `Button` / columna acciones) — **sin acciones en Detalle pedidos**
- Controles DX: filtros `TextBox`, `SelectBox`, `DateBox` — no HTML nativo final
- Componentes columnas: `ComprobanteConsultaColumns.tsx` + **`DetallePedidosConsultaColumns.tsx`** (nuevo)

### data-testid sugeridos
- `consultaDetallePedidosGrid`, `page-detalle-pedidos`
- `gridExportExcel` (GEN-03)
- `consultaHistorialDetallePopup`
- Por acción: `rowActionVer`, `rowActionEditar`, `rowActionEliminar` vía `elementAttr`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Páginas pedidos/presup (015–017) | AC-03, AC-04, AC-05 |
| T1b | Frontend | `DetallePedidosPage` + columnas detalle (028) | AC-11 |
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

### Checklist del slice (Bloque 3 — detalle pedidos, HU-101-028)
- [x] AC-11 — `DetallePedidosPage` solo lectura; estado texto; export/layouts
- [x] Menú `pw_detallepedidos` + ruta `/pedidos/detalle`
- [x] i18n columnas detalle (`consultas.detalle.column.*`)
- [x] E2E `consultas-d1.spec.ts` (renglón + export vacío)

### Checklist del slice (completo épica)
- [x] AC cumplidos (D1 Bloque 3; E2E API real tanda 2)
- [x] 9 consultas Must con UI (incl. detalle pedidos)
- [x] Export Excel operativo
- [x] ≥ 2 E2E documentados (`consultas-d1.spec.ts` + `mvp-section9`)

### Checklist normas transversales

- [x] Endpoints consumidos documentados en 101-07 con policy
- [x] Matriz coherente (101-07)
- [x] Envelope respetado en cliente API
- [x] X-Paq-Cliente en cliente HTTP
- [x] Sin ampliación de alcance (PDF, DELETE presupuesto)

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

### Bloque 3 (2026-06-03) — detalle pedidos (HU-101-028)
- `frontend/src/features/consultas/pages/DetallePedidosPage.tsx` (nuevo)
- `frontend/src/features/consultas/components/DetallePedidosConsultaColumns.tsx` (nuevo)
- `frontend/src/features/consultas/api/consultaApi.ts` — `fetchDetallePedidos` + tipos
- `backend/config/paqsuite_mvp.php` — ítem menú `pw_detallepedidos`
- `frontend/src/locales/*.json` — `consultas.detalle.*`, `consultas.comprobanteEstado.*`

### Docs
- Mapa grid_id ↔ proceso: `pw_stock`, `pw_deuda`, `pw_cheques`, `pw_historialventas`
- Fuentes de verdad consultas: [`consulta-stock.md`](../../02-producto/PedidosWeb/consulta-stock.md), [`consulta-deuda.md`](../../02-producto/PedidosWeb/consulta-deuda.md), [`consulta-cheques.md`](../../02-producto/PedidosWeb/consulta-cheques.md), [`consulta-historial-ventas.md`](../../02-producto/PedidosWeb/consulta-historial-ventas.md)
