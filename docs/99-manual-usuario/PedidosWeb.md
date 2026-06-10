# PedidosWeb — Manual de usuario

| Campo | Valor |
|-------|--------|
| **Versión documento** | MVP Fase 1 — 2026-06-09 |
| **Ámbito** | Módulo comercial PedidosWeb |
| **Manual transversal** | [Generalidades.md](./Generalidades.md) (login, sesión, menú, grillas, idioma) |
| **Público** | Usuarios finales (vendedor, supervisor, cliente) y soporte funcional/técnico |

---

## 1. Introducción

Este manual describe la operatoria del módulo **PedidosWeb**: carga y consulta de **pedidos** y **presupuestos**, consultas comerciales (stock, deuda, cheques, historial), **dashboard operativo** y herramientas de apoyo.

Está pensado como **documento de consulta y referencia** para:

- vendedores, supervisores y usuarios con perfil **cliente** que operan pedidos desde el portal;
- soporte funcional y técnico que debe orientar sobre flujos, estados, permisos y validaciones;
- la generación del **asistente conversacional (chatbot)** del módulo, que tomará este manual como base documental.

Para login, navegación general, idioma, apariencia, **expiración de sesión por inactividad** y uso estándar de **grillas**, consultar primero [Generalidades.md](./Generalidades.md).

---

## 2. Alcance

### Incluye

- Dashboard operativo (KPIs y mes en curso por estado).
- Carga, edición, copia y conversión de pedidos y presupuestos.
- Consultas de comprobantes (ingresados, pendientes, presupuestos).
- Consulta **Detalle de pedidos** (cabecera + renglones en una grilla).
- Consultas comerciales: deuda, cheques, historial de ventas, stock.
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
| **Pedidos → Detalle** | Consulta plana cabecera + renglones (todos los estados) |
| **Presupuestos → Ingresados** | Presupuestos activos y cerrados |
| **Informes → Deuda** | Situación de deuda de clientes |
| **Informes → Cheques** | Cheques en cartera |
| **Informes → Historial ventas** | Ventas detalladas por período |
| **Informes → Stock** | Disponibilidad de artículos |
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
| **Toolbar** | Cancelar; Grabar presupuesto; Grabar pedido (según modo y permisos) |
| **Cabecera** | Cliente, datos comerciales, bonificaciones, expreso, fecha entrega, observaciones |
| **Artículos** | Búsqueda y agregar renglones |
| **Grilla renglones** | Líneas del comprobante con importes y precio neto unitario |
| **Totales** | Subtotal, IVA, total |
| **Leyendas 1–5** | Textos al pie del comprobante (ver §6.13) |

### 6.2 Selección de cliente

| Perfil | Comportamiento |
|--------|----------------|
| **Vendedor / supervisor** | Debe elegir cliente en un combobox de búsqueda |
| **Cliente** | Ve su cliente fijo; no hay selector |

En el combobox de cliente se muestra: **(código) razón social - nombre comercial**. Puede ordenar por **código**, **razón social** o **nombre comercial** mediante el selector de orden junto al campo.

Al elegir cliente, el sistema inicializa la cabecera con los datos habituales del maestro (condición de venta, transporte, lista de precios, bonificaciones, perfil, etc.).

### 6.3 Alta de un comprobante nuevo

1. Abrir **Carga** (modo nuevo).
2. **Seleccionar cliente** (vendedor/supervisor) o ver cliente fijo (perfil cliente).
3. Revisar y completar la **cabecera** (lookups obligatorios — ver §6.11).
4. Buscar **artículos** (no se listan artículos tipo BASE del catálogo), agregar renglones y completar cantidades en el popup de edición.
5. Revisar **totales** y leyendas.
6. Grabar como **pedido** o **presupuesto** según corresponda.
7. Tras grabar, confirmar el mensaje de éxito; el sistema puede ofrecer limpiar la pantalla para un nuevo comprobante (según parámetro *carga recurrente*).

### 6.4 Perfil de pedido

Campo combobox en cabecera. Define el perfil comercial del comprobante (catálogo ERP). Valor inicial según parámetro **CodPerfilPedidos** en altas; en edición se muestra el valor **grabado** del comprobante.

### 6.5 Bonificaciones e importes

- **Bonificaciones 1, 2 y 3** en cabecera (si el usuario tiene permiso de modificación).
- **Bonificación 3** admite valores entre **-99,99 y 99,99** (puede ser negativa).
- **Bonificación neta** calculada automáticamente (solo lectura).
- Cada renglón muestra **precio neto unitario** (solo lectura), precio de lista, bonificación de línea e **importe neto** con la bonificación neta de cabecera aplicada.
- Popup de renglón: importe bruto, neto, IVA y neto con IVA.

Al cambiar **lista de precios** o **bonificaciones de cabecera** con renglones ya cargados, el sistema **recalcula precios e importes** del detalle.

### 6.6 Lista de precios

Al cambiar la lista de precios en cabecera, el sistema recalcula precios de los renglones ya cargados según la nueva lista y actualiza moneda / incluye IVA cuando corresponda.

### 6.7 Búsqueda de artículos

- Búsqueda por código o descripción contra el servidor.
- Cada ítem muestra la **disponibilidad neta** (y disponibilidad base cuando aplica).
- No aparecen artículos marcados como **BASE** en el catálogo ERP (`usa_esc = 'B'`).

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
| **Perfil de pedido** | Sí |
| **Condición de venta** | Sí |
| **Transporte** | Sí |
| **Dirección de entrega** | Sí |
| **Lista de precios** | Sí |
| **Renglones** | Al menos uno con artículo |

Campos informativos (vendedor, moneda, incluye IVA) se completan automáticamente; conviene revisarlos antes de grabar.

Si falta un dato obligatorio, el sistema muestra un **aviso** (texto según idioma activo) e impide la grabación.

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

---

## 7. Consultas de comprobantes

Comparten el patrón de **grilla** descrito en [Generalidades §16](./Generalidades.md): filtros, layouts, exportación Excel y acciones por fila.

Elementos comunes:

- **Fecha último proceso** en la carátula (formato fecha/hora según idioma, sin segundos).
- Ícono **Actualizar** en la barra de herramientas (recarga datos del servidor).
- Columna **nombre comercial** del cliente además de razón social y código.

### 7.1 Pedidos ingresados

Pedidos en estado **ingresado** y relacionados según reglas del proceso (incluye en modificación cuando aplica).

**Acciones habituales** (según permisos): ver, editar, eliminar (solo ingresados), copiar, convertir a presupuesto.

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

Presupuestos **activos (99)** y **cerrados (98)** en procesos separados o pestañas según menú.

**Acciones habituales:** ver, editar (activos), copiar, convertir a pedido, **cerrar presupuesto** (con motivo de cierre).

---

## 8. Consulta Detalle de pedidos

Ruta: **Pedidos → Detalle de pedidos**.

Grilla **plana**: cada fila = un renglón con datos de cabecera repetidos.

- Todos los **estados** visibles para el usuario.
- Columna **Precio neto unitario** por renglón.
- Columna **Estado** como **texto** (no código numérico).
- Solo consulta y export Excel; sin acciones de edición.

**Cuándo usarla:** análisis de líneas, auditoría de renglones o exportación masiva cabecera + detalle.

---

## 9. Consultas comerciales (Informes)

Grupo **Informes**. Procesos de **consulta** con grilla transversal e ícono **Actualizar** cuando aplique.

### 9.1 Deuda de clientes

Saldos y composición de deuda según visibilidad. Filtros por cliente y columnas expuestas en la grilla.

### 9.2 Cheques en cartera

Cheques con fechas, importes y estado. Incluye cheques en cartera y aplicados según reglas comerciales.

### 9.3 Historial de ventas

Ventas detalladas en un rango temporal (parámetro **DiasVentasDetalladas** en ERP). Análisis por artículo, cliente o vendedor según columnas.

### 9.4 Stock

Disponibilidad de artículos (stock neto comprometido). La misma lógica se refleja al buscar artículos en **carga**.

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

La sesión expira tras un período de **inactividad** configurable (**MinutosWeb** en parámetros ERP). Cada acción del usuario (navegación, interacción con la pantalla, operaciones exitosas) **renueva** el contador.

Si la sesión expira, el sistema redirige al login con mensaje informativo. Detalle en [Generalidades](./Generalidades.md) (sesión e inactividad).

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

- Cumplir requisitos de cabecera y renglones (§6.12).
- No duplicar el mismo **código de artículo** en un comprobante.
- Al **cambiar cliente** con renglones cargados, el sistema pide confirmación (se pierden las líneas).
- Bonificaciones y precios pueden estar **deshabilitados** según permisos ERP.
- Artículos **BASE** no se ofrecen en la búsqueda de carga.

---

## 15. Mensajes de error y advertencia

Los textos exactos dependen del **idioma activo**. Interpretación funcional habitual:

| Situación | Acción sugerida |
|-----------|-----------------|
| No permite grabar | Completar lookups obligatorios (§6.12); agregar renglones; verificar permisos |
| Grilla vacía en consulta | Revisar filtros; pulsar **Actualizar**; ampliar criterios |
| No puedo editar un pedido | Revisar §7.1 y §13: parámetro *Impide modificar pedidos*, permiso de menú, estado del pedido o bloqueo en edición (-1) |
| No puedo eliminar un pedido | Revisar §7.1 y §13: parámetro *Impide eliminar pedidos*, permiso de baja o estado distinto de ingresado (0) |
| Leyendas vacías pese a tenerlas en el cliente | Revisar §6.13: parámetro *Inicializar leyenda N*, texto en maestro cliente y que sea carga nueva |
| Mail no enviado tras grabar | Fallo de correo; la grabación sí se realizó — revisar parámetros mail en ERP |
| Totales distintos al esperado | Verificar bonificación neta de cabecera y % IVA en renglones |
| Dashboard sin datos | Verificar visibilidad de cartera y mes en curso |

Para acceso, sesión o permisos generales: [Generalidades §10](./Generalidades.md).

---

## 16. Problemas frecuentes

- Confundir **presupuesto** con **pedido** al grabar (usar el botón correcto en la toolbar).
- Editar cabecera esperando cambiar **cliente** sin perder renglones (el sistema advierte antes).
- Buscar un artículo con stock **cero** y asumir error del sistema (puede ser disponibilidad real).
- Fecha de comprobante distinta a la esperada en consultas (verificar zona/fecha de grabación con soporte si persiste).
- No ver **Detalle de pedidos** en menú (requiere permiso; contactar administrador).
- Tener permiso de menú pero **no ver Editar/Eliminar**: revisar *Impide modificar/eliminar pedidos* en Consulta de parámetros (§13).
- Esperar leyendas del cliente en **edición** de un comprobante ya grabado (solo se inicializan desde cliente en **alta nueva**; §6.13).

---

## 17. Recomendaciones de uso

- Completar y **verificar lookups obligatorios** antes de grabar (§6.12).
- Definir **lista de precios** en cabecera antes de cargar muchos renglones.
- Usar **layouts** de grilla en consultas frecuentes ([Generalidades §16](./Generalidades.md)).
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

### ¿La conversión presupuesto → pedido borra el presupuesto?

Genera un **pedido nuevo**; el presupuesto origen sigue su ciclo (puede cerrarse aparte).

### ¿Qué muestra el dashboard «Mes en curso»?

Cantidad, importe y unidades por **estado** (99, 98, 0, 1, 2, 3) solo para comprobantes del **mes actual**.

### ¿Por qué no puedo editar o eliminar pedidos ingresados?

Puede deberse a: (1) parámetros ERP *Impide modificar pedidos* o *Impide eliminar pedidos* en **Sí** — bloqueo global para todo el portal; (2) falta permiso de modificación o baja en su rol; (3) el pedido no está en estado ingresado (0); (4) otro usuario lo tiene en edición. Detalle en §7.1 y §13. Consulte **General → Consulta de parámetros** para ver el valor de esos flags.

### ¿Por qué no aparecen las leyendas que tiene cargadas el cliente?

En **carga nueva**, cada leyenda solo se copia del maestro cliente si el parámetro *Inicializar leyenda N desde cliente* está en **Sí** y el cliente tiene texto en esa leyenda. Si el parámetro está en **No** (común en instalaciones recientes), los campos arrancan vacíos aunque el cliente tenga leyendas en el ERP. En **edición** se muestran las leyendas del comprobante grabado, no las del cliente. Ver §6.13.

---

## 19. Resumen operativo

PedidosWeb concentra la operatoria comercial web en cuatro ejes:

1. **Dashboard** — KPIs operativos y mes en curso por estado.
2. **Carga** — pedidos y presupuestos con cabecera completa y renglones.
3. **Consultas** — comprobantes, detalle plano e informes comerciales.
4. **Soporte** — logs de integración y consulta de parámetros (General).

Grillas, idioma y acceso se rigen por [Generalidades.md](./Generalidades.md). Permisos y parámetros ERP determinan qué ve y qué puede modificar cada usuario.

---

## Referencias técnicas (soporte)

| Tema | Documento |
|------|-----------|
| Carga UI | [pantalla-carga-comprobante-ui.md](../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) |
| Consultas cabecera | [consulta-comprobantes-cabecera.md](../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md) |
| Detalle pedidos | [consulta-detalle-pedidos.md](../02-producto/PedidosWeb/consulta-detalle-pedidos.md) |
| Dashboard | [patron-dashboard-operativo-ui.md](../02-producto/PedidosWeb/patron-dashboard-operativo-ui.md) |
| Historias de usuario | [101-PedidosWeb/README.md](../03-historias-usuario/101-PedidosWeb/README.md) |
