# Asistente IA en carga de Pedidos / Presupuestos — definición de producto

| Campo | Valor |
|-------|--------|
| **Estado** | Borrador de definición (pre-SPEC / pre-HU) |
| **Ámbito** | PedidosWeb — pantalla de carga (`/pedidos/carga`) + canal conversacional operativo |
| **Última actualización** | 2026-07-12 (UX pie de formulario, audio, BYOK, stock exacto, K en MVP) |
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
| C | Campos libres de cabecera (nivel, observaciones, leyendas 1–5) por atributo + valor |
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

---

## 5. Presentación UX y configuración LLM

### 5.1 Ubicación del chat (decisión de producto)

**Panel de chat al pie del formulario de carga** (debajo de observaciones / totales / toolbar de grabación), en la misma página `/pedidos/carga`.

| Aspecto | Criterio |
|---------|----------|
| Por qué al pie | El operador ve cabecera + renglones arriba y conversa abajo sin perder contexto; no abre otra pestaña como el chat documental |
| Layout web | Bloque colapsable/expandible (`data-testid` estable, p. ej. `cargaAsistenteIaPanel`) con altura máxima y scroll interno del hilo, para no empujar la grilla fuera de vista en pantallas chicas |
| Toolbar del panel | Campo de texto, botón enviar, **micrófono** (audio L), **adjuntar imagen** (K), **ruedita** (Preferencias M) |
| Mobile / native | Misma idea al pie de la vista de carga; si el viewport es muy bajo, el panel puede abrirse como sheet/drawer anclado abajo, sin salir del flujo de carga |
| Modo Ver / solo lectura | El panel puede permitir consultas E–H; acciones de mutación (A–D, J) deshabilitadas o rechazadas con el mismo criterio que la UI |

No se usa el chat documental global como host de estas acciones: ese sigue siendo solo ayuda por manuales.

### 5.2 Proveedor LLM = Asistente IA

- Se reutiliza la **misma configuración BYOK** del usuario (tablas / Preferencias del Asistente IA: proveedor, modelo, API key, visión, etc.).
- No hay un segundo catálogo de proveedores ni credenciales específicas de “carga”.
- Capacidades de **visión** (K) y, si el proveedor lo permite, calidad de transcripción para **audio** (L) dependen de lo ya configurado / soportado (`supports_vision`, etc.).

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
| 1 | Usuario pulsa micrófono (Web Speech API del navegador y/o captura de audio enviada a transcripción según TR técnica) |
| 2 | Se obtiene **texto** (dictado) |
| 3 | Ese texto ingresa a la **misma pipeline** de intenciones que el chat escrito (A–K) |
| 4 | Si no hay permiso de micrófono o falla la transcripción: mensaje claro; no mutar el comprobante |

Audio **no** es un canal de negocio aparte: es entrada alternativa a texto. En MVP se prioriza dictado → texto; si el proveedor LLM ofrece speech-to-text nativo, la TR puede elegirlo sin cambiar este contrato funcional.

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
2. **0 resultados:** informar y pedir otro criterio.
3. **1 resultado:** seleccionar ese cliente.
4. **2–10 resultados:** lista numerada (código + razón social + fantasía si hay). El usuario responde con el número (o reformula).
5. **Más de 10 resultados:** **no** mostrar lista completa; avisar que refine la búsqueda (más código o nombre).
6. Al quedar el cliente determinado: ejecutar la **misma inicialización de cabecera** que al elegir en el listbox de carga (vendedor del cliente, condición, transporte, lista, bonificaciones, perfil según parámetros, dirección habitual, leyendas según `ClienteLeyendaN`, etc. — ver [pantalla-carga-comprobante-ui.md](./pantalla-carga-comprobante-ui.md)).

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

### Forma de pedido al asistente

No se exige elegir de lista. Ejemplos:

- “Nivel 100”
- “Observaciones: entregar por calle lateral”
- “Leyenda 2: Facturar a nombre de…”

El asistente confirma el valor aplicado. Si el campo está deshabilitado por modo solo lectura / Ver, rechazar.

---

## 9. Capacidad D — Selección de artículos y renglones

### Intención típica

- “Artículo ABC-01 cantidad 12”
- “Agregar tornillo hexagonal 5/16”
- “Poner precio 1500 y descuento 5 en el último renglón”

### Comportamiento

1. Búsqueda por **código** o **descripción** sobre el mismo universo que el combobox de carga (excluye BASE `usa_esc = 'B'`; requiere lista de precios válida cuando aplique precio).
2. **0 / 1 / 2–10** resultados: patrón de lista numerada; **>10** → pedir refinar (igual que A/E).
3. Al elegir artículo: pedir o tomar **cantidad** (> 0). Inicializar bonificación de renglón como en UI (bonificación del maestro / reglas de cantidad).
4. **Precio** y **descuento/bonificación de línea:** solo si parámetros/permisos lo permiten (`ModificaPrecioV/S`, `ModificaBonArtV/S`). Perfil cliente: nunca modifica precio/bonif./lista.
5. Agregar o actualizar renglón en el borrador de carga (sin duplicar código de artículo: misma regla que UI — un código por comprobante).
6. Recalcular importes con la lógica vigente (`CalculoTotales` / utilidades de carga).

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
- Importes con formato decimal coherente al portal.
- **Fechas y zona horaria:** igual que las **consultas actuales** del portal (mismo formato / TZ que deuda, cheques e historial en UI).
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
- Auditoría: usuario, timestamp, modalidad de entrada (texto/audio/imagen), intención, acción, resultado.

---

## 17. Criterios de aceptación (borrador para HU futuras)

- [ ] **CA-UX01:** El chat está al pie del formulario de carga; ruedita abre Preferencias / Asistente IA.
- [ ] **CA-UX02:** Sin LLM configurado, cualquier prompt responde el mensaje fijo de configuración (sin mutar).
- [ ] **CA-L01:** Audio se transcribe a texto y ejecuta la misma pipeline que el texto.
- [ ] **CA-A01:** Búsqueda de cliente con 2–10 matches muestra lista numerada; >10 pide refinar; 1 match inicializa cabecera como combobox.
- [ ] **CA-B01:** Cambio de lista/cabecera sin permiso es rechazado con mensaje.
- [ ] **CA-C01:** “Leyenda 3: texto X” asigna solo ese campo.
- [ ] **CA-D01:** Artículo ambiguo (≤10) → lista; >10 refine; con cantidad agrega renglón; precio solo con permiso.
- [ ] **CA-E01:** Stock usa propiedades de [consulta-stock.md](./consulta-stock.md); ≤10 filas + totales de métricas por artículo; >10 pide refinar.
- [ ] **CA-F01 / G01 / H01:** Sin cliente → pide selección; con cliente → datos de la API; fechas/TZ como consultas actuales.
- [ ] **CA-I01:** Cambio de cliente con renglones pide confirmación; sin confirmar no borra.
- [ ] **CA-J01:** “Grabar pedido” / “Grabar presupuesto” dispara el mismo flujo que los botones.
- [ ] **CA-K01:** Imagen con ítems válidos hidrata renglones en MVP; inválidos no se cargan y se informan.

---

## 18. Riesgos y decisiones abiertas

| # | Tema | Estado / notas |
|---|------|----------------|
| 1 | Ubicación del chat | **Cerrado:** pie del formulario de carga (§5.1) |
| 2 | Mapping stock | **Cerrado:** contrato [consulta-stock.md](./consulta-stock.md) §6 |
| 3 | Límite de listas | **Cerrado:** máx. 10; si hay más → refinar |
| 4 | Zona horaria / fechas F–H | **Cerrado:** igual que consultas actuales |
| 5 | Alcance K | **Cerrado:** incluido en MVP |
| 6 | Motor de audio (Web Speech vs STT del proveedor) | Abierto técnico en TR; funcionalmente dictado → texto |
| 7 | Costo BYOK en sesiones largas + imágenes | Avisos de uso / modelo económico |
| 8 | Altura del panel vs grilla en notebooks | UX: colapsable + max-height |

---

## 19. Próximos pasos documentales sugeridos

1. OpenSpec (p. ej. `SPEC-101-xx-asistente-carga-ia`) a partir de este archivo.
2. HU por capacidad (A–M en MVP, agrupables).
3. TR con contratos de “acciones” backend reutilizando services de carga/consultas + gate de credencial LLM.
4. Actualizar [pantalla-carga-comprobante-ui.md](./pantalla-carga-comprobante-ui.md) y manual de usuario cuando exista implementación.

---

## 20. Referencias

- [pantalla-carga-comprobante-ui.md](./pantalla-carga-comprobante-ui.md)
- [consulta-stock.md](./consulta-stock.md)
- [PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md](./PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md)
- Manual carga: [PedidosWeb.md](../../99-manual-usuario/PedidosWeb.md) §6
- Chat documental / Preferencias: [Chat-Asistente-IA.md](../../99-manual-usuario/Chat-Asistente-IA.md)
