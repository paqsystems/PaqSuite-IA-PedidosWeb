# Pantalla de carga de comprobante — UI (fuente de verdad)

| Campo | Valor |
|-------|--------|
| **Estado** | Vigente |
| **Ámbito** | Frontend + contratos API cabecera/renglones |
| **Ruta** | `/pedidos/carga` |
| **TR** | [TR-SPEC-101-10-pantalla-carga](../../04-tareas/101-PedidosWeb/TR-SPEC-101-10-pantalla-carga.md) |
| **SPEC** | [SPEC-101-10-pantalla-carga](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Última actualización** | 2026-06-09 (CC PQ #3) |

Este documento es la **fuente de verdad** para comportamiento de UI de la pantalla única de carga/edición de pedidos y presupuestos. Ante conflicto con implementaciones antiguas, prevalece este archivo.

---

## 1. Componentes frontend

| Componente | Archivo |
|------------|---------|
| Página | `frontend/src/features/pedidos/pages/PedidosCargaPage.tsx` |
| Cabecera | `frontend/src/features/pedidos/components/ComprobanteCabeceraForm.tsx` |
| Grilla renglones | `frontend/src/features/pedidos/components/PedidosCargaRenglonesGrid.tsx` |
| Edición renglón (popup) | `frontend/src/features/pedidos/components/PedidosCargaRenglonEditDialog.tsx` |
| Constantes cabecera | `frontend/src/features/pedidos/constants/cabeceraCatalogos.ts` |
| Catálogos / formato combobox | `frontend/src/features/pedidos/utils/cargaCatalogos.ts` |
| Cálculos importes | `frontend/src/features/pedidos/utils/renglonesCarga.ts` |
| Leyendas pie | `frontend/src/features/pedidos/components/ComprobanteLeyendasPie.tsx` |
| Precios por lista | `frontend/src/features/pedidos/utils/actualizarPreciosRenglones.ts` |

Controles: **DevExtreme** (`SelectBox`, `NumberBox`, `DataGrid`, `Popup`, `Button`).

---

## 2. Combobox — Cliente

| Regla | Detalle |
|-------|---------|
| Control | `SelectBoxDx` (`data-testid`: `cliente-select`) — wrapper transversal CC3 |
| API | `GET /api/v1/clientes` (cache en memoria por sesión tras primer fetch) |
| Estado carga | Hint i18n `selectBox.loading` + control deshabilitado durante fetch inicial |
| Auto-match | Si la búsqueda deja un único ítem, se selecciona automáticamente |
| Valor inicial | **Vacío** (`null`) en modo nuevo para perfiles vendedor/supervisor; el usuario debe elegir cliente |
| Perfil cliente | Cliente fijo de sesión (sin combobox) |
| Orden | Selector `cliente-orden-select`: **código**, **razón social** o **nombre fantasía** (ascendente); default razón social |
| Etiqueta visible | `(codigo) {razonSocial} - {nombreFantasia}` (`displayExpr`); si falta fantasía, segmento final vacío o omitido según i18n |
| Búsqueda | `searchEnabled`, `searchExpr`: `razonSocial`, `nombre`, `nombreFantasia`, `codCliente` |
| Limpiar | `showClearButton` cuando no es solo lectura; al limpiar se resetean cabecera, catálogos, artículos y renglones |
| Placeholder | `pedidos.carga.clientePlaceholder` |

---

## 3. Combobox — Artículos

| Regla | Detalle |
|-------|---------|
| Control | `SelectBoxDx` (`data-testid`: `articulo-select`) |
| API | `GET /api/v1/articulos?q=&lista_precios={cod_lista}&page_size=500` |
| Carga de datos | **DevExtreme `CustomStore`** (`useArticulosCargaDataSource`): consulta remota con `q` (trim en API); **no** consulta al solo enfocar el campo (`openOnFieldClick=false`) |
| Umbral búsqueda | Mínimo **4** caracteres tipeados (espacios incluidos); tras **1 s** sin tipear con umbral cumplido → `component.open()` |
| Flecha desplegable | Sin texto escrito: carga primer lote (hasta **500** ítems; `page_size` máx. **1000** en API) |
| Estado carga | Hint i18n `selectBox.loading` durante fetch (`onLoadingChanged`); en artículos **no** se deshabilita al buscar (`disableWhileLoading=false`) |
| Auto-match | Si la búsqueda deja un único ítem (≥ 4 caracteres), selección automática (`autoSelectMinSearchLength=4`) |
| Lista precios | Sin `listaPrecios` válida en cabecera el combobox queda deshabilitado (sin `DataSource`); al cambiar lista → `actualizarPreciosRenglonesPorLista` (batch `codigos`) |
| Orden | **`descripcion` ASC** (API `orderBy('descripcion')` + `ordenarArticulosPorDescripcion` en cliente) |
| Búsqueda | Escribir filtra en servidor (`q`); `searchExpr` código/descripción; `cacheRawData=false` |
| Exclusión BASE | No listar artículos con `pq_pedidosweb_articulos.usa_esc = 'B'` (solo lookup/browse; refresh por `codigos` no aplica este filtro) |
| Formato ítem | Ver §3.1 |
| Precio al agregar | Campo `precio` de la respuesta (lista activa en cabecera) |
| `porc_iva` | Normalizar a escala 0–100 con `normalizarPorcIvaAlmacenado` (0.21 → 21) |

### 3.1 Texto del ítem (disponible neto)

Misma lógica que [consulta-stock.md](./consulta-stock.md) (`StockConsultaService::lookupDisponibilidadPorCodigos`):

- **Disponible neto** = `stock − comprometido − comprometido_web` (pedidos `estado = 0`).
- **Disponible neto base** = agregado por `articulos.base` cuando `base` no está vacío; si no hay base → `null` (no se muestra paréntesis).

| Caso | Plantilla i18n |
|------|----------------|
| Sin base | `pedidos.carga.articuloDisplay` → `{{codigo}} - {{descripcion}} — Disp. {{disponible}}` |
| Con base | `pedidos.carga.articuloDisplayConBase` → `{{codigo}} - {{descripcion}} — Disp. {{disponible}} ({{disponibleBase}})` |

Cantidades con 2 decimales (`es-AR`). Campos API en `ArticuloOption`: `disponibleNeto`, `disponibleNetoBase`.

---

## 4. Barra de acciones (toolbar)

Layout en **tres zonas** (`pedidosCargaPage__toolbar`):

| Zona | Contenido | Alineación |
|------|-----------|------------|
| Izquierda | **Cancelar** | `toolbarLeft` |
| Centro | **Grabar presupuesto** (si aplica) | `toolbarCenter` |
| Derecha | **Grabar pedido** (si aplica) | `toolbarRight` |

`data-testid`: `btn-cancelar-carga`, `btn-grabar-presupuesto`, `btn-grabar-pedido`.

---

## 5. Cabecera — Perfil de pedido

| Regla | Detalle |
|-------|---------|
| Control | **DevExtreme `SelectBox`** (`data-testid`: `cabecera-perfil`) |
| Campo persistido | `pq_pedidosweb_pedidoscabecera.cod_perfil` |
| Origen catálogo | `pq_pedidosweb_perfil` → `catalogos.perfiles` en `GET /api/v1/clientes/{cod}/cabecera-inicial` (y catálogos al editar/copiar comprobante) |
| Ítem catálogo | `{ cod_perfil, descripcion }` |
| Formato visible | `{cod_perfil} — {descripcion}` |
| Búsqueda | `searchEnabled`; `searchExpr`: `cod_perfil`, `descripcion` |
| Valor inicial (nuevo) | Parámetro ERP **`CodPerfilPedidos`** (`CabeceraInicialService` → `cod_perfil` en cabecera) |
| Edición / copia | Valor del comprobante origen |
| Modo solo lectura | `readOnly` cuando `modo=ver` |
| Permisos | Editable en carga/edición salvo solo lectura; **no** depende de `Modifica*` (producto §10.5–§10.7 no define bloqueo de perfil) |
| Grabación | `cod_perfil` en body cabecera (`mapCabeceraToApi`) |

Ubicación UI: después de **Vendedor**, antes de **Condición de venta**.

---

## 6. Cabecera — Lista de precios

- Control: **DevExtreme `SelectBox`** (combobox con búsqueda).
- Origen: `pq_pedidosweb_listaprecios` → `catalogos.listasPrecios`.
- `displayExpr`: `{cod_lista} — {descripcion}`; editable según `modificaListaPrec`.
- `data-testid`: `cabecera-lista-precios`.
- **Al cambiar lista:** actualizar `moneda` / `incluye_iva` y **recalcular precio** de cada renglón con artículo vía `GET /api/v1/articulos?codigos={csv}&lista_precios={cod}` (`actualizarPreciosRenglonesPorLista`).

---

## 7. Cabecera — Moneda

- Solo lectura; `0` → Moneda Extranjera, `1` → Moneda Corriente (`pedidos.carga.moneda.*`).
- `data-testid`: `cabecera-moneda`.

---

## 8. Cabecera — Bonificaciones 1, 2 y 3

- `NumberBox` formato `#,##0.00`; habilitadas según `modificaBonCli`.
- **Bonificación 3:** rango **-99,99 a 99,99** (negativos permitidos).
- **Bonificación neta**: `calcularBonificacionNeta(bonif1, bonif2, bonif3)` — solo lectura.
- Al cambiar lista de precios o bonificaciones con renglones cargados → recalcular precios e importes del detalle.

---

## 9. Leyendas al pie (1 a 5)

| Regla | Detalle |
|-------|---------|
| Ubicación | Debajo del layout principal / totales (`ComprobanteLeyendasPie`) |
| Controles | 5 × `TextBox` editables (si no es solo lectura) |
| Inicialización | Parámetros ERP `ClienteLeyenda1` … `ClienteLeyenda5` (API `parametros-carga`) |
| Origen valores | Si `ClienteLeyendaN` = true → `pq_pedidosweb_clientes.leyenda_N` en cabecera inicial; si false → vacío |
| Persistencia | `leyenda_1` … `leyenda_5` en grabación (`mapCabeceraToApi`) |

`data-testid`: `leyendas-pie`, `leyenda-1` … `leyenda-5`.

---

## 10. Renglones — Grilla, popup e importes

### Grilla

- Columna **Precio neto unitario** (solo lectura): precio lista − descuento renglón − descuento cabecera; `data-testid`: `renglon-precio-neto-unitario`.
- Columna **Importe neto** (con bonificación neta de cabecera).
- Acciones: íconos `edit` / `trash`.
- Persistencia: `pq_pedidosweb_pedidosdetalle.precio_neto` al grabar/actualizar.

### Popup edición (§9.1)

| Campo | Regla |
|-------|--------|
| Importe bruto | `precio × cantidad × (1 − bonif_renglón / 100)` |
| Importe neto | × `(1 − bonif_neta_cabecera / 100)` |
| Importe IVA | `importe_neto × factorPorcIva(porc_iva)` |
| Importe neto c/IVA | neto + IVA (resaltado) |

Layout importes: grilla **2×2** (bruto | neto / IVA | neto c/IVA).

### 9.1 IVA — `porc_iva`

| Regla | Detalle |
|-------|---------|
| Almacenamiento UI | Porcentaje 0–100 (ej. **21** = 21 %) |
| Normalización entrada | Si ERP envía fracción (`0.21`), `normalizarPorcIvaAlmacenado` → `21` |
| Cálculo | `factorPorcIva` = `porcentaje / 100` → importe IVA = neto × factor |
| Modelo datos | `pq_pedidosweb_articulos.porc_iva` / detalle: porcentaje (ver modelo datos) |

**Importante:** el importe IVA **siempre** aplica división por 100 cuando el valor está en escala porcentual (≥ 1).

`data-testid` popup: `renglon-importe-bruto`, `renglon-importe-neto`, `renglon-importe-iva`, `renglon-importe-neto-con-iva`.

---

## 11. Totales del comprobante

| Etiqueta | Cálculo |
|--------|--------|
| **Subtotal** | Σ importe neto por renglón |
| **IVA** | Σ importe IVA por renglón |
| **Total** | Σ importe neto c/IVA |

`data-testid`: `totales-subtotal`, `totales-iva`, `totales-total`.

---

## 12. Agregar artículo

1. Elegir artículo en combobox y **Agregar artículo**.
2. Renglón con precio de lista y `porc_iva` normalizado.
3. **Abrir popup** de edición del renglón nuevo (`autoOpenRenglonId`).
4. No duplicar `cod_articulo` en el mismo comprobante.

---

## 13. Descuento por cantidad (renglón)

Fuente de datos: `pq_pedidosweb_descuentocantidad` (`cod_articu`, `cantidad`, `descuento`).

| Momento | Regla |
|---------|--------|
| **Al agregar renglón** | Descuento inicial = `pq_pedidosweb_articulos.bonificacion` del artículo elegido |
| **Al cambiar cantidad** | Buscar filas del artículo con `cantidad <= cantidad ingresada`; tomar la de **mayor** `cantidad`; si hay match, aplicar su `descuento`; si no, **no cambiar** el descuento actual del renglón |
| **Permisos** | La regla por cantidad se ejecuta **siempre** al cambiar cantidad, independientemente de `ModificaBonArt*` |
| **Edición manual** | Solo si `ModificaBonArtV` / `ModificaBonArtS` lo permiten |

Backend de referencia: `ArticuloRepository::findDescuentoCantidad` (`where cantidad <=`, `orderByDesc cantidad`, `first`).

Persistir `descuento_origen` en detalle cuando exista columna (`cantidad` | `articulo` | `manual`).

**Estado implementación:** pendiente en frontend (popup/grilla carga); reglas ya documentadas en producto §11.2.

---

## 14. APIs relacionadas

| Recurso | Endpoint |
|---------|----------|
| Clientes visibles | `GET /api/v1/clientes` |
| Cabecera inicial | `GET /api/v1/clientes/{cod}/cabecera-inicial` |
| Artículos carga | `GET /api/v1/articulos?lista_precios=` |
| Stock (lookup artículos) | Misma lógica que `GET /api/v1/consultas/stock` vía `StockConsultaService` |
| Grabación | `POST /api/v1/comprobantes/grabar` |

---

## 15. i18n (claves mínimas)

| Clave | Uso |
|-------|-----|
| `pedidos.carga.cabecera.perfil` | Combobox perfil de pedido |
| `pedidos.carga.clientePlaceholder` | Placeholder cliente |
| `pedidos.carga.seleccioneCliente` | Sin cliente seleccionado |
| `pedidos.carga.articuloDisplay` / `articuloDisplayConBase` | Ítem combobox artículos |
| `pedidos.carga.renglon.importeBruto` … `importeNetoConIva` | Popup renglón |
| `pedidos.carga.moneda.extranjera` / `corriente` | Moneda cabecera |

---

## 16. Checklist de verificación manual

- [ ] Perfil de pedido: combobox con catálogo `pq_pedidosweb_perfil`; valor inicial `CodPerfilPedidos`; persiste `cod_perfil`.
- [ ] Cliente inicia vacío (vendedor/supervisor); orden por razón social.
- [ ] Artículos ordenados por descripción; texto con disponible neto (y base entre paréntesis si aplica).
- [ ] IVA del renglón coherente con 21 % (no multiplicar por 21 sin dividir).
- [ ] Popup: importes bruto/neto/IVA/neto c/IVA en 2 columnas; totales = Σ neto / Σ IVA / Σ neto c/IVA.
- [ ] Toolbar: Cancelar izquierda, presupuesto centro, pedido derecha.
- [ ] Leyendas 1–5 al pie; inicialización según `ClienteLeyendaN`.
- [ ] Cambio de lista de precios actualiza precios en grilla.
- [ ] Descuento por cantidad al modificar cantidad (§12).
- [ ] Lista de precios, moneda, bonificaciones y agregar artículo según secciones anteriores.
- [ ] **Edición / ver / copia / convertir:** cabecera y renglones del comprobante origen (§17); no cabecera-inicial del cliente ni grilla vacía.

---

## 17. Modos edición, ver, copia y convertir

Parámetros de ruta: `/pedidos/carga?codComprobante={uuid}&modo={editar|ver|copia|convertir}`.

| Modo | Fuente de datos | Cabecera | Renglones | Grabación |
|------|-----------------|----------|-----------|-----------|
| `editar` | `GET /api/v1/pedidos/{cod}` o `GET /api/v1/presupuestos/{cod}` | Valores **persistidos** del comprobante (`mapCabeceraFromPedido`) | `detalle` del comprobante | Actualiza el mismo comprobante (pedido `estado` 0/-1; presupuesto `estado` 99) |
| `ver` | Igual que editar | Solo lectura | Solo lectura | Sin grabar |
| `copia` | Igual que editar | Copia del origen; `cod_pedido` destino = null | Copia del origen | Alta nueva; `cod_comprobante_origen_copia` |
| `convertir` | Presupuesto origen | Copia del presupuesto | Copia del presupuesto | Alta pedido; `cod_presupuesto_origen` |

### Reglas de hidratación (obligatorias)

1. **No usar** `GET /api/v1/clientes/{cod}/cabecera-inicial` al abrir un comprobante existente. Ese endpoint aplica solo a **modo nuevo** (`modo=nuevo` sin `codComprobante`).
2. La respuesta de comprobante incluye **`cabecera`**, **`detalle`** (renglones) y **`catalogos`** del cliente asociado.
3. Al asignar el cliente en el `SelectBox` de forma **programática** (hidratación), **no** debe ejecutarse la lógica de cambio de cliente (`handleClienteChange`): no resetear renglones ni recargar cabecera desde maestro cliente.
4. Implementación: flag `isHydratingComprobanteRef` activo durante la carga; el `onValueChanged` del combobox cliente lo ignora hasta completar el ciclo de render.
5. **DevExtreme — cambios programáticos:** en hidratación, los controles (`SelectBox`, `NumberBox`, `TextBox`, `DateBox`) disparan `onValueChanged` sin `event.event`. Todos los handlers de cabecera, leyendas y cliente deben ignorar esos eventos (`isDevExtremeUserChange`) para no pisar valores del comprobante con defaults del catálogo o del cliente.
6. Cambio **manual** de cliente en edición: confirmación previa si hay renglones; luego sí aplica cabecera-inicial y limpia renglones (mismo comportamiento que modo nuevo).
7. Pedido en edición (`estado = 0`): tras cargar, invocar `POST /api/v1/pedidos/{cod}/iniciar-edicion` → `estado` local `-1` hasta grabar o cancelar.
8. Si falla la carga del comprobante: limpiar cabecera/renglones y mostrar `pedidos.carga.errorCargaComprobante` (no dejar datos parciales del cliente).

### Campos de cabecera esperados al editar

Además de cliente, deben mostrarse los valores grabados: vendedor, perfil (`cod_perfil`), condición de venta, transporte, dirección de entrega (`id_de`), lista de precios, moneda, bonificaciones 1–3 y leyendas 1–5.

### APIs

| Acción | Endpoint |
|--------|----------|
| Obtener pedido | `GET /api/v1/pedidos/{cod}` |
| Obtener presupuesto | `GET /api/v1/presupuestos/{cod}` |
| Iniciar edición pedido | `POST /api/v1/pedidos/{cod}/iniciar-edicion` |
| Cancelar edición | `POST /api/v1/pedidos/{cod}/cancelar-edicion` |
