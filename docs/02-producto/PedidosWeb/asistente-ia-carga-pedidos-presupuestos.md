# Asistente IA en carga de Pedidos / Presupuestos — definición de producto

| Campo | Valor |
|-------|--------|
| **Estado** | Definición de producto + OpenSpec **A1+B1+C1** + post-smoke (2026-07-13) |
| **Ámbito** | PedidosWeb — pantalla de carga (`/pedidos/carga`) + canal conversacional operativo |
| **Última actualización** | 2026-07-15 (dictado continuo, selector LLM en panel, compuesto en una línea, gate sin cliente, fallback B/V e/i) |
| **OpenSpec** | [SPEC-101-18](../../05-open-spec/101-PedidosWeb/SPEC-101-18-asistente-carga-ia-shell.md) · [SPEC-101-19](../../05-open-spec/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) · [SPEC-101-20](../../05-open-spec/101-PedidosWeb/SPEC-101-20-asistente-carga-ia-consultas.md) |
| **Cierre A1** | [F-101-18-20-cierre-a1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-a1-asistente-carga-ia.md) |
| **Cierre B1** | [F-101-18-20-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-b1-asistente-carga-ia.md) |
| **Cierre C1** | [F-101-18-20-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-c1-asistente-carga-ia.md) |
| **TR** | [TR-18](../../04-tareas/101-PedidosWeb/TR-SPEC-101-18-asistente-carga-ia-shell.md) · [TR-19](../../04-tareas/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) · [TR-20](../../04-tareas/101-PedidosWeb/TR-SPEC-101-20-asistente-carga-ia-consultas.md) |
| **HU** | [037](../../03-historias-usuario/101-PedidosWeb/HU-101-037-asistente-carga-ia-panel-gate.md) · [038](../../03-historias-usuario/101-PedidosWeb/HU-101-038-asistente-carga-ia-audio-imagen.md) · [039](../../03-historias-usuario/101-PedidosWeb/HU-101-039-asistente-carga-ia-cliente-cabecera.md) · [040](../../03-historias-usuario/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar.md) · [041](../../03-historias-usuario/101-PedidosWeb/HU-101-041-asistente-carga-ia-consulta-stock.md) · [042](../../03-historias-usuario/101-PedidosWeb/HU-101-042-asistente-carga-ia-consultas-cliente.md) |
| **Dependencias UI** | [pantalla-carga-comprobante-ui.md](./pantalla-carga-comprobante-ui.md) |
| **Consultas relacionadas** | [consulta-stock.md](./consulta-stock.md) · [consulta-deuda.md](./consulta-deuda.md) · [consulta-cheques.md](./consulta-cheques.md) · [consulta-historial-ventas.md](./consulta-historial-ventas.md) |
| **Chat documental (distinto)** | Manual [Chat-Asistente-IA.md](../../99-manual-usuario/Chat-Asistente-IA.md) — ayuda por documentación; **no** opera sobre la carga |
| **Config LLM** | Misma configuración BYOK que **Asistente IA** / Preferencias |

---

## 1. Objetivo

Incorporar un **asistente de IA operativo** en el circuito de **carga de pedidos y presupuestos**, de modo que el usuario pueda:

- armar y completar un comprobante por **lenguaje natural** (texto), **audio** y **imágenes** (MVP);
- seleccionar cliente, cabecera y renglones con la **misma lógica de negocio** que la pantalla actual (lookups, permisos, validaciones, grabación);
- consultar stock, deuda, cheques e historial de ventas del **cliente en proceso** sin abandonar el contexto de carga.

No reemplaza la UI DevExtreme de carga: es un **canal paralelo embebido al pie del formulario** que **dispara las mismas acciones** (APIs / estado de pantalla) que los controles existentes.

---

## 2. Problema que resuelve

Hoy la carga exige navegar combobox, popups y grillas. Operadores comerciales (sobre todo en mobile o con manos ocupadas) se benefician de:

- dictar o escribir “cliente Acme”, “agregar 10 unidades del artículo X”, “¿cuánto stock de tornillo?”;
- adjuntar una foto de lista o pedido manuscrito;
- resolver ambigüedades con una **lista numerada** corta;
- pedir consultas comerciales del cliente ya elegido sin cambiar de menú.

---

## 3. Alcance (MVP)

### Incluye

| ID | Capacidad |
|----|-----------|
| A | Selección de cliente por código o nombre + lista numerada si hay varios |
| B | Completar / cambiar datos de cabecera con control de permisos |
| C | Campos libres de cabecera (nivel, observaciones, leyendas 1–5, bonif. 1–3, expreso, dirección expreso) por atributo + valor |
| D | Selección de artículos (código/descripción), cantidad, precio/descuento si hay permiso |
| E | Consulta de stock por descripción — **mapping exacto** [consulta-stock.md](./consulta-stock.md); máx. **10** filas o pedir refinar |
| F | Deuda del cliente en proceso |
| G | Cheques en cartera del cliente en proceso |
| H | Historial de ventas del cliente en proceso |
| I | Confirmación al cambiar de cliente con comprobante ya iniciado |
| J | Grabar pedido / grabar presupuesto vía intención de lenguaje natural |
| K | Recepción de **imágenes** para leer, validar y cargar lo que se valide (**incluido en MVP**) |
| L | Entrada por **audio** (dictado → texto → misma pipeline de intenciones) |
| M | Configuración LLM vía Preferencias / Asistente IA (ruedita en el panel) |
| N | Selector de **configuración LLM activa** en el panel (mismas credenciales BYOK) |
| O | Pedido compuesto en **una sola línea / dictado largo** (corte por palabras clave, no solo saltos de línea) |
| P | **Gate de cliente:** sin cliente resuelto no se aplican cabecera ni renglones del mismo turno compuesto (ni imagen equivalente) |

### No incluye (MVP)

- Reemplazar el Chat Asistente documental (ayuda por manuales).
- ABM de maestros (alta de clientes/artículos en ERP).
- Autorización de excepciones comerciales fuera de parámetros/permisos vigentes.
- Grabación sin pasar por las validaciones de servidor actuales.
- Pivot / importación Excel / ABM seguridad (alineado a exclusiones mobile si el canal es nativo).
- Proveedor LLM distinto o credenciales separadas del Asistente IA (se **reutiliza** el mismo BYOK).

---

## 4. Principios de diseño

1. **Misma fuente de verdad que la pantalla:** cada acción del asistente debe equivaler a lo que haría el usuario en UI (mismo endpoint, mismos guards de permiso, mismos mensajes de error).
2. **Ambiguo → lista numerada:** si hay más de un match, no elegir en silencio; mostrar opciones `1…N` y pedir el número. **Tope 10** ítems visibles; si hay más matches, pedir **refinar la búsqueda** (no listar 11+).
3. **Un único match → auto-selección** (igual espíritu que el auto-match de `SelectBox` en carga).
4. **Permisos primero:** si el usuario no puede modificar precio, bonificación, lista, etc., el asistente informa y no aplica el cambio.
5. **Cliente en proceso:** consultas F–H usan el cliente ya seleccionado en la carga; si no hay cliente, pedir selección (A) antes.
6. **Confirmación destructiva:** cambio de cliente con datos cargados exige confirmación explícita (I).
7. **Trazabilidad:** loguear intención interpretada + acción ejecutada (útil para soporte y mejora del prompt).
8. **Canal + pantalla sincronizados:** lo que el asistente carga debe verse reflejado en la UI de carga abierta (o en el borrador de sesión mobile).
9. **Sin LLM configurado → respuesta fija** (no invocar modelo ni inventar acciones); ver §5.3.
10. **Pedido pegado / multilínea o monolínea:** si el mensaje trae varias etiquetas (cliente, cabecera, renglones), interpretar **todas** las partes aplicables y aplicarlas en orden. El corte puede ser por **saltos de línea** o por **palabras clave en una sola línea** (p. ej. dictado continuo). Ante lista numerada o confirmación, **diferir** el resto y continuar tras la respuesta (sin perder campos ya parseados).
11. **Sin cliente no se carga el resto:** en un turno compuesto (texto/dictado/imagen), si la selección de cliente falla o queda pendiente de elección/confirmación, **no** aplicar cabecera ni renglones de ese mismo mensaje hasta que el cliente quede determinado.

---

## 5. Presentación UX y configuración LLM

### 5.1 Ubicación del chat (decisión de producto)

**Panel de chat al pie del formulario de carga** (debajo de observaciones / totales / toolbar de grabación), en la misma página `/pedidos/carga`.

| Aspecto | Criterio |
|---------|----------|
| Por qué al pie | El operador ve cabecera + renglones arriba y conversa abajo sin perder contexto; no abre otra pestaña como el chat documental |
| Layout web | Bloque colapsable/expandible (`data-testid` estable, p. ej. `cargaAsistenteIaPanel`); hilo expandido con **mín. 270px** (hasta 33vh) y scroll interno |
| Toolbar del panel | Campo de texto, **Enviar**, **Dictar** / **Detener dictado** (audio L), **Adjuntar imagen** (K), **ruedita** (Preferencias M) |
| Selector LLM (N) | Debajo de los botones: `SelectBox` con las configuraciones BYOK operativas del usuario (`credentialId`); misma sesión/preferencia que el chat documental |
| Mobile / native | Misma idea al pie de la vista de carga; si el viewport es muy bajo, el panel puede abrirse como sheet/drawer anclado abajo, sin salir del flujo de carga |
| Modo Ver / solo lectura | El panel puede permitir consultas E–H; acciones de mutación (A–D, J) deshabilitadas o rechazadas con el mismo criterio que la UI |

No se usa el chat documental global como host de estas acciones: ese sigue siendo solo ayuda por manuales.

### 5.2 Proveedor LLM = Asistente IA

- Se reutiliza la **misma configuración BYOK** del usuario (tablas / Preferencias del Asistente IA: proveedor, modelo, API key, visión, etc.).
- No hay un segundo catálogo de proveedores ni credenciales específicas de “carga”.
- El panel permite **elegir cuál configuración activa** usar en el turno (`credentialId`), sin salir de la carga (capacidad N).
- Capacidades de **visión** (K) dependen de lo ya configurado / soportado (`supports_vision`, etc.).

### 5.3 Ruedita de configuración

Junto al chat (toolbar del panel), un icono de **engranaje / ruedita** (`data-testid` p. ej. `cargaAsistenteIaConfig`) que navega a la misma pantalla/ruta de **Preferencias → Asistente IA** que usa el chat documental.

### 5.4 Sin proveedor / sin configuración válida

Si el usuario **no tiene** configuración LLM habilitada (igual criterio que el Asistente IA “debe configurar”):

- Ante **cualquier** prompt (texto, audio o imagen), la respuesta es **fija**, sin llamar al LLM ni ejecutar acciones de carga.
- Mensaje (i18n; redacción ES de referencia):

> Debe configurar primero el proveedor LLM. Ir a **Asistente IA** (Preferencias).

- El panel puede mostrar además un CTA/enlace que abra Preferencias (misma ruta que la ruedita).
- Texto visible vía claves i18n (no hardcode final en JSX).

### 5.5 Audio (capacidad L)

| Paso | Comportamiento |
|------|----------------|
| 1 | Usuario pulsa **Dictar** (Web Speech API del navegador; MVP cerrado: no STT del proveedor) |
| 2 | El reconocimiento es **continuo** hasta que el usuario pulsa **Detener dictado** (no corta solo tras una frase) |
| 3 | Mientras escucha, el texto parcial se refleja en el composer; al detener se envía el texto acumulado a la **misma pipeline** de intenciones (A–K, O–P) |
| 4 | Si no hay permiso de micrófono, contexto inseguro (HTTP no-localhost) o falla el reconocimiento: mensaje claro; no mutar el comprobante |

Audio **no** es un canal de negocio aparte: es entrada alternativa a texto. Un pedido completo se puede dictar en un solo turno; el motor de intenciones debe partir por palabras clave (§10 / §18 decisión 15).

### 5.6 Imágenes (capacidad K) — en MVP

Ver §13. Entrada desde el mismo panel (adjunto), no desde otro menú.

---

## 6. Capacidad A — Selección de cliente

### Intención típica

- “Cliente 12345”
- “Buscar cliente Acme”
- “Quiero cargar a Pérez Hermanos”

### Comportamiento

1. Resolver búsqueda por **código** y/o **razón social / nombre / fantasía** (misma visibilidad de cartera que `GET /api/v1/clientes`).
2. **0 resultados:** informar y pedir otro criterio. Antes de declararlo vacío, aplicar **fallbacks tipográficos de dictado** (p. ej. B↔V y terminación e↔i: `vernasconi` → candidatos `bernascone`).
3. **1 resultado:** seleccionar ese cliente.
4. **2–10 resultados:** lista numerada (código + razón social + fantasía si hay). El usuario responde con el número (o reformula). **No** es equivalente a “no encontrado”.
5. **Más de 10 resultados:** **no** mostrar lista completa; avisar que refine la búsqueda (más código o nombre).
6. Al quedar el cliente determinado: ejecutar la **misma inicialización de cabecera** que al elegir en el listbox de carga (vendedor del cliente, condición, transporte, lista, bonificaciones, perfil según parámetros, dirección habitual, leyendas según `ClienteLeyendaN`, etc. — ver [pantalla-carga-comprobante-ui.md](./pantalla-carga-comprobante-ui.md)).
7. **Gate (P):** en turno compuesto / imagen con cliente pedido, si el cliente **no** queda determinado (0 matches o pendiente de choice/confirmación), **no** aplicar cabecera ni renglones del mismo turno. Si hay lista numerada, diferir el resto (`deferredCompositeItems` / `deferredImageExtract`) hasta la elección.

### Perfil cliente

Usuario con perfil **cliente**: el cliente es fijo de sesión; el asistente no ofrece elegir otro.

---

## 7. Capacidad B — Resto de cabecera (con listas / lookups)

Campos con catálogo o lookup (ejemplos): perfil de pedido, condición de venta, transporte, dirección de entrega, lista de precios, moneda (si aplica edición), bonificaciones de cabecera cuando son editables.

### Comportamiento

1. Interpretar atributo + valor (“lista de precios 2”, “transporte Andreani”, “condición 30 días”).
2. **Verificar permiso** del usuario/perfil/parámetros ERP (`ModificaListaPrec*`, `ModificaCondVta*`, `ModificaDirEntr*`, `ModificaExpreso*`, `ModificaBonCli*`, etc.).
3. Si no tiene permiso: mensaje claro; no mutar.
4. Si tiene permiso y hay ambigüedad: lista numerada (máx. 10; si hay más, pedir refinar).
5. Si aplica: efectos colaterales iguales a la UI (p. ej. cambio de lista → recálculo de precios de renglones).

---

## 8. Capacidad C — Campos sin lista (texto / numérico libre)

| Atributo | Notas |
|----------|--------|
| Nivel | Respetar `NivelExtremo` (solo 0/100 si aplica) |
| Observaciones | Texto libre de cabecera |
| Leyenda 1 … Leyenda 5 | Texto libre; el usuario indica **atributo + valor** |
| Bonificación 1 / 2 / 3 | Numérico; aplica directo en cabecera. Requiere `ModificaBonCli*` (perfil **C** nunca). Bonif. 3 rango −99.99…99.99. Alias de etiqueta: `Bonificación` / `Bonif` / `Descuento` / `Descto` / `Desc` / `Dto` + slot 1|2|3 |
| Expreso | Texto libre (`expreso`); permiso `ModificaExpreso*` |
| Dirección expreso | Texto libre (`expresoDire`); permiso `ModificaExpreso*`. Etiqueta suelta `Direccion:` (sin “de entrega”) → tratar como dirección de expreso |
| Transporte | Lookup catálogo `codTranspor` (código/descripción); ambigüedad → lista numerada |
| Condición de venta | Lookup catálogo `codCondvta`; permiso `ModificaCondVta*` |
| Perfil | Lookup catálogo `codPerfil` (solo lectura bloquea) |
| Lista de precios | Lookup + set moneda/IVA; permiso `ModificaListaPrec*` (perfil **C** nunca) |
| Fecha de entrega | Fecha libre (`YYYY-MM-DD` / `d/m/Y`); solo lectura bloquea |
| Dirección de entrega | Lookup direcciones del cliente (`idDe`); permiso `ModificaDirEntr*`; requiere cliente |

### Forma de pedido al asistente

No se exige elegir de lista. Ejemplos:

- “Nivel 100”
- “Observaciones: entregar por calle lateral”
- “Leyenda 2: Facturar a nombre de…”
- “Bonificación 1 5” / “Bonif 2: 3%” / “Bonificación 3 -2”
- “Expreso Andreani”
- “Dirección expreso Calle Falsa 123”
- “Transporte Pablo” (lookup catálogo; 1 match aplica; 2–10 lista numerada; >10 refinar)
- “Condición de venta 30 días”
- “Perfil STANDARD”
- “Lista de precios 2”
- “Fecha de entrega 15/07/2026”
- “Dirección de entrega Mitre”

El asistente confirma el valor aplicado. Si el campo está deshabilitado por modo solo lectura / Ver, o sin permiso de bonificación de cabecera, rechazar.

---

## 9. Capacidad D — Selección de artículos y renglones

### Intención típica

- “Artículo ABC-01 cantidad 12”
- “Agregar 10 unidades del artículo 1001”
- “Agregar tornillo hexagonal 5/16”
- “art. AJO… canti: 100” / “item arroz cant: 10” / “it \"almendra…\" cant: 120”
- “Poner precio 1500 y descuento 5 en el último renglón”
- “Bonificación 10 al artículo X” / “Descuento 5% en el último renglón”

### Comportamiento

1. Búsqueda por **código** o **descripción** sobre el mismo universo que el combobox de carga (excluye BASE `usa_esc = 'B'`; requiere lista de precios válida cuando aplique precio).
   - Por **descripción** con varias palabras: el match debe cumplir **todas** las palabras significativas (AND por tokens), no un `LIKE` único de la frase entera.
2. Resultados y mensajes (distintos; i18n en panel):
   - **0** → *No se encontró ningún artículo…* (`reply.articulosNone`)
   - **1** → agregar (o auto-aplicar) con reglas de cantidad/precio/bonif.
   - **2–10** → lista numerada (`reply.articulosAmbiguous`)
   - **>10** → *Demasiados artículos… refiná…* (`reply.articulosRefine`) — **no** el mismo texto que “no encontrado”
3. **Parseo de frase de alta** (antes de buscar):
   - Prefijo de renglón: `artículo(s)` / `art.` / `art` / `producto(s)` / `prod.` / `prod` / `item(s)` / `it.` / `it` (además de `agregar` / `cargar`).
   - Extraer **cantidad** si el usuario dice número + `unidad(es)` / `cantidad` / `canti` / `cant` / `N del artículo…`; si **no** indica cantidad → **asumir 1**.
   - Extraer **precio** si dice `precio` / `a $…` / `precio unitario`.
   - Extraer **bonificación/descuento de línea** si dice `bonificación` / `bonif` / `descuento` / `%` asociado; sinónimo comercial: descuento ≈ bonificación de renglón.
   - El **texto de búsqueda** del artículo **no** debe incluir los tokens de cantidad/unidad/precio/bonif (p. ej. de “10 unidades del artículo 1001” buscar `1001`, no `10 unidades…`).
4. Al elegir artículo: cantidad `> 0` (default 1). Inicializar bonificación de renglón como en UI (maestro / reglas de cantidad) **salvo** que el usuario haya pedido un % explícito.
5. **Precio** y **descuento/bonificación de línea:** solo si parámetros/permisos lo permiten (`ModificaPrecioV/S`, `ModificaBonArtV/S`). Perfil cliente: nunca modifica precio/bonif./lista. Si pide precio/bonif. sin permiso → informar y no aplicar ese campo (sí puede agregar con defaults de UI).
6. Intenciones de **solo precio** o **solo bonif./descuento** sobre renglón existente (“último renglón” / lista si hay varios) reutilizan D1-13.
7. **Eliminar / modificar renglón ya cargado** (detalle del comprobante, **no** el maestro de artículos):
   - Intenciones: `eliminar`/`elimina`/`borrar`/`borra`/`quitar`/`quita`/`sacar`/`saca` + artículo; o `cambiar`/`modificar`/`poner` + cantidad/precio/bonif.
   - Ámbito de búsqueda: solo `draftContext.renglones` (código, descripción o “último renglón”).
   - **Convención de frase:** descripción/código entre **comillas** (`"…"` / `'…'`) **o** al **final** del mensaje (valores cantidad/precio **antes** del artículo). Así se permiten descripciones con espacios/números sin confundirlas con el valor nuevo.
   - **0 match:** informar la **descripción/código buscado** (`renglonNoEncontradoConQ`).
   - **1 match:** aplicar `remove` o `update`.
   - **2–10 matches:** lista numerada `código — descripción · cant · precio · bonif%`; el usuario responde con el número.
   - **>10 matches** en detalle: pedir refinar (mismo tope que listas del asistente).
   - Update: cantidad/precio/bonif con los mismos permisos que el alta (`ModificaPrecio*`, `ModificaBonArt*`).
8. Agregar o actualizar renglón en el borrador (un código por comprobante en UI manual; el asistente desambigua por lista si hay varios renglones coincidentes).
9. Recalcular importes con la lógica vigente (`CalculoTotales` / utilidades de carga).
10. Mensajes del asistente hacia el usuario: **siempre texto i18n del locale activo** (no claves crudas `carga.asistente.reply.*` en el panel).

### Ejemplos eliminar / modificar

- “Eliminar artículo almendra”
- “Elimina el artículo arroz” (si hay varios renglones en el detalle, lista para elegir — **no** busca en el maestro)
- “Quitar el último renglón”
- “Cambiar cantidad a 5 del artículo ABC”
- “Cambiar cantidad del artículo \"almendra tostada\" a 150”
- “Poner precio 1500 y descuento 3 en el último renglón”

---

## 10. Capacidad E — Consulta de stock por descripción

Fuente de verdad: [consulta-stock.md](./consulta-stock.md) (`GET /api/v1/consultas/stock`, `StockConsultaService`).

### Intención típica

- “Stock de tornillo”
- “Disponible de cable UTP”

### Comportamiento

1. Filtrar con el mismo criterio que la consulta (`q` — `LIKE` sobre `cod_articulo` y `descripcion`).
2. Si el total de matches es **mayor a 10**: **no** listar el resultado completo; responder pidiendo **refinar la búsqueda** (descripción o código más preciso).
3. Si hay **1–10** filas: listar con el **mapping exacto** de la API de consulta stock:

| Etiqueta en chat (ES referencia) | Propiedad JSON | Origen / fórmula |
|----------------------------------|----------------|------------------|
| Código | `codArticulo` | `pq_pedidosweb_stock.cod_articulo` |
| Descripción | `descripcion` | `pq_pedidosweb_articulos.descripcion` |
| Stock (real) | `stock` | `pq_pedidosweb_stock.stock` |
| Comprometido | `comprometido` | `pq_pedidosweb_stock.comprometido` |
| Comprometido web | `comprometidoWeb` | Suma cantidades detalle con cabecera `estado = 0` |
| Disponible neto | `disponibleNeto` | `stock − comprometido − comprometidoWeb` |
| Código base | `codBase` | `articulos.base` o null |
| Stock base | `stockBase` | §5 consulta-stock (null si sin base) |
| Comprometido base | `comprometidoBase` | §5 |
| Comprometido base web | `comprometidoBaseWeb` | §5 |
| Disponible neto base | `disponibleNetoBase` | `stockBase − comprometidoBase − comprometidoBaseWeb` |

4. **Totales al pie** de las filas listadas: suma de `stock`, `comprometido`, `comprometidoWeb`, `disponibleNeto` (y, si se muestran columnas base en el hilo, aclarar que los totales base no se suman fila a fila de forma ingenua — en MVP del chat: totalizar solo métricas **por artículo** de las filas mostradas; métricas `*Base` se muestran por fila cuando no son null, sin totalizar base en el pie salvo que la TR lo defina explícitamente).
5. Decimales: **2** (`#,##0.00` equivalente en texto).
6. Es consulta **informativa**; no agrega renglones salvo intención posterior (D).
7. Permiso: mismo que consulta stock (`Permiso_Repo` / `pw_consultastock`); sin permiso → mensaje, sin inventar datos.

---

## 11. Capacidades F–H — Consultas del cliente en proceso

Requieren **cliente seleccionado** en el comprobante en curso. Visibilidad = universo del usuario (mismas APIs que las pantallas de consulta).

| ID | Consulta | Fuente producto | Intención ejemplo |
|----|----------|-----------------|-------------------|
| F | Deuda | [consulta-deuda.md](./consulta-deuda.md) | “¿Qué deuda tiene este cliente?” |
| G | Cheques en cartera | [consulta-cheques.md](./consulta-cheques.md) | “Cheques en cartera del cliente” |
| H | Historial de ventas | [consulta-historial-ventas.md](./consulta-historial-ventas.md) | “Últimas ventas de este cliente” |

### Presentación

- Resumen + lista acotada (máx. **10** filas visibles; si hay más, indicar total y pedir refinar o “ver en consulta …” / paginación conversacional en TR).
- Presentación tabular en el panel (tabla HTML alineada; importes a la derecha con dígitos tabulares).
- Importes con formato decimal coherente al portal.
- **Fechas:** solo la parte fecha (`YYYY-MM-DD`), **sin horario**, en deuda/cheques (y columnas `fecha` / `vencimiento` del chat).
- **Totales al pie (F/G):** si el listado tiene **más de un ítem**, al final mostrar la suma de **saldo** (deuda) o **importe** (cheques) de las filas listadas.
- Si no hay cliente: guiar a capacidad A.
- Si el usuario no tiene permiso de consulta del proceso: mensaje de permiso (no inventar datos).

---

## 12. Capacidad I — Cambio de cliente con comprobante iniciado

Si ya hay cliente y/o renglones / cabecera cargada y el usuario pide **otro cliente**:

1. Advertir que se **perderán** datos del comprobante en curso (mismo espíritu que el diálogo al cambiar cliente en UI).
2. Pedir confirmación explícita (“sí” / “confirmo” / opción numerada Sí-No).
3. Solo entonces limpiar y aplicar inicialización del nuevo cliente (A).
4. Si no confirma: mantener estado actual.

---

## 13. Capacidad J — Grabar pedido / grabar presupuesto

### Intención típica

- “Grabar pedido”
- “Guardar como presupuesto”
- “Confirmar presupuesto”

### Comportamiento

1. Equivalente a los botones **Grabar pedido** / **Grabar presupuesto** de la toolbar de carga.
2. Ejecutar las **mismas validaciones** cliente + servidor (§ requisitos de cabecera, renglones, precios, permisos).
3. Si hay errores: devolver la lista de mensajes (mismo contenido que el modal de errores de grabación).
4. Si OK: mismo flujo post-grabación (mensaje de éxito con número/GUID, carga recurrente según parámetro, mail si aplica).
5. No inventar un atajo que saltee bloqueos (`NOmodificaPedido`, estado no editable, etc.).

---

## 14. Capacidad K — Imágenes (**MVP**)

### Objetivo

Permitir adjuntar **una o más imágenes** (foto de pedido manuscrito, captura de planilla, lista de WhatsApp, etc.) desde el panel al pie, para que el asistente:

1. **Lea** el contenido (visión del proveedor LLM configurado en Preferencias — `supports_vision`);
2. **Extraiga** candidatos (cliente, renglones, cantidades, precios, leyendas…);
3. **Valide** cada dato contra maestros, permisos y reglas de carga;
4. **Cargue** solo lo validado; lo dudoso o inválido se presenta en lista numerada / errores para confirmación humana.

### Reglas

- Nada se graba en BD solo por leer la imagen: primero hidrata el **borrador de carga**; la persistencia sigue siendo J.
- Si no hay LLM configurado: mensaje fijo §5.4.
- Si el proveedor no soporta visión: informar y pedir texto o audio.
- Límites de tamaño/cantidad: reutilizar política del Asistente IA con imágenes (o TR alineada).
- Datos ilegibles o no match: no forzar; pedir aclaración.
- Tras extracto parcial, el usuario puede completar por texto/audio (A–D).

---

## 15. Relación con el Chat Asistente documental

| | Chat documental (Asistente IA) | Asistente de carga (este documento) |
|--|--------------------------------|-------------------------------------|
| Ubicación | Menú avatar → nueva pestaña | **Pie del formulario** de carga |
| Fuente | Manuales / corpus | APIs y estado de **carga** + consultas comerciales |
| Puede mutar comprobantes | No | Sí (borrador + grabar) |
| Datos de la BD del tenant | No (orientativo) | Sí (cliente, stock, deuda, etc.) |
| Config LLM | Preferencias BYOK | **La misma** (ruedita → Preferencias / Asistente IA) |
| Entradas | Texto + imágenes (ayuda) | Texto + **audio** + **imágenes** (operación) |

Pueden coexistir: el documental responde “cómo se usa”; el de carga **opera** el comprobante.

---

## 16. Seguridad y permisos

- Toda acción respeta rol, perfil (V/S/C), parámetros ERP y visibilidad de cartera.
- No elevar privilegios vía prompt injection: el backend de acciones debe revalidar permisos (nunca confiar solo en el LLM).
- Sin configuración LLM: bloquear en cliente y servidor (respuesta fija §5.4).
- Auditoría: **log de aplicación** (usuario, timestamp, modalidad texto/audio/imagen, intención, acción, resultado).

---

## 17. Criterios de aceptación (borrador para HU futuras)

- [ ] **CA-UX01:** El chat está al pie del formulario de carga; ruedita abre Preferencias / Asistente IA; selector LLM elige credencial BYOK.
- [ ] **CA-UX02:** Sin LLM configurado, cualquier prompt responde el mensaje fijo de configuración (sin mutar).
- [ ] **CA-L01:** Audio se transcribe a texto (Web Speech continuo hasta Detener) y ejecuta la misma pipeline que el texto.
- [ ] **CA-A01:** Búsqueda de cliente con 2–10 matches muestra lista numerada; >10 pide refinar; 1 match inicializa cabecera como combobox; 0 matches intenta fallback B/V e/i antes de “no encontrado”.
- [ ] **CA-A02:** Sin cliente determinado en turno compuesto/imagen, no se cargan cabecera ni renglones de ese turno.
- [ ] **CA-B01:** Cambio de lista/cabecera sin permiso es rechazado con mensaje.
- [ ] **CA-C01:** “Leyenda 3: texto X” asigna solo ese campo.
- [ ] **CA-C02:** Pedido multilínea **o monolínea con keywords** con cliente + cabecera + renglones aplica todos los campos permitidos; si hay choice intermedia, el resto no se pierde.
- [ ] **CA-D01:** Artículo ambiguo (≤10) → lista; >10 refine (mensaje distinto a “no encontrado”); con cantidad agrega renglón; “N unidades del artículo X” → qty N + búsqueda X; prefijos `art`/`item`/`it`; `canti`/`cant`; bonif/descuento de línea con permiso; precio solo con permiso; reply i18n resuelto.
- [ ] **CA-D02:** Eliminar/modificar renglón busca en el **detalle del comprobante** (conjugados `elimina`/`borra`/…); comillas o descripción al final; 0 → muestra q buscada; 2+ → lista cant·precio·bonif; no confundir con alta en maestro.
- [ ] **CA-E01:** Stock usa propiedades de [consulta-stock.md](./consulta-stock.md); ≤10 filas + totales de métricas por artículo; >10 pide refinar.
- [ ] **CA-F01 / G01 / H01:** Sin cliente → pide selección; con cliente → datos de la API; F/G fechas sin hora y total al pie si >1 ítem; presentación en tabla HTML del panel.
- [ ] **CA-I01:** Cambio de cliente con renglones pide confirmación; sin confirmar no borra.
- [ ] **CA-J01:** “Grabar pedido” / “Grabar presupuesto” dispara el mismo flujo que los botones.
- [ ] **CA-K01:** Imagen con ítems válidos hidrata renglones en MVP; inválidos no se cargan y se informan.
- [ ] **CA-UX-H:** Hilo expandido mín. 270px / `max(270px, 33vh)`; scroll interno (no empuja scroll de página).

---

## 18. Riesgos y decisiones abiertas

| # | Tema | Estado / notas |
|---|------|----------------|
| 1 | Ubicación del chat | **Cerrado:** pie del formulario de carga (§5.1) |
| 2 | Mapping stock | **Cerrado:** contrato [consulta-stock.md](./consulta-stock.md) §6 |
| 3 | Límite de listas | **Cerrado:** máx. 10; si hay más → refinar |
| 4 | Fechas F–G en chat | **Cerrado:** solo `YYYY-MM-DD` (sin horario); >1 ítem → total saldo/importe |
| 5 | Alcance K | **Cerrado:** incluido en MVP |
| 6 | Motor de audio (Web Speech vs STT del proveedor) | **Cerrado:** Web Speech API; **continuo** hasta Detener dictado (2026-07-15) |
| 7 | Costo BYOK en sesiones largas + imágenes | Avisos de uso / modelo económico |
| 8 | Altura del hilo del panel | **Cerrado (rev):** mín. **270px** (`max(270px, 33vh)`); scroll interno |
| 9 | Auditoría acciones asistente | **Cerrado:** log de aplicación Laravel |
| 10 | Columnas chat deuda / cheques / historial | **Cerrado:** F tipo/nro·fecha·vto·saldo; G nro·fecha·importe; H desc·cant·PU neto·importe |
| 11 | Frases confirmación cambio cliente | **Cerrado:** sí, confirmo, aceptado |
| 12 | Presentación tablas consulta | **Cerrado:** tabla HTML (`cargaAsistenteIaConsultaTable`) |
| 13 | Cabecera vía chat (C ampliado) | **Cerrado:** bonif 1–3, expreso, transporte, cond. venta, perfil, lista, fecha/dirección entrega + `Modifica*` |
| 14 | Eliminar/modificar renglón | **Cerrado:** detalle borrador; comillas o desc. al final; lista con cant·precio·bonif; conjugados elimina/borra… |
| 15 | Pedido multilínea / compuesto | **Cerrado (2026-07-14/15):** parse por líneas **o** por keywords en una línea; aplicar A–D en orden; diferir resto tras `needsChoice`/`confirm`; **sin cliente no aplica el resto** |
| 16 | Alias renglón y cabecera | **Cerrado (2026-07-14):** art/item/it + canti; Descto N→bonifN; Direccion:→expresoDire |
| 17 | Selector LLM en panel | **Cerrado (2026-07-15):** SelectBox de credenciales BYOK del usuario |
| 18 | Fallback dictado cliente B/V e/i | **Cerrado (2026-07-15):** si 0 matches, probar variantes tipográficas de dictado |

---

## 19. Próximos pasos documentales sugeridos

1. ~~OpenSpec A0~~ → ~~revisión **A1**~~ — [cierre A1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-a1-asistente-carga-ia.md).
2. ~~HU (B/B1)~~ — HU-101-037…042; [cierre B1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-b1-asistente-carga-ia.md).
3. ~~TR (C/C1)~~ — TR-18/19/20; [cierre C1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-c1-asistente-carga-ia.md) **Apto** → **D1**.
4. Actualizar [pantalla-carga-comprobante-ui.md](./pantalla-carga-comprobante-ui.md) (detalle UI) si hace falta sincronía fina.
5. ~~Ajustes post-smoke~~ · ~~F1~~ · ~~Parte F + OpenAPI~~ — [F-101-18-20-cierre-formal](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-formal.md) (2026-07-13; **rev. 2026-07-14** pedido compuesto / alias).

---

## 20. Referencias

- [pantalla-carga-comprobante-ui.md](./pantalla-carga-comprobante-ui.md)
- [consulta-stock.md](./consulta-stock.md)
- [PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md](./PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md)
- Manual carga: [PedidosWeb.md](../../99-manual-usuario/PedidosWeb.md) §6
- Manual asistente de carga: [PedidosWeb-asistente-carga-ia.md](../../99-manual-usuario/PedidosWeb-asistente-carga-ia.md)
- Chat documental / Preferencias: [Chat-Asistente-IA.md](../../99-manual-usuario/Chat-Asistente-IA.md)
- Patrón reusable (otros proyectos): [patron-asistente-operativo-embebido.md](../_patrones/patron-asistente-operativo-embebido.md)

---

## 21. Historial de ajustes post-smoke

Consolidado de mejoras pedidas en implementación / smoke del panel:

| Área | Ajuste | Fecha |
|------|--------|-------|
| Panel (SPEC-18) | Altura hilo **270px mín.** / `max(270px, 33vh)`; `flex-shrink: 0`; scroll interno (sin `scrollIntoView` de página) | 2026-07-13 |
| Consultas F/G (SPEC-20) | Fechas solo `YYYY-MM-DD`; totales pie si >1 ítem; tabla HTML alineada | 2026-07-13 |
| Cabecera B/C (SPEC-19) | Bonif. 1–3, expreso/dir. expreso, transporte, cond. venta, perfil, lista precios, fecha y dirección entrega; flags `Modifica*` ampliados | 2026-07-13 |
| Artículos D (SPEC-19) | Eliminar/modificar en **detalle**; comillas o descripción al final; devolver q buscada si 0; lista desambiguación con cant·precio·bonif%; conjugados `elimina`/`borra`/`quita`/`saca` (no caer en alta/maestro) | 2026-07-13 |
| i18n | `elegirRenglon`, `renglonNoEncontrado`, `renglonNoEncontradoConQ`, `renglonEliminado`, `renglonActualizado` (+ locales) | 2026-07-13 |
| Pedido compuesto | Mensaje multilínea con etiquetas → `compositePedido`; aplica cliente + cabecera + renglones en orden; `deferredCompositeItems` tras choice | 2026-07-14 |
| Alias / sinónimos | Renglón: `art`/`art.`/`item`/`it`/`prod`; cantidad `canti`/`cant`; cabecera `Descto`/`Descuento` N→bonifN; `Direccion:`→expresoDire | 2026-07-14 |
| Imagen K | Extracto JSON ampliado a perfil, cond., fecha, expreso, lista, bonif 1–3, leyendas 1–5, observaciones (+ diferido cabecera) | 2026-07-14 |
| Dictado continuo | Web Speech `continuous`; botón Detener; acumula frases; no corta tras la primera | 2026-07-15 |
| Selector LLM panel | SelectBox de configuraciones BYOK + `credentialId` en el turno | 2026-07-15 |
| Compuesto monolínea | Partir por keywords (`cliente`, `artículo`/`art`/`item`/`it`, cabecera…) además de saltos de línea | 2026-07-15 |
| Gate sin cliente | Si cliente no resuelto / pendiente choice, no aplicar cabecera ni renglones del mismo turno (compuesto e imagen) | 2026-07-15 |
| Fallback dictado cliente | Si 0 matches: variantes B↔V y e↔i final antes de “no encontrado” | 2026-07-15 |
