# TR-SPEC-101-10 — Pantalla única de carga pedido/presupuesto

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-004](../../03-historias-usuario/101-PedidosWeb/HU-101-004-seleccion-cliente.md) … [HU-101-011](../../03-historias-usuario/101-PedidosWeb/HU-101-011-editar-pedido.md), [HU-101-009](../../03-historias-usuario/101-PedidosWeb/HU-101-009-grabar-pedido.md), [HU-101-010](../../03-historias-usuario/101-PedidosWeb/HU-101-010-grabar-presupuesto.md), [HU-101-013](../../03-historias-usuario/101-PedidosWeb/HU-101-013-conversion-presupuesto-pedido.md), [HU-101-024](../../03-historias-usuario/101-PedidosWeb/HU-101-024-conversion-pedido-presupuesto.md), [HU-101-026](../../03-historias-usuario/101-PedidosWeb/HU-101-026-copiar-comprobante.md) |
| **SPEC relacionada** | [SPEC-101-10-pantalla-carga](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Épica** | 101 — PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | TR-SPEC-101-05 (controllers), TR-SPEC-101-04 (services), TR-SPEC-101-06, TR-SPEC-101-09; SPEC-001-04 (parámetros `Modifica*`); TR-SPEC-101-13 (mail post-grabación) |
| **Estado** | Finalizado |
| **Última actualización** | 2026-06-17 (CC PQ #6 — disponible base agregado) |

**Origen:** HU-101-004 … HU-101-011, HU-101-009, HU-101-010, HU-101-013, HU-101-024, HU-101-026  
**Referencia SPEC:** [SPEC-101-10-pantalla-carga](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md)  
**Fuente de verdad UI:** [pantalla-carga-comprobante-ui.md](../../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Pantalla única DevExtreme para alta/edición/conversión/copia de pedidos y presupuestos.

### Narrativa
Como **usuario comercial**,  
quiero **cargar o editar pedidos y presupuestos en una sola pantalla con botones “Grabar pedido”, “Grabar presupuesto” y “Cancelar”**,  
para **operar según la matriz de transiciones del producto §10.1 sin pantallas separadas**.

### In scope / Out of scope
- **In scope:** UI DevExtreme; cabecera/renglones/totales; 6 transiciones §10.1; entrada nuevo/edición/copia/consulta; selector cliente (formato y orden CC PQ); permisos precio/descuento vía parámetros ERP `Modifica*`; exclusión artículos BASE (`usa_esc = 'B'`); columna precio neto unitario; `data-testid` estables; integración API SPEC-101-05.
- **Out of scope:** DELETE presupuesto; tratativas (101-12 Should); ABM parámetros ERP; eliminación pedido fuera de estado 0 (HU-101-012 en flujo consulta).

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Una sola ruta `/pedidos/carga` (query: `modo`, `codComprobante`, `tipoOrigen`) cubre pedido y presupuesto.
- **AC-02:** Botones visibles: **Grabar pedido**, **Grabar presupuesto**, **Cancelar** (DevExtreme `Button`).
- **AC-03:** Matriz §10.1 implementada en frontend + validación backend (6 filas).
- **AC-04:** Perfil cliente: sin selector; vendedor/supervisor: SelectBox cliente (HU-101-004).
- **AC-05:** Campos precio/lista/bonificación habilitados según `Modifica*` y `functionalProfile` (cliente **C** siempre lectura en precio/lista/descuento artículo).
- **AC-06:** Autocompletar artículos; totales/IVA en tiempo real (HU-101-006…008).
- **AC-07:** Post-grabación: confirmación GUID/número visible; disparo mail (HU-101-019 / TR-101-13).
- **AC-08:** Copia (HU-101-026): precarga desde `POST .../copiar` o GET detalle + modo copia.
- **AC-09:** Identificación visual pedido vs presupuesto (tonalidad/label i18n).
- **AC-10:** E2E camino feliz §9 SPEC madre: carga + grabar pedido 0.
- **AC-11:** `data-testid` en botones y controles críticos (tabla §6).
- **AC-12:** Si `resultado.mailEnviado === false` tras grabación OK, toast informativo i18n (TR-101-13 §6); `data-testid` `toast-mail-envio-fallido`.

### Escenarios Gherkin

```gherkin
Feature: Pantalla única carga

  Scenario: Alta nuevo pedido
    Given pantalla en modo "nuevo" sin comprobante previo
    When el usuario completa cabecera y un renglón
    And pulsa "Grabar pedido"
    Then se crea comprobante estado 0
    And muestra confirmación con número visible y sufijo GUID

  Scenario: Conversión presupuesto 99 a pedido
    Given un presupuesto estado 99 abierto en edición
    When pulsa "Grabar pedido"
    Then se crea pedido estado 0
    And el presupuesto origen pasa a estado 98 con cierre registrado

  Scenario: Cliente no edita precio
    Given usuario functionalProfile "cliente"
    When abre renglón de artículo
    Then los campos precio y bonificación artículo están deshabilitados
```

---

## 3) Reglas de Negocio

### 3.1 Matriz de transiciones (producto §10.1 — 6 combinaciones)

| # | Situación de partida | Acción usuario | Resultado |
|---|----------------------|----------------|-----------|
| T1 | Alta nueva | Grabar pedido | Pedido **0** (nuevo código) |
| T2 | Alta nueva | Grabar presupuesto | Presupuesto **99** (nuevo código) |
| T3 | Pedido **0** (o **-1** editable) en edición | Grabar pedido | Pedido **0** actualizado (mismo código) |
| T4 | Presupuesto **99** en edición | Grabar presupuesto | Presupuesto **99** actualizado (mismo código) |
| T5 | Pedido **0** en edición | Grabar presupuesto | Presupuesto **99** nuevo; pedido origen deja de ser operable como ingresado (§15.2 / HU-101-024) |
| T6 | Presupuesto **99** en edición | Grabar pedido | Pedido **0** nuevo; presupuesto origen → **98** + `presupuestos_cierres` (HU-101-013) |

**Cancelar:** descarta cambios no confirmados y vuelve a ruta anterior (consulta o dashboard) sin persistir.

### 3.2 Casos de entrada (§10.2)

- Nuevo (sin `codComprobante`).
- Edición pedido **0** / **-1** (reglas bloqueo `-1` + `MinutosWeb` en TR-101-14/04).
- Edición presupuesto **99**.
- Copia desde comprobante visible (HU-101-026).
- Deep link desde consulta (TR-101-11) con query acordada.

### 3.3 Parámetros ERP — permisos precio/descuento (§10.6–10.7)

Leídos en runtime (SPEC-001-04) según `functionalProfile`:

| Ámbito | Vendedor (`V`) | Supervisor (`S`) | Cliente (`C`) |
|--------|----------------|------------------|---------------|
| Precio renglón | `ModificaPrecioV` | `ModificaPrecioS` | No |
| Bonif. artículo | `ModificaBonArtV` | `ModificaBonArtS` | No |
| Bonif. cabecera cliente | `ModificaBonCliV` | `ModificaBonCliS` | No |
| Lista precios cabecera | `ModificaListaPrecV` | `ModificaListaPrecS` | No |

- UI: `readOnly`/`disabled` en DevExtreme cuando parámetro ≠ habilitado.
- Backend: rechazar payload con campos alterados si no permitido (`error` 2000, clave `business.*`).

### 3.4 Otras reglas

1. **RN-01:** ≥ 1 renglón y cabecera obligatoria para grabar.
2. **RN-02:** Conversión T6: `CodMotivoCierreExitoso` en cierre (parámetro ERP).
3. **RN-03:** Trazabilidad T6: `cod_presupuesto_origen` en pedido; `cod_pedido_generado` en cierre.
4. **RN-04:** T5: `cod_pedido_origen` en presupuesto nuevo.
5. **RN-05:** Secuencia número visible única por tenant (pedido y presupuesto).
6. **RN-06:** Prohibido `DELETE` presupuesto.

---

## 4) Impacto en Datos

### Tablas afectadas
- `pq_pedidosweb_pedidoscabecera`, `pq_pedidosweb_pedidosdetalle`
- `pq_pedidosweb_presupuestos_cierres`, `pq_pedidosweb_motivos_cierre`
- Parámetros generales ERP (`Modifica*`, `CodMotivoCierreExitoso`, `CargaRecurrente`)

### Seed mínimo para tests
- Cliente con condición venta, transporte, lista precios
- Artículos para autocompletar
- Parámetros `ModificaPrecioV=1`, `ModificaPrecioV=0` para dos escenarios
- Motivo cierre exitoso en catálogo

---

## 5) Contratos de API y OpenAPI

> Controllers delgados (SPEC-101-05); reglas en services (SPEC-101-04). Envelope MONO obligatorio.

**Headers:** `Authorization`, `X-Paq-Cliente`, `Content-Type: application/json`

### 5.1 Endpoints del slice (carga / comprobantes)

| Método | Path | Permiso | Uso pantalla |
|--------|------|---------|--------------|
| GET | `/api/v1/clientes` | `Permiso_Repo` | Selector cliente |
| GET | `/api/v1/clientes/{cod}/cabecera-inicial` | `Permiso_Repo` | Init cabecera HU-101-005 |
| GET | `/api/v1/articulos` | `Permiso_Repo` | Autocompletar |
| GET | `/api/v1/comprobantes/{cod}` | `Permiso_Repo` | Edición / copia |
| GET | `/api/v1/config/parametros-carga` | Autenticado | `Modifica*` + flags UI |
| POST | `/api/v1/pedidos` | `Permiso_Alta` | T1, T3, T6 (alta/conversión) |
| PUT | `/api/v1/pedidos/{cod}` | `Permiso_Modi` | T3 |
| POST | `/api/v1/presupuestos` | `Permiso_Alta` | T2, T4, T5 |
| PUT | `/api/v1/presupuestos/{cod}` | `Permiso_Modi` | T4 |
| POST | `/api/v1/comprobantes/copiar` | `Permiso_Alta` | HU-101-026 — borrador sin persistir (TR-101-05) |
| POST | `/api/v1/comprobantes/grabar` | `Permiso_Alta` / `Modi` | T1–T6 — **canónico** grabación y conversiones (TR-101-05) |

*Nota:* Los botones «Grabar pedido» / «Grabar presupuesto» y las conversiones T5/T6 invocan **`POST /api/v1/comprobantes/grabar`** con `accionGrabacion` y `cod_*_origen`. **No** usar rutas `.../convertir-a-*` en MVP.

### 5.2 Detalle por operación

#### GET `/api/v1/config/parametros-carga`

**Autorización:** usuario autenticado

**Response 200 `resultado`:**

```json
{
  "modificaPrecio": true,
  "modificaBonArt": false,
  "modificaBonCli": true,
  "modificaListaPrec": true,
  "functionalProfile": "vendedor",
  "codMotivoCierreExitoso": "MOT-OK-01"
}
```

Mapeo interno desde `ModificaPrecioV/S`, `ModificaBonArtV/S`, `ModificaBonCliV/S`, `ModificaListaPrecV/S` según perfil.

---

#### GET `/api/v1/clientes/{cod}/cabecera-inicial`

**Autorización:** `Permiso_Repo` + cliente visible

**Response 200 `resultado` (resumen):**

```json
{
  "cabecera": {
    "cod_cliente": "CLI001",
    "cod_perfil": "MVP",
    "lista_precios": 1,
    "bonif_1": 0
  },
  "catalogos": {
    "condicionesVenta": [{ "codigo": 1, "descripcion": "Contado" }],
    "transportes": [],
    "listasPrecios": [{ "cod_lista": 1, "descripcion": "Lista 1", "moneda": 1, "incluye_iva": false }],
    "direccionesEntrega": [],
    "perfiles": [{ "cod_perfil": "MVP", "descripcion": "Perfil estándar" }]
  }
}
```

- **`cabecera.cod_perfil`:** valor inicial = parámetro ERP `CodPerfilPedidos` (`PedidosWebParameterService`).
- **`catalogos.perfiles`:** listado completo de `pq_pedidosweb_perfil` (orden `descripcion` ASC).
- UI: `SelectBox` `cabecera-perfil` — ver [pantalla-carga-comprobante-ui.md](../../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) §5.

---

**Autorización:** `Permiso_Repo` + visibilidad

**Response 200:** cabecera + `renglones[]` + `tipoComprobante` (`pedido`|`presupuesto`), `estado`, flags `puedeGrabarPedido`, `puedeGrabarPresupuesto` derivados de matriz §3.1.

**Response 404:** fuera de visibilidad o inexistente.

---

#### POST `/api/v1/pedidos`

**Autorización:** `Permiso_Alta` + cliente visible

**Request (resumen):**

```json
{
  "accion": "grabar_pedido",
  "codCliente": "CLI001",
  "cabecera": { "condicionVenta": "...", "listaPrecios": "...", "bonificaciones": {} },
  "renglones": [
    { "codArticulo": "ART1", "cantidad": 1, "precio": 100, "bonificacion": 0 }
  ],
  "codPresupuestoOrigen": null,
  "codPedidoOrigen": null
}
```

**Reglas:** validar `Modifica*`; T6 incluye `codPresupuestoOrigen`; ejecutar cierre 98 + `presupuestos_cierres`.

**Response 200:**

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "codPedido": "PED-000123",
    "numeroVisible": 123,
    "guidSufijo": "A1B2",
    "estado": 0
  }
}
```

**Response 403:** sin permiso. **Response 422:** validación. **Response 2000:** regla negocio (estado no permitido, precio no modificable).

---

#### PUT `/api/v1/pedidos/{cod}`

**Autorización:** `Permiso_Modi` + visibilidad + estado editable (0 / -1 según reglas)

**Request:** mismo schema; `accion: grabar_pedido` para T3.

---

#### POST `/api/v1/presupuestos`

**Autorización:** `Permiso_Alta`

**Request:** `accion: grabar_presupuesto`; T2 alta; T5 incluye `codPedidoOrigen`.

**Response 200:** `codPresupuesto`, `estado: 99`, número visible, guid.

---

#### PUT `/api/v1/presupuestos/{cod}`

**Autorización:** `Permiso_Modi`; solo estado **99** (T4).

---

#### POST `/api/v1/comprobantes/copiar`

**Autorización:** `Permiso_Alta` + comprobante origen visible

**Request:**

```json
{
  "codComprobanteOrigen": "guid-origen",
  "tipoDestino": "pedido",
  "codCliente": "CLI001"
}
```

**Response 200:** `resultado.borrador` (cabecera, renglones, `tipoComprobante`, `codComprobanteOrigen`) **sin persistir** hasta `POST /api/v1/comprobantes/grabar` (cerrado D1 2026-06-02; HU-101-026).

---

#### POST `/api/v1/comprobantes/grabar` (grabación / conversión desde pantalla)

Ver contrato canónico en [TR-SPEC-101-05-controllers-rest](TR-SPEC-101-05-controllers-rest.md) §5.2. La pantalla envía cabecera + renglones + `accionGrabacion` + orígenes T5/T6.

### 5.3 OpenAPI y matriz

- [ ] Todas las operaciones §5.1 en Swagger con `security`, 401, 403, 422
- [x] `GET /config/parametros-carga` y `GET /articulos` en OpenAPI
- [ ] Filas en `matriz-permisos-mvp.md` con TR `TR-SPEC-101-10` / `TR-SPEC-101-05`

---

## 6) Cambios Frontend

### Pantallas / componentes
- `PedidosCargaPage.tsx` — layout único: toolbar botones, form cabecera DevExtreme, grid renglones (`DataGrid` o editor acordado), panel totales.
- Hooks: `useParametrosCarga`, `useComprobante`, `useGrabarComprobante`.
- Integración i18n: `pedidos.carga.*`, botones, validaciones.
- Post-grabación: si `mailEnviado === false`, toast informativo (ver TR-101-13 §6).

### data-testid sugeridos

| Control | data-testid |
|---------|-------------|
| Botón Grabar pedido | `btn-grabar-pedido` |
| Botón Grabar presupuesto | `btn-grabar-presupuesto` |
| Botón Cancelar | `btn-cancelar-carga` |
| Selector cliente | `cliente-select` |
| Orden clientes | `cliente-orden-select` |
| Columna precio neto unitario | `renglon-precio-neto-unitario` |
| Form cabecera | `form-cabecera-carga` |
| Grid renglones | `grid-renglones-carga` |
| Campo precio renglón | `renglon-precio` |
| Campo bonificación renglón | `renglon-bonificacion` |
| Lista precios cabecera | `cabecera-lista-precios` |
| Confirmación post-grabación | `dialog-confirmacion-grabar` |
| Toast fallo envío mail | `toast-mail-envio-fallido` |
| Contenedor página | `page-pedidos-carga` |

Usar `elementAttr` / `inputAttr` DevExtreme; no acoplar tests al DOM interno DX.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Services transiciones T1–T6 + validación `Modifica*` | Unit tests matriz |
| T2 | Backend | Endpoints §5.1 + OpenAPI | Feature tests |
| T3 | Frontend | Pantalla DevExtreme + parametros | AC-02, AC-05 |
| T4 | Frontend | Flujos edición/copia/deep link | AC-08 |
| T5 | Tests | E2E §9 madre + conversión T6 | Playwright verde |
| T6 | Integración | Mail post-grabación (TR-101-13) | Log/mock |

### 7.1 Control de calidad PQ 04/06/2026 (cerrado)

| ID | Ámbito | Tarea | DoD |
|----|--------|-------|-----|
| T1 | API cliente | `displayExpr` `(codigo) {razonSocial} - {nombreFantasia}`; sort código/razón/fantasía | [x] FE `cargaCatalogos` + SelectBox |
| T2 | UI cabecera | Bonificación 3: rango **-99,99..99,99** | [x] `NumberBox` validación |
| T3 | API artículos | Excluir `usa_esc = 'B'` en browse carga | [x] scope `excluirArticulosBaseCarga` |
| T4 | Dominio | `calcularPrecioNetoUnitario` (lista, bonif renglón, bonif cabecera) | [x] FE + BE |
| T5 | UI grilla | Columna precio neto unitario; recálculo al cambiar cabecera/lista | [x] grilla + hooks |
| T6 | BD | Persistir en `precio_neto` existente (sin migración) | [x] `PedidoService` |
| T7 | Tests | Unit cálculo + scope BASE | [x] FE/BE tests |

**AC-CC:** CA-CC-01..03 de HU-101-004/005/006 cubiertos por implementación y tests donde aplica.

### 7.2 Control de calidad PQ 09/06/2026 (cerrado)

| ID | Ámbito | Tarea | DoD |
|----|--------|-------|-----|
| T1 | UI cliente | `SelectBoxDx` + cache sesión `fetchClientes` | [x] `PedidosCargaPage.tsx`, `comprobanteApi.ts` |
| T2 | UI artículos | Display `codigo - descripcion`; búsqueda lazy (mín. 4 chars; apertura 1 s) | [x] `useArticulosCargaDataSource.ts`, `cargaCatalogos.ts` |
| T3 | Precios | Recálculo batch al cambiar lista (`actualizarPreciosRenglonesPorLista`) | [x] FE + API `codigos` |
| T4 | Tests | Vitest + E2E carga grabar | [x] `mvp-section9.spec.ts` |
| T5 | Manual | `PedidosWeb.md` §6.2–6.7 | [x] Parte I |

**AC-CC3:** CA-CC3-01..06 de HU-101-005 cubiertos; QA manual PQ aprobado 09/06/2026.

### 7.3 Control de calidad PQ #5 (09/06/2026 — cerrado; fórmulas revisadas en CC PQ #6)

| ID | Ámbito | Tarea | DoD |
|----|--------|-------|-----|
| T1 | API artículos | Browse `GET /articulos` → `ArticuloCargaLookupService::buscar` con `disponibleNeto` y `disponibleNetoBase` | [x] |
| T2 | Producto | `pantalla-carga-comprobante-ui.md` §3.1 — fórmulas disponible artículo y base | [x] |
| T3 | Tests | PHPUnit lookup artículos + consulta stock | [x] |
| T4 | Manual | Display listbox con disponible y base entre paréntesis | [x] Parte I |

*Nota CC PQ #6 (17/06/2026):* disponible base corregido — agregado SUM por `base`; ver §7.4.

**AC-CC5:** CA-CC5-01..04 de HU-101-005 cubiertos; PHPUnit + QA manual PQ 11/06/2026.

### 7.4 Control de calidad PQ #6 (17/06/2026 — cerrado)

| ID | Ámbito | Tarea | DoD |
|----|--------|-------|-----|
| T1 | API artículos | `ArticuloCargaLookupService`: `disponibleNetoBase` con subconsulta `SUM(stock)` / `SUM(comprometido)` agrupada por `articulos.base` (no join `stock.cod_articulo = a.base`) | [x] alineado `StockConsultaService` §5 |
| T2 | Producto | `pantalla-carga-comprobante-ui.md` §3.1 + `consulta-stock.md` §5 — regla SUM explícita | [x] |
| T3 | Tests | `ArticuloCargaLookupServiceTest` — SQL con `[bs]` / `stock_base`; test funcional agregado base | [x] |
| T4 | Manual | Paréntesis listbox muestra disponible neto base (ej. AC01 → 177.100), no `comprometidoBaseWeb` | [x] QA PQ |

**AC-CC6:** CA-CC6-01..03 de HU-101-005.

---

## 8) Estrategia de Tests

- **Unit:** matriz transiciones; validación campos según `Modifica*`.
- **Integration:** POST pedido/presupuesto 401/403/404/422; conversión T6 cierra 98.
- **E2E:** flujo prioritario SPEC madre; escenario precio deshabilitado cliente; copia comprobante.

---

## 9) Riesgos y Edge Cases

- **R1:** Convención API única: `comprobantes/grabar` + `comprobantes/copiar` (TR-101-05; cerrado D1 2026-06-02).
- **R2:** SPEC-001-04 pendiente — stub parametros con defaults documentados.
- **R3:** Estado `-1` bloqueado por ERP — UI debe mostrar mensaje y deshabilitar grabación.
- **R4:** T5 tratamiento pedido origen — alinear con HU-101-024 en service (no dejar pedido 0 editable).
- **R5:** Concurrencia dos pestañas editando mismo código — respuesta conflicto (`error` 4000).

---

## 10) Checklist final

### Checklist del slice
- [x] Matriz 6 transiciones OK (UI visibilidad botones + backend `grabarComprobante`)
- [x] Botones DevExtreme + testids
- [x] Parámetros `Modifica*` en UI y API
- [x] E2E camino feliz pedido (mock parametros-carga + articulos)

### Checklist normas transversales

- [ ] Endpoints con policy en código
- [ ] Matriz permisos actualizada
- [ ] OpenAPI coherente
- [ ] 401/403 documentados
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado
- [ ] Tests API 401/403
- [ ] Sin ampliación fuera de SPEC/HU/TR

---

## Archivos creados/modificados

(Post-implementación)

### Backend
- `PedidoController`, `PresupuestoController`, `ComprobanteController`
- `PedidoCargaService`, DTOs request/response
- `tests/Feature/Api/PedidosCargaTest.php`

### Frontend
- `frontend/src/features/pedidos/pages/PedidosCargaPage.tsx`
- Componentes cabecera/renglones
- `frontend/tests/e2e/pedidos-carga.spec.ts`

### OpenAPI / Docs
- Matriz permisos
- Enlace TR-SPEC-101-13 (mail)
