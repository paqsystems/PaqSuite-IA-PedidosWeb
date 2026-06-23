# PedidosWeb — Manual de usuario

| Campo | Valor |
|-------|--------|
| **Versión documento** | MVP Fase 1 — 2026-06-22 (revisión ampliada: circuito, validaciones, renglones, parámetros, chat) |
| **Ámbito** | Módulo comercial PedidosWeb |
| **Manual transversal** | [Generalidades.md](./Generalidades.md) (login, sesión, menú, grillas, idioma, chat IA) |
| **Guías complementarias** | [Circuito y estados](./PedidosWeb-circuito-estados.md) · [Validaciones y errores](./PedidosWeb-validaciones-errores.md) · [Chat Asistente IA](./Chat-Asistente-IA.md) |
| **Público** | Usuarios finales (vendedor, supervisor, cliente) y soporte funcional/técnico |

---

## 1. Introducción

Este manual describe la operatoria del módulo **PedidosWeb**: carga y consulta de **pedidos** y **presupuestos**, consultas comerciales (stock, deuda, cheques, historial), **dashboard operativo** y herramientas de apoyo.

Está pensado como **documento de consulta y referencia** para:

- vendedores, supervisores y usuarios con perfil **cliente** que operan pedidos desde el portal;
- soporte funcional y técnico que debe orientar sobre flujos, estados, permisos y validaciones;
- la generación del **asistente conversacional (chatbot)** del módulo, que tomará este manual como base documental.

Para login, navegación general, idioma, apariencia, **expiración de sesión por inactividad**, uso estándar de **grillas**, **vista pivot**, **consulta de parámetros** y **Chat Asistente IA**, consultar primero [Generalidades.md](./Generalidades.md).

Este manual se complementa con documentos de **referencia rápida** pensados para soporte y para el asistente conversacional:

| Documento | Cuándo consultarlo |
|-----------|-------------------|
| [PedidosWeb-circuito-estados.md](./PedidosWeb-circuito-estados.md) | Estados, conversiones, bloqueo -1, cierre de presupuestos |
| [PedidosWeb-validaciones-errores.md](./PedidosWeb-validaciones-errores.md) | Catálogo completo de validaciones, mensajes y causas al grabar/importar |
| [Chat-Asistente-IA.md](./Chat-Asistente-IA.md) | Configuración BYOK, límites y alcance del chat de ayuda |

---

## 2. Alcance

### Incluye

- Dashboard operativo (KPIs y mes en curso por estado).
- Carga, edición, copia y conversión de pedidos y presupuestos.
- **Importación desde Excel** de pedido individual en carga (si el tenant la tiene habilitada).
- Consultas de comprobantes (ingresados, pendientes, presupuestos).
- Consulta **Detalle de pedidos** (cabecera + renglones en una grilla).
- Consultas comerciales: deuda, cheques, historial de ventas, stock (con **vista pivot** opcional si el tenant la tiene habilitada).
- Cierre de presupuestos.
- Logs de integración (consulta).
- Notificaciones por mail al grabar (según parámetros ERP).

### No incluye

- Administración de usuarios, roles o permisos (ERP / herramientas internas).
- ABM de parámetros generales (ver [Generalidades §18](./Generalidades.md)).
- Resolución multi-empresa / selección de tenant (etapa posterior).

---

## 3. Conceptos clave

### Pedido

Comprobante comercial en curso o confirmado. Estados relevantes en consultas y dashboard:

| Estado | Descripción habitual |
|--------|----------------------|
| **En modificación** (-1) | Bloqueado mientras un usuario lo edita en el portal |
| **Ingresado** (0) | Pedido cargado, pendiente de procesamiento comercial |
| **Pendiente ERP** (1) | En cartera pendiente según reglas comerciales / ERP |
| **Cerrado ERP** (2) | Procesado o cerrado en el ERP |
| **Facturado** (3) | Facturado en el ERP |

### Presupuesto

Oferta comercial:

| Estado | Descripción habitual |
|--------|----------------------|
| **Presupuesto activo** (99) | Vigente, editable según permisos |
| **Presupuesto cerrado** (98) | Cerrado (aceptado, rechazado u otro motivo de cierre) |

### Comprobante

Término genérico que puede referirse a un **pedido** o un **presupuesto**.

### Cabecera y renglones

- **Cabecera:** cliente, vendedor, condiciones comerciales, transporte, lista de precios, bonificaciones, leyendas, observaciones, etc.
- **Renglones:** artículos con cantidad, precio, bonificación de línea, **precio neto unitario** e importes.

### Precio neto unitario

Precio de lista del renglón, menos el descuento de la línea y menos la bonificación neta de cabecera. Se muestra en la grilla de carga (solo lectura) y en consultas de detalle. No es un campo que el operador edite directamente: se recalcula al cambiar lista de precios o bonificaciones.

### Perfil funcional

Define qué ve y qué puede hacer el usuario:

- **Vendedor / supervisor:** elige cliente, accede a cartera según visibilidad.
- **Cliente:** cliente fijo de sesión; no elige otro cliente en carga.

Los permisos concretos (modificar precios, bonificaciones, etc.) dependen de parámetros ERP y del rol asignado.

**Matriz resumida de estados y conversiones:** ver [PedidosWeb-circuito-estados.md](./PedidosWeb-circuito-estados.md).

### Visibilidad de datos

Los listados y KPIs muestran solo el **universo visible** del usuario (clientes de su cartera o universo ampliado del supervisor). No es posible consultar comprobantes de clientes fuera de ese universo.

---

## 4. Menú y acceso a procesos

Tras el login, el menú lateral agrupa los procesos PedidosWeb. Los ítems visibles dependen del **perfil y permisos** del usuario.

| Grupo / proceso | Uso principal |
|-----------------|---------------|
| **Dashboard** | Indicadores operativos |
| **Pedidos → Carga** | Alta/edición de pedidos y presupuestos |
| **Pedidos → Ingresados** | Consulta pedidos estado ingresado |
| **Pedidos → Pendientes** | Consulta pedidos pendientes |
| **Pedidos → Detalle** | Consulta plana cabecera + renglones (grilla y pivot opcional) |
| **Presupuestos → Ingresados** | Presupuestos activos y cerrados |
| **Informes → Deuda** | Situación de deuda de clientes (grilla y pivot opcional) |
| **Informes → Cheques** | Cheques en cartera (grilla y pivot opcional) |
| **Informes → Historial ventas** | Ventas detalladas por período (grilla y pivot opcional) |
| **Informes → Stock** | Disponibilidad de artículos (grilla y pivot opcional) |
| **Gestión presupuestos → Tratativas** | Seguimiento de presupuestos (alcance Should) |
| **Integración → Logs** | Consulta de logs técnicos |

**General → Consulta de parámetros** se documenta en [Generalidades §18](./Generalidades.md).

---

## 5. Dashboard operativo

Pantalla de entrada habitual tras el login. Muestra indicadores del **universo visible** del usuario.

### 5.1 KPIs principales (acumulado operativo)

Tres bloques con **cantidad de comprobantes**, **importe total** y **unidades** (suma de cantidades de renglones):

| Bloque | Qué incluye |
|--------|-------------|
| **Presupuestos activos** | Comprobantes en estado 99 |
| **Pedidos ingresados** | Estados 0 y -1 (con reglas de exclusión por edición activa) |
| **Pedidos pendientes** | Estado 1 |

Además, **clientes destacados**: el de mayor importe en presupuestos activos y el de mayor importe en pedidos ingresados.

### 5.2 Mes en curso por estado

Sección separada que resume **solo el mes calendario actual**, con un indicador por cada estado operativo:

- Presupuesto activo (99) y presupuesto cerrado (98).
- Pedido ingresado (0), pendiente ERP (1), cerrado ERP (2) y facturado (3).

Cada bloque muestra cantidad, importe total y unidades de los comprobantes de ese estado grabados en el mes.

### 5.3 Uso

1. Abrir **Dashboard** en el menú.
2. Revisar KPIs y la sección **Mes en curso**.
3. Pulsar **Actualizar** para recalcular indicadores.
4. Usar los accesos rápidos (presupuestos ingresados, pedidos ingresados, pedidos pendientes) para ir a las consultas relacionadas.

La fecha y hora de última actualización aparece bajo el título del dashboard.

---

## 6. Carga de pedidos y presupuestos

Ruta: **Pedidos → Carga de pedidos y presupuestos**.

Pantalla única para **alta**, **edición**, **consulta** (solo lectura), **copia** y **conversión** entre tipos.

### 6.1 Estructura de la pantalla

| Zona | Contenido |
|------|-----------|
| **Barra Excel** (opcional) | Descargar plantilla e importar archivo — solo en altas nuevas, si el tenant lo habilita (§6.14) |
| **Toolbar** | Cancelar; Grabar presupuesto; Grabar pedido (según modo y permisos) |
| **Cabecera** | Cliente, vendedor, perfil, condiciones comerciales, transporte, lista de precios, bonificaciones, expreso, fecha entrega, observaciones |
| **Columna leyendas** | Leyendas 1–5 al pie del comprobante (§6.13) |
| **Artículos** | Búsqueda, actualizar catálogo y agregar renglones |
| **Grilla renglones** | Líneas del comprobante con importes y precio neto unitario |
| **Totales** | Subtotal, IVA, total |

El diseño organiza cabecera, leyendas y grilla en columnas para facilitar la carga sin desplazarse entre bloques alejados.

### 6.2 Selección de cliente

| Perfil | Comportamiento |
|--------|----------------|
| **Vendedor / supervisor** | Debe elegir cliente en un combobox de búsqueda |
| **Cliente** | Ve su cliente fijo; no hay selector |

En el combobox de cliente se muestra: **(código) razón social - nombre comercial**. Puede ordenar por **código**, **razón social** o **nombre comercial** mediante el selector de orden junto al campo.

Mientras se carga el listado de clientes, el combobox muestra **Cargando…** y permanece bloqueado. Si al escribir en la búsqueda queda **un solo cliente**, se selecciona automáticamente.

Al elegir cliente, el sistema inicializa la cabecera con los datos habituales del maestro: **vendedor asignado al cliente**, condición de venta, transporte, lista de precios, bonificaciones, **perfil** (según parámetro *Perfil de pedidos por defecto* — §6.4), dirección de entrega habitual, etc.

El campo **Vendedor** en cabecera es de **solo lectura**: muestra el código y nombre del vendedor del cliente en ERP, no el usuario logueado salvo que coincidan.

### 6.3 Alta de un comprobante nuevo

1. Abrir **Carga** (modo nuevo).
2. **Seleccionar cliente** (vendedor/supervisor) o ver cliente fijo (perfil cliente).
3. Revisar y completar la **cabecera** (lookups obligatorios — ver §6.12).
4. Buscar **artículos** (no se listan artículos tipo BASE del catálogo), agregar renglones y completar cantidades en el popup de edición.
5. Revisar **totales** y leyendas.
6. Grabar como **pedido** o **presupuesto** según corresponda.
7. Tras grabar, confirmar el mensaje de éxito; el sistema puede ofrecer limpiar la pantalla para un nuevo comprobante (según parámetro *carga recurrente*).

### 6.4 Perfil de pedido

Campo combobox en cabecera. Define el perfil comercial del comprobante (catálogo ERP). En un **comprobante nuevo**, tras elegir cliente, el valor inicial proviene del parámetro ERP **Perfil de pedidos por defecto** (*CodPerfilPedidos* en Consulta de parámetros). Si ese parámetro está en cero o vacío, el perfil queda sin seleccionar y el operador debe elegirlo manualmente. En **edición** se muestra el valor **grabado** del comprobante.

### 6.5 Bonificaciones e importes

- **Bonificaciones 1, 2 y 3** en cabecera (si el usuario tiene permiso de modificación).
- **Bonificación 3** admite valores entre **-99,99 y 99,99** (puede ser negativa).
- **Bonificación neta** calculada automáticamente (solo lectura).
- Cada renglón muestra **precio neto unitario** (solo lectura), precio de lista, bonificación de línea e **importe neto** con la bonificación neta de cabecera aplicada.
- Popup de renglón: importe bruto, neto, IVA y neto con IVA.

Al cambiar **lista de precios** o **bonificaciones de cabecera** con renglones ya cargados, el sistema **recalcula precios e importes** del detalle.

### 6.6 Lista de precios

Al cambiar la lista de precios en cabecera, el sistema recalcula precios de los renglones ya cargados según la nueva lista y actualiza moneda / incluye IVA cuando corresponda. El recálculo usa una consulta agrupada al servidor (más eficiente que consultar artículo por artículo).

### 6.7 Búsqueda de artículos

- Al **ingresar** a la pantalla de carga, el sistema **precarga en segundo plano** el catálogo con **stock y disponible** (hasta el límite configurado por página). Mientras dura esa carga puede verse el mensaje **Cargando…** sobre el combobox.
- Cuando la cabecera tiene una **lista de precios válida**, el sistema completa en memoria los **precios** de ese catálogo (consulta separada, más liviana). El botón **Agregar artículo** permanece deshabilitado mientras cargan esos precios.
- El combobox de artículos permanece **deshabilitado** hasta que termine la precarga de stock **y** la cabecera tenga lista de precios válida; la búsqueda dentro del listado es **local** (código o descripción).
- Icono **Actualizar** (↻) junto al combobox: vuelve a consultar **stock/disponible** al servidor si el usuario desea refrescar disponibilidades.
- Cada ítem se muestra con **código, descripción y disponible** (y disponible del artículo base entre paréntesis cuando aplica).
- Si al filtrar queda **un solo artículo**, se selecciona automáticamente.
- No aparecen artículos marcados como **BASE** en el catálogo ERP.

**Significado de los números (Disp.)**

Formato habitual en el combobox:

| Caso | Ejemplo visual |
|------|----------------|
| Sin artículo base | `ART01 - Descripción — Disp. 120,00` |
| Con artículo base | `ART01 - Descripción — Disp. 120,00 (450,00)` |

| Número | Qué representa |
|--------|----------------|
| **Primero** (tras «Disp.») | **Disponible neto del artículo**: stock ERP − comprometido ERP − cantidades en **pedidos web ingresados** (estado 0) no descargados de ese artículo. |
| **Segundo** (entre paréntesis, si existe) | **Disponible neto del artículo base**: agrega stock y comprometido ERP de **todas las presentaciones** con la misma base, y resta los pedidos web ingresados de esas presentaciones. Solo se muestra si el artículo tiene código base en el maestro ERP. |

**Importante:** no son «stock» y «comprometido» por separado; ambos valores son **disponible neto** listo para operatoria de venta. Si el artículo no tiene base, verá un solo número.

**Diferencia con Consulta de stock (§9.4):** el informe **Stock** permite análisis pivot y filtros amplios en servidor; el listbox de carga usa un catálogo precargado en memoria, optimizado para operatoria de alta.

### 6.8 Editar un comprobante existente

Desde consultas de pedidos o presupuestos, acción **Editar**:

- La pantalla carga **cabecera y renglones del comprobante** (no reinicializa desde el cliente).
- Pedidos en estado ingresado pasan a **en modificación** (-1) mientras se modifican.
- Al **Cancelar** o salir sin grabar, se libera el bloqueo de edición.

Si otro usuario mantiene el pedido en edición dentro del plazo configurado (**MinutosWeb**), puede no aparecer en los KPIs de ingresados hasta que se libere.

### 6.9 Ver, copiar y convertir

| Modo | Comportamiento |
|------|----------------|
| **Ver** | Solo lectura |
| **Copiar** | Nuevo comprobante con datos del origen |
| **Convertir** | Presupuesto → pedido (o según acción disponible) |

### 6.10 Mail al grabar

Si el parámetro ERP lo habilita, al grabar o modificar se envía notificación por correo a destinatarios configurados (cliente, vendedor, supervisor, lista adicional).

El mail incluye cabecera completa y, si **DetallePorMail** está activo, tabla de renglones con **precio neto unitario**. Los importes neto y bruto reflejan los **descuentos aplicados** (coherentes con lo grabado).

Si el envío falla, puede mostrarse un **aviso informativo** en pantalla **sin revertir** la grabación.

### 6.11 Cancelar

**Cancelar** abandona la pantalla. Si había una edición iniciada, se intenta liberar el bloqueo del pedido en el servidor.

### 6.12 Requisitos para grabar

Para **Grabar pedido** o **Grabar presupuesto**:

- Todos los datos obligatorios de cabecera completos.
- Al menos **un renglón válido** (artículo con cantidad mayor a cero).

Lookups obligatorios (combobox contra catálogos ERP):

| Campo | Obligatorio |
|-------|-------------|
| **Cliente** | Sí (vendedor/supervisor; fijo en perfil cliente) |
| **Vendedor** | Sí — se completa automáticamente desde el cliente (solo lectura) |
| **Perfil de pedido** | Sí |
| **Condición de venta** | Sí |
| **Transporte** | Sí |
| **Dirección de entrega** | Sí |
| **Lista de precios** | Sí |
| **Renglones** | Al menos uno con artículo |

Campos informativos (moneda, incluye IVA) se completan automáticamente; conviene revisarlos antes de grabar.

Validaciones adicionales del servidor:

| Regla | Comportamiento |
|-------|----------------|
| **Nivel extremo** | Si el parámetro *Nivel extremo* está activo en Consulta de parámetros, el nivel solo puede ser **0** o **100** |
| **Precio cero** | Si *Admitir artículos con precio cero* y *Admitir artículos sin precio* están inactivos, no se admiten renglones con precio cero |
| **Cliente inhabilitado** | No se puede grabar para clientes marcados como inhabilitados en el maestro ERP |

Si falta un dato obligatorio o incumple una regla, el sistema muestra un **aviso** (texto según idioma activo) e impide la grabación.

### 6.13 Leyendas al pie (1 a 5)

Las cinco leyendas son textos libres al pie del comprobante. En un **comprobante nuevo**, al elegir cliente el sistema **puede** completarlas automáticamente desde el maestro de clientes, pero **solo si se cumplen todas** estas condiciones:

| Condición | Qué verificar |
|-----------|----------------|
| Parámetro ERP activo | En **General → Consulta de parámetros**, la fila *Inicializar leyenda N desde cliente* debe estar en **Sí** (parámetros `ClienteLeyenda1` … `ClienteLeyenda5`, uno por cada leyenda). |
| Texto en el cliente | El maestro del cliente debe tener contenido en la leyenda N correspondiente. Si el cliente no tiene texto cargado, el campo queda vacío aunque el parámetro esté en Sí. |
| Momento de la carga | La copia desde cliente ocurre al **seleccionar el cliente en un alta nueva**. En **edición**, **ver** o **copia** de un comprobante existente se muestran las leyendas **grabadas en ese comprobante**, no se vuelven a leer del maestro cliente. |

Si esperaba ver leyendas del cliente y los campos están vacíos:

1. Abrir **Consulta de parámetros** y confirmar que *Inicializar leyenda N desde cliente* está en **Sí** para la leyenda que falta.
2. Verificar en el ERP que el cliente tenga texto en esa leyenda.
3. Confirmar que está en **carga nueva** (no editando un comprobante ya grabado).
4. Tras corregir parámetros o datos del cliente, **volver a elegir el cliente** (o iniciar un comprobante nuevo) para que se apliquen.

Las leyendas son **editables** en carga (salvo modo solo lectura) aunque no se hayan inicializado desde el cliente.

### 6.14 Importación desde Excel (pedido individual)

Cuando el tenant tiene habilitada la importación Excel en carga, aparece una **barra superior** con acceso a plantilla e importación.

**Cuándo está disponible**

| Condición | Efecto |
|-----------|--------|
| Modo **nuevo** sin renglones cargados | Habilitada (si el tenant la activó) |
| **Edición**, **ver**, **copia** o comprobante ya abierto | **No** disponible |
| Vendedor/supervisor con **cliente ya seleccionado** manualmente | Importación **deshabilitada** (usar importación **antes** de elegir cliente, o limpiar la pantalla) |
| Perfil **cliente** con cliente fijo | Puede importar si no hay renglones previos |

**Flujo**

1. Descargar **plantilla modelo** (columnas según idioma activo del portal).
2. Completar filas: repetir en cada renglón los datos de cabecera que deban aplicarse; columnas no editables según permisos del usuario deben ir **vacías** (el sistema las resuelve desde el maestro).
3. Importar el archivo. Si **cualquier fila** tiene error de validación, **no se procesa nada** (sin ingreso parcial).
4. Tras validar, el sistema **selecciona el cliente** de la primera fila, **inicializa la cabecera** desde el maestro (incluido el **vendedor del cliente**) y vuelca los renglones importados con los mismos cálculos que la carga manual (bonificación neta, precio neto, importes).
5. Revisar cabecera, vendedor, lookups y totales antes de **Grabar pedido** o **Grabar presupuesto**.
6. La importación **inicializa** la pantalla de carga; **no graba** el comprobante. Puede **seguir editando** cabecera, modificar renglones importados o **agregar más líneas** antes de grabar.

**Formato de la planilla (preguntas frecuentes)**

| Pregunta | Respuesta |
|----------|-----------|
| ¿Puede tener **más columnas** que la plantilla modelo? | **Sí.** Columnas adicionales con otros títulos se ignoran; no invalidan el archivo. |
| ¿Se puede **cambiar el orden** de las columnas de la plantilla? | **Sí.** En cualquier posición, incluso **intercaladas** entre columnas de la plantilla. El sistema identifica cada columna por su **título** (fila 1), no por la posición. |
| ¿Se pueden **quitar** columnas de la plantilla? | **No.** Deben permanecer **todas** las columnas definidas en la plantilla modelo descargada. Las que su perfil no puede editar deben ir **vacías** (el sistema completa desde el maestro). |
| ¿Se pueden **cambiar los nombres** de las columnas? | **No.** Los títulos deben coincidir con los de la plantilla (según idioma al descargarla o equivalente en otro idioma soportado del portal). |
| ¿Los títulos deben estar en la **primera fila**? | **Sí.** Fila 1 = encabezados; los datos de cada renglón empiezan en la **fila 2**. |
| ¿Se pueden dejar **filas en blanco**? | **No.** No deje filas vacías entre renglones; cada fila de datos debe tener al menos artículo y cantidad válidos. |
| ¿Qué pasa si la **cabecera difiere** entre filas (cliente, lista, transporte, etc.)? | **Error de inconsistencia** y se **cancela toda la importación** (sin ingreso parcial). Los datos de cabecera deben ser **idénticos en todas las filas**; solo pueden variar artículo, cantidad, precio lista y bonificación de renglón. |
| ¿Tras importar se **graba** automáticamente o se puede **seguir editando**? | El Excel **inicializa** cabecera y renglones en la pantalla de carga. **No graba** el comprobante. Puede **editar**, **cambiar** datos o **agregar más renglones** antes de pulsar Grabar pedido o Grabar presupuesto. |

**Errores de importación:** catálogo completo en [PedidosWeb-validaciones-errores.md §8](./PedidosWeb-validaciones-errores.md#8-importación-excel--errores-por-fila-y-por-lote).

### 6.15 Renglones — grilla, popup y reglas

| Tema | Comportamiento |
|------|----------------|
| **Agregar artículo** | Elegir en combobox → **Agregar artículo** → se abre popup del renglón nuevo para completar cantidad |
| **Artículo duplicado** | No se permite el mismo código dos veces; mensaje en pantalla antes de agregar |
| **Editar / quitar** | Íconos en grilla de renglones; popup muestra importes calculados |
| **Precio neto unitario** | Solo lectura en grilla; precio lista − bonif. renglón − bonif. neta cabecera |
| **Popup importes** | Bruto, neto, IVA y neto con IVA (2×2); IVA según % del artículo |
| **Bonificación renglón** | Editable según `ModificaBonArtV/S`; rango habitual 0–100 |
| **Descuento por cantidad** | Al cambiar cantidad, el sistema puede aplicar descuento del maestro `descuentocantidad` (mayor tramo ≤ cantidad ingresada), **independiente** del permiso de bonificación manual |
| **Bonificación inicial** | Al agregar, parte de la bonificación del artículo en maestro |
| **Stock** | Disponible informativo; **no bloquea** grabación |

Al cambiar **lista de precios** o **bonificaciones de cabecera**, se recalculan precios e importes de todos los renglones ya cargados.

### 6.16 Parámetros ERP que más afectan la carga

Valores en **General → Consulta de parámetros** (solo lectura). Detalle de mensajes en [validaciones y errores](./PedidosWeb-validaciones-errores.md).

| Parámetro (nombre en consulta) | Efecto en operatoria |
|--------------------------------|----------------------|
| Perfil de pedidos por defecto | Perfil inicial en alta nueva |
| Inicializar leyenda N desde cliente | Copia leyendas 1–5 del maestro al elegir cliente |
| Admitir artículos con precio cero / sin precio | Relaja o endurece validación de precios al grabar |
| Solo niveles 0 y 100 | Restringe campo nivel de cabecera |
| Modifica precio / bonif. / lista (V y S) | Habilita o bloquea cambios comerciales y grabación si se alteraron |
| Impide modificar / eliminar pedidos | Bloqueo global de edición o baja en portal |
| Minutos de inactividad web | Sesión y ventana de bloqueo -1 en edición |
| Carga recurrente post grabación | Tras grabar, limpia pantalla o vuelve al listado |
| Motivo de cierre exitoso | Usado al convertir presupuesto → pedido |
| Incluir detalle en mail | Tabla de renglones en correo de notificación |

---

## 7. Consultas de comprobantes

Comparten el patrón de **grilla** descrito en [Generalidades §16](./Generalidades.md): filtros, layouts, exportación Excel y acciones por fila.

Elementos comunes:

- **Fecha último proceso** en la carátula (formato fecha/hora según idioma, sin segundos).
- Ícono **Actualizar** en la barra de herramientas (recarga datos del servidor).
- Columna **nombre comercial** del cliente además de razón social y código.

### 7.1 Pedidos ingresados

Ruta: **Pedidos → Pedidos ingresados**.

Pedidos en estado **ingresado** y relacionados según reglas del proceso (incluye en modificación cuando aplica).

**Acciones habituales** (según permisos): ver, editar, eliminar (solo ingresados), copiar, convertir a presupuesto.

**Convertir pedido a presupuesto**

Solo pedidos en estado **ingresado (0)** que aún no fueron descargados ni pasaron a pendiente.

1. En **Pedidos → Pedidos ingresados**, localice el pedido que desea convertir.
2. Pulse **Editar** en la fila del pedido (según permisos y parámetros ERP; ver abajo).
3. En la pantalla de carga, revise cabecera y renglones si lo desea.
4. Pulse **Grabar presupuesto** en la toolbar para generar un presupuesto **activo (99)**. El pedido origen deja de estar disponible como ingresado.

También puede usar la acción **Convertir a presupuesto** directamente desde la grilla, sin pasar por edición previa.

#### Por qué no veo Editar o Eliminar

Las acciones **Editar** y **Eliminar** solo aparecen cuando **todas** las condiciones siguientes se cumplen. Si falta alguna, el ícono **no se muestra** (no suele haber un mensaje explícito):

| Acción | Condiciones habituales |
|--------|------------------------|
| **Editar** | Permiso de **modificación** en el menú; pedido en estado **ingresado (0)** o en modificación (-1); parámetro ERP *Impide modificar pedidos* en **No**; pedido no bloqueado por otro usuario en edición. |
| **Eliminar** | Permiso de **baja** en el menú; pedido en estado **ingresado (0)** únicamente; parámetro ERP *Impide eliminar pedidos* en **No**. |

Los parámetros *Impide modificar pedidos* y *Impide eliminar pedidos* (`NOmodificaPedido` / `NOeliminaPedido`) son **bloqueos globales** configurados en el ERP: si están en **Sí**, inhiben la acción para **todos** los usuarios del portal, aunque tengan permiso de menú. Un supervisor puede confirmarlo en **General → Consulta de parámetros**.

Otros motivos frecuentes sin acción Editar: otro operador tiene el pedido en edición (-1) dentro del plazo **MinutosWeb**; el pedido ya pasó a otro estado (pendiente, cerrado, etc.) — en ese caso use **Ver** o **Copiar** según corresponda.

### 7.2 Pedidos pendientes

Pedidos en cartera **pendiente** (estado 1). Consulta de seguimiento; **sin edición ni eliminación** desde la grilla.

**Acciones habituales:** ver, **copiar** (mismo patrón que pedidos ingresados y presupuestos).

### 7.3 Presupuestos ingresados

Ruta: **Pedidos → Presupuestos ingresados**.

Presupuestos **activos (99)** y **cerrados (98)** en procesos separados o pestañas según menú.

**Acciones habituales:** ver, editar (activos), copiar, convertir a pedido, **cerrar presupuesto** (con motivo de cierre).

**Convertir presupuesto a pedido**

1. En **Pedidos → Presupuestos ingresados**, localice el presupuesto **activo (99)** que desea convertir.
2. En la fila del presupuesto, use la acción **Convertir a pedido** (según permisos).
3. Se abre la pantalla de **carga** con los datos del presupuesto; revise cabecera y renglones.
4. Pulse **Grabar pedido** para generar el pedido nuevo. El presupuesto origen sigue su ciclo (puede cerrarse aparte; ver §10).

---

## 8. Consulta Detalle de pedidos

Ruta: **Pedidos → Detalle de pedidos**.

Grilla **plana**: cada fila = un renglón con datos de cabecera repetidos.

- Todos los **estados** visibles para el usuario.
- Columna **Precio neto unitario** por renglón.
- Columna **Estado** como **texto** (no código numérico).
- Solo consulta y export Excel; sin acciones de edición.
- Con pivot habilitado en el tenant: conmutador **Grilla / Pivot** para análisis por dimensiones (cliente, artículo, vendedor, etc.) — ver [Generalidades §19](./Generalidades.md).

**Cuándo usarla:** análisis de líneas, auditoría de renglones, exportación masiva cabecera + detalle o totales pivotados por artículo/cliente.

---

## 9. Consultas comerciales (Informes)

Grupo **Informes**. Procesos de **consulta** con grilla transversal, ícono **Actualizar** y —cuando el tenant lo habilita— **vista pivot** ([Generalidades §19](./Generalidades.md)).

Elementos comunes en informes con pivot:

- Vista inicial **Grilla**; conmutador **Grilla / Pivot** en la barra superior.
- Mismos criterios de visibilidad comercial que la grilla.
- Exportación Excel desde grilla o desde pivot según la vista activa.
- Valores numéricos en pivot con formato **`#,##0.00`**.

Las consultas de **pedidos ingresados**, **pendientes** y **presupuestos** no incluyen pivot: solo grilla de cabecera.

### 9.1 Deuda de clientes

Saldos y composición de deuda según visibilidad. Filtros por cliente y columnas expuestas en la grilla.

**Pivot (opcional):** agrupar por cliente, vendedor o moneda; totalizar saldos e importes.

### 9.2 Cheques en cartera

Cheques con fechas, importes y estado. Incluye cheques en cartera y aplicados según reglas comerciales.

**Pivot (opcional):** analizar importes por cliente, banco o estado.

### 9.3 Historial de ventas

Ventas detalladas en un rango temporal (parámetro **DiasVentasDetalladas** en ERP). Análisis por artículo, cliente o vendedor según columnas.

**Pivot (opcional):** totales por artículo, cliente o período según campos arrastrados al panel pivot.

### 9.4 Stock

Disponibilidad de artículos con **stock neto**: descuenta stock ERP, comprometido ERP y **pedidos web ingresados** (`estado = 0`).

En la **carga de pedidos** (§6.7), el combobox muestra **código, descripción y disponible** (con disponible base entre paréntesis cuando aplica). Use el icono **Actualizar** si necesita refrescar cantidades desde el servidor, o este informe **Stock** para análisis pivot y filtros amplios.

---

## 10. Cierre de presupuestos

Desde **Presupuestos ingresados**, acción **Cerrar** (según permisos):

1. Seleccionar un presupuesto activo.
2. Elegir **motivo de cierre** (catálogo ERP).
3. Confirmar.

El presupuesto pasa a estado **cerrado (98)** y deja de editarse como activo. No se elimina físicamente.

---

## 11. Logs de integración

Ruta: **Integración → Logs de integración**.

Consulta técnica de eventos de integración (fechas, tipos, mensajes). Solo lectura para soporte y supervisión. Filtros por rango de fechas y tipo de evento.

---

## 12. Sesión e inactividad

La sesión expira tras un período de **inactividad** configurable (**MinutosWeb** en parámetros ERP — ver **General → Consulta de parámetros**). Cada acción del usuario (navegación, interacción con pantallas, operaciones exitosas) **renueva** el contador.

Si la sesión expira, el sistema redirige al login con mensaje informativo. Detalle en [Generalidades §11](./Generalidades.md) (comportamientos de sesión).

---

## 13. Permisos y visibilidad

### Visibilidad de datos

| Perfil | Universo habitual |
|--------|-------------------|
| **Vendedor** | Clientes y comprobantes de su cartera |
| **Supervisor** | Universo ampliado según configuración |
| **Cliente** | Solo su propio código de cliente |

### Permisos de acción (ejemplos)

| Acción | Depende de |
|--------|------------|
| Consultar listados | Permiso de consulta por procedimiento |
| Alta / grabación | Permiso de alta |
| Edición | Permiso de modificación + estado del comprobante |
| Eliminación pedido | Permiso de baja + estado ingresado |
| Modificar precio / bonif. en carga | Parámetros `ModificaPrecio*`, `ModificaBonArt*`, `ModificaBonCli*`, `ModificaListaPrec*` |

### Bloqueos globales de pedidos (parámetros ERP)

Además del permiso de menú, existen dos interruptores generales que afectan a **todo el portal**:

| Parámetro (Consulta de parámetros) | Si está en **Sí** | Efecto visible |
|-----------------------------------|-------------------|----------------|
| **Impide modificar pedidos** | Activo | No aparece **Editar** en pedidos ingresados ni en presupuestos activos; tampoco se puede abrir edición aunque el rol tenga permiso de modificación. |
| **Impide eliminar pedidos** | Activo | No aparece **Eliminar** en pedidos ingresados, aunque el rol tenga permiso de baja. |

Estos flags suelen activarse en ventanas de cierre comercial o sincronización con el ERP. No los modifica el usuario desde el portal; debe consultarlos en **General → Consulta de parámetros** o solicitar cambio al administrador ERP.

Si una acción no aparece en la grilla, el usuario **no tiene permiso**, el **estado del comprobante** no lo permite, o un **parámetro global** lo inhibe (tabla anterior).

---

## 14. Validaciones habituales en carga

Resumen operativo; **catálogo exhaustivo** en [PedidosWeb-validaciones-errores.md](./PedidosWeb-validaciones-errores.md).

### Antes de grabar (cliente)

- Cumplir requisitos de cabecera y renglones (§6.12).
- Verificar que el **vendedor** de cabecera corresponda al cliente (especialmente tras importar Excel — §6.14).
- Definir **lista de precios** antes de agregar artículos (combobox de artículos deshabilitado sin lista válida).

### Renglones y artículos

- No duplicar el mismo **código de artículo** en un comprobante.
- Cantidad **mayor a cero** en cada línea activa.
- Artículos **BASE** no se ofrecen en la búsqueda de carga ni en Excel.
- Con parámetros de precio cero inactivos, no se admiten renglones sin precio o con precio cero.

### Cabecera y permisos

- Al **cambiar cliente** con renglones cargados, el sistema pide confirmación (se pierden las líneas).
- Bonificaciones, precios y lista pueden estar **deshabilitados** según perfil (cliente nunca modifica precio/bonif./lista).
- Con *Nivel extremo* activo, el nivel solo admite **0** o **100**.
- Grabar con precio o bonificación **modificados sin permiso** falla en servidor aunque la pantalla lo permitiera temporalmente.

### Edición concurrente

- Pedido en **-1** bloqueado por otro usuario dentro de **MinutosWeb** → error *edición en curso* (ver [circuito §5](./PedidosWeb-circuito-estados.md#5-bloqueo-de-edición-estado--1-y-minutosweb)).

---

## 15. Mensajes de error y advertencia

Los textos exactos dependen del **idioma activo**. Al grabar, puede aparecer un **diálogo con lista** de errores. Tabla ampliada en [PedidosWeb-validaciones-errores.md](./PedidosWeb-validaciones-errores.md).

| Situación | Acción sugerida |
|-----------|-----------------|
| No permite grabar (lista de errores) | Corregir cada ítem del diálogo; revisar §6.12 y [validaciones §11](./PedidosWeb-validaciones-errores.md#11-tabla-rápida-no-puedo-grabar--revisar-en-orden) |
| Debe seleccionar cliente / perfil / transporte / etc. | Completar lookups obligatorios de cabecera |
| Hay artículos con precio cero o sin precio | Cambiar artículo o lista; revisar parámetros *Admitir artículos con precio cero* y *sin precio* |
| No tiene permiso para modificar precios/bonificaciones/lista | Revertir cambios comerciales o solicitar habilitación ERP |
| Importación Excel rechazada | Corregir **todas** las filas; sin ingreso parcial — §6.14 y [validaciones §8](./PedidosWeb-validaciones-errores.md#8-importación-excel--errores-por-fila-y-por-lote) |
| Vendedor distinto al esperado tras Excel | Es el vendedor del **cliente** en ERP — §6.14 |
| Edición en curso | Otro usuario edita el pedido; esperar o contactarlo — [circuito §5](./PedidosWeb-circuito-estados.md#5-bloqueo-de-edición-estado--1-y-minutosweb) |
| Estado no editable | Comprobante en estado que no admite edición (pendiente, cerrado, presupuesto 98) |
| Grilla vacía en consulta | Revisar filtros; pulsar **Actualizar**; ampliar criterios; verificar cartera |
| No puedo editar un pedido | §7.1, §13 y [validaciones §12](./PedidosWeb-validaciones-errores.md#12-tabla-rápida-no-puedo-editar--eliminar--revisar-en-orden) |
| No puedo eliminar un pedido | Solo estado **0**; parámetro *Impide eliminar*; permiso de baja |
| Leyendas vacías pese a tenerlas en el cliente | §6.13: parámetro *Inicializar leyenda N*, texto en maestro y carga nueva |
| Mail no enviado tras grabar | Fallo de correo; **la grabación sí se realizó** — parámetros mail en ERP |
| Totales distintos al esperado | Bonificación neta de cabecera, bonif. de renglón y % IVA |
| Dashboard sin datos | Visibilidad de cartera; pedidos en -1 pueden excluirse de ingresados |
| Cliente no existe o no disponible | Fuera de cartera o inhabilitado |
| Motivo de cierre inválido al convertir presupuesto | Revisar *Motivo de cierre exitoso* y catálogo ERP |

Para acceso, sesión, permisos generales o chat IA: [Generalidades.md](./Generalidades.md) y [Chat-Asistente-IA.md](./Chat-Asistente-IA.md).

---

## 16. Problemas frecuentes

- Confundir **presupuesto** con **pedido** al grabar (usar el botón correcto en la toolbar).
- Usar **Copiar** cuando la intención es **Convertir** tipo de comprobante — ver [circuito §4](./PedidosWeb-circuito-estados.md#4-matriz-de-grabación-pedido-vs-presupuesto).
- Editar cabecera esperando cambiar **cliente** sin perder renglones (el sistema advierte antes).
- Grabar con botones deshabilitados porque la **cabecera aún carga** tras elegir cliente (esperar fin de carga).
- Buscar un artículo con stock **cero** en carga: el listbox muestra disponible informativo; **no impide** grabar.
- No ver el conmutador **Grilla / Pivot** en un informe (pivot puede no estar habilitado en el tenant; §9).
- Fecha de comprobante distinta a la esperada en consultas (verificar zona/fecha de grabación con soporte si persiste).
- No ver **Detalle de pedidos** en menú (requiere permiso; contactar administrador).
- Tener permiso de menú pero **no ver Editar/Eliminar**: revisar *Impide modificar/eliminar pedidos* en Consulta de parámetros (§13).
- Intentar importar Excel **después** de haber elegido cliente manualmente (vendedor/supervisor): el botón queda deshabilitado — importar al inicio o en pantalla limpia (§6.14).
- Esperar leyendas del cliente en **edición** de un comprobante ya grabado (solo se inicializan desde cliente en **alta nueva**; §6.13).
- Creer que el pedido **desapareció** tras grabar presupuesto (conversión **elimina** el pedido ingresado origen).
- Esperar que el presupuesto **se borre** al convertir a pedido (pasa a **cerrado 98**, no se elimina).
- Dos usuarios editando el mismo pedido: el segundo recibe **edición en curso** hasta liberar bloqueo -1.
- Chat asistente con respuestas pobres: formular preguntas con pantalla y mensaje de error; consultar [Chat-Asistente-IA.md](./Chat-Asistente-IA.md) y manuales complementarios.

---

## 17. Recomendaciones de uso

- Completar y **verificar lookups obligatorios** antes de grabar (§6.12).
- Definir **lista de precios** en cabecera **antes** de agregar renglones (el combobox de artículos permanece deshabilitado sin lista válida; §6.7).
- Usar **layouts** de grilla en consultas frecuentes ([Generalidades §16](./Generalidades.md)); diseños propios se identifican con **` (*)`**.
- Guardar **diseños pivot** recurrentes en informes analíticos ([Generalidades §19](./Generalidades.md)).
- Tras grabar, verificar el **número visible** en el mensaje de confirmación.
- En edición, usar **Cancelar** para liberar bloqueo si no se grabará.
- Revisar **Consulta de parámetros** (General) para flags de mail, minutos de edición y permisos de modificación.
- Consultar el **dashboard** al inicio del día y usar **Mes en curso** para el panorama del mes.

---

## 18. Preguntas frecuentes

### ¿Qué necesito para grabar un pedido o presupuesto?

Cliente seleccionado, lookups obligatorios de cabecera (§6.12) y al menos un renglón. Ver §6.3 y §6.12.

### ¿Puedo tener dos comprobantes abiertos a la vez en carga?

No. Un comprobante a la vez. Use consultas para alternar.

### ¿El cliente puede cargar pedidos?

Sí, con perfil **cliente** y permisos de alta; ve su cliente fijo.

### ¿Qué es el precio neto unitario?

Precio de lista menos descuentos de renglón y cabecera. Solo lectura en grilla; ver §3.

### ¿Puedo exportar el detalle de pedidos?

Sí, desde **Detalle de pedidos** con Exportar Excel (si hay datos visibles).

### ¿Por qué no veo bonificaciones editables?

Parámetros ERP o rol pueden inhibir modificación de bonificaciones.

### ¿Cómo paso un presupuesto a pedido?

Para convertir un presupuesto activo en pedido:

1. Acceda a **Pedidos → Presupuestos ingresados**.
2. Busque el presupuesto que desea convertir (debe estar **activo**, estado 99).
3. Utilice la acción **Convertir a pedido** en la fila del presupuesto.
4. En la pantalla de carga que se abre, revise los datos y pulse **Grabar pedido**.

No use la acción **Copiar** para este fin: copia crea un comprobante nuevo del mismo tipo; la conversión es la acción **Convertir a pedido**. Detalle en §7.3.

### ¿Puedo pasar un pedido a presupuesto?

Sí, si el pedido está en estado **ingresado (0)** y no fue descargado:

1. Acceda a **Pedidos → Pedidos ingresados**.
2. **Edite** el pedido que desea convertir.
3. En la pantalla de carga, pulse **Grabar presupuesto**.

También puede usar **Convertir a presupuesto** desde la grilla sin editar antes. Detalle en §7.1.

### ¿La conversión presupuesto → pedido borra el presupuesto?

Genera un **pedido nuevo**; el presupuesto origen sigue su ciclo (puede cerrarse aparte).

### ¿Qué muestra el dashboard «Mes en curso»?

Cantidad, importe y unidades por **estado** (99, 98, 0, 1, 2, 3) solo para comprobantes del **mes actual**.

### ¿Por qué no puedo editar o eliminar pedidos ingresados?

Puede deberse a: (1) parámetros ERP *Impide modificar pedidos* o *Impide eliminar pedidos* en **Sí** — bloqueo global para todo el portal; (2) falta permiso de modificación o baja en su rol; (3) el pedido no está en estado ingresado (0); (4) otro usuario lo tiene en edición. Detalle en §7.1 y §13. Consulte **General → Consulta de parámetros** para ver el valor de esos flags.

### ¿Por qué no aparecen las leyendas que tiene cargadas el cliente?

En **carga nueva**, cada leyenda solo se copia del maestro cliente si el parámetro *Inicializar leyenda N desde cliente* está en **Sí** y el cliente tiene texto en esa leyenda. Si el parámetro está en **No** (común en instalaciones recientes), los campos arrancan vacíos aunque el cliente tenga leyendas en el ERP. En **edición** se muestran las leyendas del comprobante grabado, no las del cliente. Ver §6.13.

### ¿Cómo importo un pedido desde Excel?

En **Carga**, modo **nuevo**, usar la barra superior de importación (si el tenant la habilitó). Descargar plantilla, completar filas e importar **antes** de elegir cliente manualmente si es vendedor/supervisor. Ver §6.14 (incluye formato de planilla: columnas, filas, cabecera y edición posterior).

### ¿Puedo quitar columnas de la plantilla Excel?

**No.** Deben permanecer todas las columnas del modelo descargado. Las que su perfil no puede editar van **vacías**. Ver §6.14.

### ¿Puedo dejar filas en blanco en el Excel?

**No.** No deje filas vacías entre renglones. Ver §6.14.

### ¿El Excel graba el pedido automáticamente?

**No.** Inicializa cabecera y renglones en carga; puede editarlos y agregar líneas antes de **Grabar pedido** o **Grabar presupuesto**. Ver §6.14.

### ¿Por qué el combobox de artículos está deshabilitado o muestra disponibilidad desactualizada?

El combobox se habilita cuando la cabecera tiene **lista de precios válida**. El catálogo se precarga al **ingresar** a la pantalla; use el icono **Actualizar artículos** para refrescar desde el servidor. Para análisis ampliado use **Informes → Stock** (§9.4). Ver §6.7.

### ¿Qué significan los dos números en la lista de artículos en carga?

En el combobox de **Carga de pedidos**, junto al código y la descripción aparece **Disp.** con uno o dos números:

1. **Primer número:** disponible **neto** del artículo (stock ERP − comprometido ERP − pedidos web ingresados no descargados de ese artículo).
2. **Segundo número (entre paréntesis):** disponible neto del **artículo base**, solo si el artículo tiene base en el maestro. Agrega todas las presentaciones con esa base y aplica la misma regla de descuentos.

No representan stock y comprometido por separado. Si no hay artículo base, solo verá el primer número. Detalle en §6.7.

### ¿Cómo uso la vista pivot en informes?

Abrir un informe habilitado (deuda, cheques, stock, detalle de pedidos o historial), pulsar **Pivot** en el conmutador superior y arrastrar campos desde el panel lateral. Requiere que el tenant tenga pivots activos. Detalle en [Generalidades §19](./Generalidades.md).

### ¿El stock en carga impide grabar si es cero?

No. El disponible mostrado es **informativo**. Puede grabar igual salvo otras validaciones (precio, permisos, cabecera).

### ¿Qué pasa si grabo presupuesto desde un pedido ingresado?

Se crea un presupuesto **activo (99)** nuevo y el pedido ingresado origen **deja de existir** en el portal. Ver [circuito §4](./PedidosWeb-circuito-estados.md#4-matriz-de-grabación-pedido-vs-presupuesto).

### ¿Qué pasa si grabo pedido desde un presupuesto activo?

Se crea pedido **ingresado (0)** y el presupuesto pasa a **cerrado (98)** con cierre por conversión. Ver [circuito §6](./PedidosWeb-circuito-estados.md#6-cierre-de-presupuestos).

### ¿Por qué otro usuario no puede editar mi pedido?

Está en estado **-1** por su sesión de edición. Debe **grabar**, **cancelar** o esperar **MinutosWeb** sin actividad.

### ¿Puedo eliminar un presupuesto?

No desde el portal. Los presupuestos se **cierran** (estado 98), no se eliminan como los pedidos ingresados.

### ¿Dónde está el listado completo de mensajes al grabar?

En [PedidosWeb-validaciones-errores.md](./PedidosWeb-validaciones-errores.md).

### ¿El chat asistente puede ver mi comprobante abierto?

No. Orienta según documentación. Ver [Chat-Asistente-IA.md](./Chat-Asistente-IA.md).

---

## 19. Resumen operativo

PedidosWeb concentra la operatoria comercial web en cuatro ejes:

1. **Dashboard** — KPIs operativos y mes en curso por estado.
2. **Carga** — pedidos y presupuestos con cabecera completa y renglones.
3. **Consultas** — comprobantes, detalle plano e informes comerciales (grilla y pivot opcional).
4. **Soporte** — logs de integración y consulta de parámetros (General).

Grillas, pivot, idioma y acceso se rigen por [Generalidades.md](./Generalidades.md). Permisos y parámetros ERP determinan qué ve y qué puede modificar cada usuario.

---

## Referencias técnicas (soporte)

| Tema | Documento |
|------|-----------|
| Circuito y estados | [PedidosWeb-circuito-estados.md](./PedidosWeb-circuito-estados.md) |
| Validaciones y errores | [PedidosWeb-validaciones-errores.md](./PedidosWeb-validaciones-errores.md) |
| Chat Asistente IA | [Chat-Asistente-IA.md](./Chat-Asistente-IA.md) |
| Carga UI | [pantalla-carga-comprobante-ui.md](../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) |
| Consultas cabecera | [consulta-comprobantes-cabecera.md](../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md) |
| Detalle pedidos | [consulta-detalle-pedidos.md](../02-producto/PedidosWeb/consulta-detalle-pedidos.md) |
| Dashboard | [patron-dashboard-operativo-ui.md](../02-producto/PedidosWeb/patron-dashboard-operativo-ui.md) |
| Importación Excel | [Importación Pedido Individual desde Excel.md](../02-producto/PedidosWeb/Importación%20Pedido%20Individual%20desde%20Excel.md) |
| Historias de usuario | [101-PedidosWeb/README.md](../03-historias-usuario/101-PedidosWeb/README.md) |
