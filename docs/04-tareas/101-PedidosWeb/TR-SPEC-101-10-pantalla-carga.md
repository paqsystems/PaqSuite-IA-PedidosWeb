# TR-SPEC-101-10 — Pantalla única de carga pedido/presupuesto

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-004](../../03-historias-usuario/101-PedidosWeb/HU-101-004-seleccion-cliente.md) … [HU-101-011](../../03-historias-usuario/101-PedidosWeb/HU-101-011-editar-pedido.md), [HU-101-009](../../03-historias-usuario/101-PedidosWeb/HU-101-009-grabar-pedido.md), [HU-101-010](../../03-historias-usuario/101-PedidosWeb/HU-101-010-grabar-presupuesto.md), [HU-101-013](../../03-historias-usuario/101-PedidosWeb/HU-101-013-conversion-presupuesto-pedido.md), [HU-101-024](../../03-historias-usuario/101-PedidosWeb/HU-101-024-conversion-pedido-presupuesto.md), [HU-101-026](../../03-historias-usuario/101-PedidosWeb/HU-101-026-copiar-comprobante.md) |
| **SPEC relacionada** | [SPEC-101-10-pantalla-carga](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Épica** | 101 — PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | TR-SPEC-101-05 (controllers), TR-SPEC-101-04 (services), TR-SPEC-101-06, TR-SPEC-101-09; SPEC-001-04 (parámetros `Modifica*`); TR-SPEC-101-13 (mail post-grabación) |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 |

**Origen:** HU-101-004 … HU-101-011, HU-101-009, HU-101-010, HU-101-013, HU-101-024, HU-101-026  
**Referencia SPEC:** [SPEC-101-10-pantalla-carga](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md)  
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
- **In scope:** UI DevExtreme; cabecera/renglones/totales; 6 transiciones §10.1; entrada nuevo/edición/copia/consulta; selector cliente; permisos precio/descuento vía parámetros ERP `Modifica*`; `data-testid` estables; integración API SPEC-101-05.
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
| POST | `/api/v1/comprobantes/{cod}/copiar` | `Permiso_Alta` | HU-101-026 |
| POST | `/api/v1/presupuestos/{cod}/convertir-a-pedido` | `Permiso_Modi` | Alternativa explícita T6 si no solo botón |
| POST | `/api/v1/pedidos/{cod}/convertir-a-presupuesto` | `Permiso_Modi` | Alternativa T5 |

*Nota:* T5/T6 pueden resolverse con `POST/PUT` + campo `accionGrabado: pedido|presupuesto` en body unificado — **cerrar en implementación** una sola convención y documentar en OpenAPI.

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

#### GET `/api/v1/comprobantes/{cod}`

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

#### POST `/api/v1/comprobantes/{cod}/copiar`

**Autorización:** `Permiso_Alta` + comprobante visible

**Request:**

```json
{
  "tipoDestino": "pedido",
  "codCliente": "CLI001"
}
```

**Response 200:** payload listo para pantalla (cabecera/renglones sin persistir) o `codComprobante` borrador según diseño — **preferencia MVP:** devolver DTO precargado en `resultado.borrador` sin persistir hasta grabar.

---

#### POST `/api/v1/presupuestos/{cod}/convertir-a-pedido` (opcional si no unificado)

**Autorización:** `Permiso_Modi`; presupuesto **99**

Equivalente a T6 con motivo `CodMotivoCierreExitoso`.

### 5.3 OpenAPI y matriz

- [ ] Todas las operaciones §5.1 en Swagger con `security`, 401, 403, 422
- [ ] Descripción referencia matriz T1–T6 y parámetros `Modifica*`
- [ ] Filas en `matriz-permisos-mvp.md` con TR `TR-SPEC-101-10` / `TR-SPEC-101-05`

---

## 6) Cambios Frontend

### Pantallas / componentes
- `PedidosCargaPage.tsx` — layout único: toolbar botones, form cabecera DevExtreme, grid renglones (`DataGrid` o editor acordado), panel totales.
- Hooks: `useParametrosCarga`, `useComprobante`, `useGrabarComprobante`.
- Integración i18n: `pedidos.carga.*`, botones, validaciones.

### data-testid sugeridos

| Control | data-testid |
|---------|-------------|
| Botón Grabar pedido | `btn-grabar-pedido` |
| Botón Grabar presupuesto | `btn-grabar-presupuesto` |
| Botón Cancelar | `btn-cancelar-carga` |
| Selector cliente | `cliente-select` |
| Form cabecera | `form-cabecera-carga` |
| Grid renglones | `grid-renglones-carga` |
| Campo precio renglón | `renglon-precio` |
| Campo bonificación renglón | `renglon-bonificacion` |
| Lista precios cabecera | `cabecera-lista-precios` |
| Confirmación post-grabación | `dialog-confirmacion-grabar` |
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

---

## 8) Estrategia de Tests

- **Unit:** matriz transiciones; validación campos según `Modifica*`.
- **Integration:** POST pedido/presupuesto 401/403/404/422; conversión T6 cierra 98.
- **E2E:** flujo prioritario SPEC madre; escenario precio deshabilitado cliente; copia comprobante.

---

## 9) Riesgos y Edge Cases

- **R1:** Doble convención API (acción en body vs endpoints `/convertir-*`) — cerrar en T1 backend.
- **R2:** SPEC-001-04 pendiente — stub parametros con defaults documentados.
- **R3:** Estado `-1` bloqueado por ERP — UI debe mostrar mensaje y deshabilitar grabación.
- **R4:** T5 tratamiento pedido origen — alinear con HU-101-024 en service (no dejar pedido 0 editable).
- **R5:** Concurrencia dos pestañas editando mismo código — respuesta conflicto (`error` 4000).

---

## 10) Checklist final

### Checklist del slice
- [ ] Matriz 6 transiciones OK
- [ ] Botones DevExtreme + testids
- [ ] Parámetros `Modifica*` en UI y API
- [ ] E2E camino feliz pedido

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
