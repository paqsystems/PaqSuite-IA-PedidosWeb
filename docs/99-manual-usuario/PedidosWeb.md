# PedidosWeb — Manual de usuario

| Campo | Valor |
|-------|--------|
| **Versión documento** | MVP Fase 1 — 2026-06-03 |
| **Ámbito** | Módulo comercial PedidosWeb |
| **Manual transversal** | [Generalidades.md](./Generalidades.md) (login, menú, grillas, idioma) |

---

## 1. Introducción

Este manual describe la operatoria del módulo **PedidosWeb**: carga y consulta de **pedidos** y **presupuestos**, consultas comerciales (stock, deuda, cheques, historial), **dashboard operativo** y herramientas de apoyo.

Está pensado como **documento de consulta y referencia** para:

- vendedores, supervisores y usuarios con perfil **cliente** que operan pedidos desde el portal;
- soporte funcional y técnico que debe orientar sobre flujos, estados, permisos y validaciones;
- la generación del **asistente conversacional (chatbot)** del módulo, que tomará este manual como base documental.

Para login, navegación general, idioma, apariencia y uso estándar de **grillas**, consultar primero [Generalidades.md](./Generalidades.md).

---

## 2. Alcance

### Incluye

- Dashboard operativo (KPIs).
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

Comprobante comercial en curso o confirmado. Estados habituales en consultas:

| Estado | Significado habitual |
|--------|----------------------|
| **Ingresado** (0) | Pedido cargado, pendiente de procesamiento comercial |
| **En edición** (-1) | Bloqueado mientras un usuario lo modifica en el portal |
| **Pendiente** (1) | En cartera pendiente según reglas comerciales |

### Presupuesto

Oferta comercial. Estados habituales:

| Estado | Significado habitual |
|--------|----------------------|
| **Activo** (99) | Presupuesto vigente, editable según permisos |
| **Cerrado** (98) | Presupuesto cerrado (aceptado, rechazado u otro motivo de cierre) |

### Comprobante

Término genérico que puede referirse a un **pedido** o un **presupuesto**.

### Cabecera y renglones

- **Cabecera:** cliente, vendedor, condiciones comerciales, transporte, lista de precios, bonificaciones, leyendas, observaciones, etc.
- **Renglones:** artículos con cantidad, precio, bonificación de línea e importes.

### Perfil funcional

Define qué ve y qué puede hacer el usuario:

- **Vendedor / supervisor:** elige cliente, accede a cartera según visibilidad.
- **Cliente:** cliente fijo de sesión; no elige otro cliente en carga.

Los permisos concretos (modificar precios, bonificaciones, etc.) dependen de parámetros ERP y del rol asignado.

---

## 4. Menú y acceso a procesos

Tras el login, el menú lateral agrupa los procesos PedidosWeb. Los ítems visibles dependen del **perfil y permisos** del usuario.

| Grupo / proceso | Uso principal |
|-----------------|---------------|
| **Dashboard** | Indicadores operativos del día |
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

Muestra **ocho indicadores (KPIs)** de la operatoria comercial del usuario (según visibilidad de datos):

- pedidos y presupuestos del día;
- montos;
- indicadores de cartera y actividad reciente.

### Uso

1. Ingresar al portal.
2. Abrir **Dashboard** en el menú.
3. Revisar los KPIs; hacer clic en un indicador si el proceso permite navegar al detalle relacionado.

Los KPIs respetan la **visibilidad** del usuario (vendedor ve su cartera; supervisor puede ver un universo más amplio según configuración).

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
| **Grilla renglones** | Líneas del comprobante con importes |
| **Totales** | Subtotal, IVA, total |
| **Leyendas 1–5** | Textos al pie del comprobante |

### 6.2 Alta de un comprobante nuevo

1. Abrir **Carga** (modo nuevo).
2. **Seleccionar cliente** (vendedor/supervisor) o ver cliente fijo (perfil cliente).
3. Revisar la **cabecera** inicializada desde el maestro cliente (condición de venta, transporte, lista de precios, bonificaciones, perfil de pedido, etc.).
4. Buscar **artículos**, agregar renglones y completar cantidades en el popup de edición.
5. Revisar **totales** y leyendas.
6. **Verificar la cabecera:** todos los campos obligatorios cargados por lookup deben tener valor seleccionado (ver §6.10).
7. Grabar como **pedido** o **presupuesto** según corresponda.
8. Tras grabar, confirmar el mensaje de éxito; el sistema puede ofrecer limpiar la pantalla para un nuevo comprobante (según parámetro *carga recurrente*).

### 6.3 Perfil de pedido

Campo combobox en cabecera. Define el perfil comercial del comprobante (catálogo ERP). Valor inicial según parámetro **CodPerfilPedidos** en altas; en edición se muestra el valor **grabado** del comprobante.

### 6.4 Bonificaciones e importes

- **Bonificaciones 1, 2 y 3** en cabecera (si el usuario tiene permiso de modificación).
- **Bonificación neta** calculada automáticamente (solo lectura).
- Cada renglón muestra precio, bonificación de línea e **importe neto** con la bonificación neta de cabecera aplicada.
- Popup de renglón: importe bruto, neto, IVA y neto con IVA.

### 6.5 Lista de precios

Al cambiar la lista de precios en cabecera, el sistema **recalcula precios** de los renglones ya cargados según la nueva lista.

### 6.6 Editar un comprobante existente

Desde consultas de pedidos o presupuestos, acción **Editar**:

- La pantalla carga **cabecera y renglones del comprobante** (no reinicializa desde el cliente).
- Pedidos en estado ingresado pasan a **en edición** (-1) mientras se modifican.
- Al **Cancelar** o salir, se libera el bloqueo de edición.

### 6.7 Ver, copiar y convertir

| Modo | Comportamiento |
|------|----------------|
| **Ver** | Solo lectura |
| **Copiar** | Nuevo comprobante con datos del origen |
| **Convertir** | Presupuesto → pedido (o según acción disponible) |

### 6.8 Mail al grabar

Si el parámetro ERP lo habilita, al grabar o modificar se envía notificación por correo. Si el envío falla, puede mostrarse un aviso en pantalla sin revertir la grabación.

### 6.9 Cancelar

**Cancelar** abandona la pantalla. Si había una edición iniciada, se intenta liberar el bloqueo del pedido en el servidor.

### 6.10 Requisitos para grabar pedido o presupuesto

Para **Grabar pedido** o **Grabar presupuesto**, deben estar completos **todos los datos obligatorios** de la cabecera y al menos **un renglón válido** (artículo con cantidad mayor a cero).

Los datos comerciales de cabecera se cargan mediante **combobox (lookup)** contra catálogos ERP. Tras elegir el cliente, el sistema propone valores habituales desde el maestro cliente; el operador debe **confirmar que cada lookup obligatorio tenga un ítem seleccionado** antes de grabar:

| Campo (lookup) | Obligatorio |
|----------------|-------------|
| **Cliente** | Sí (combobox vendedor/supervisor; fijo en perfil cliente) |
| **Perfil de pedido** | Sí |
| **Condición de venta** | Sí |
| **Transporte** | Sí |
| **Dirección de entrega** | Sí |
| **Lista de precios** | Sí |
| **Renglones** | Al menos uno con artículo |

Campos **informativos** (solo lectura), como vendedor, moneda o incluye IVA, se completan automáticamente desde el cliente o la lista de precios; no requieren selección manual, pero conviene revisarlos.

Si falta un dato obligatorio o un renglón válido, el sistema muestra un **aviso en pantalla** (texto según idioma activo) e impide completar la grabación hasta corregir la situación señalada.

---

## 7. Consultas de comprobantes

Comparten el patrón de **grilla** descrito en [Generalidades §16](./Generalidades.md): filtros, layouts, exportación Excel y acciones por fila.

### 7.1 Pedidos ingresados

Listado de pedidos en estado **ingresado** (y relacionados según reglas del proceso).

**Acciones habituales** (según permisos): ver, editar, eliminar, copiar, convertir a presupuesto.

Columnas de **cabecera comercial** visibles: cliente, vendedor, condición de venta, transporte, lista de precios, bonificaciones, totales, etc.

### 7.2 Pedidos pendientes

Listado de pedidos en cartera **pendiente**. Consulta orientada a seguimiento operativo.

### 7.3 Presupuestos ingresados

Incluye presupuestos **activos (99)** y **cerrados (98)**.

**Acciones habituales:** ver, editar (activos), copiar, convertir a pedido, **cerrar presupuesto** (con motivo de cierre cuando aplique).

---

## 8. Consulta Detalle de pedidos

Ruta: **Pedidos → Detalle de pedidos**.

Muestra una **grilla plana**: cada fila combina datos de **cabecera** y de **renglón** (artículo, cantidades, precios, importes).

- Incluye comprobantes en **todos los estados** visibles para el usuario.
- La columna **Estado** muestra la **descripción** del estado (no solo el código numérico).
- Proceso de **solo consulta** (sin alta ABM desde esta grilla).
- Exportación a Excel según permisos y datos visibles.

**Cuándo usarla:** análisis detallado de líneas vendidas, auditoría de renglones o exportación masiva cabecera+detalle.

---

## 9. Consultas comerciales (Informes)

Ubicadas en el grupo **Informes**. Todas son procesos de **consulta** con grilla transversal.

### 9.1 Deuda de clientes

Saldos y composición de deuda según visibilidad del usuario. Filtros por cliente, vendedor u otros criterios expuestos en columnas.

### 9.2 Cheques en cartera

Cheques registrados con fechas, importes y estado. Útil para seguimiento de cobranzas.

### 9.3 Historial de ventas

Ventas detalladas en un rango temporal configurable (parámetro **DiasVentasDetalladas** en ERP). Permite análisis histórico por artículo, cliente o vendedor según columnas disponibles.

### 9.4 Stock

Disponibilidad de artículos (stock neto comprometido). La misma lógica de disponibilidad se refleja al buscar artículos en **carga**.

---

## 10. Cierre de presupuestos

Desde **Presupuestos ingresados**, acción **Cerrar** (según permisos):

1. Seleccionar un presupuesto activo.
2. Elegir **motivo de cierre** (catálogo ERP).
3. Confirmar.

El presupuesto pasa a estado **cerrado (98)** y deja de editarse como activo.

---

## 11. Logs de integración

Ruta: **Integración → Logs de integración**.

Consulta técnica de eventos de integración (fechas, tipos, mensajes). Proceso de **solo lectura** para soporte y supervisión.

Filtros por rango de fechas y tipo de evento. No modifica datos de negocio.

---

## 12. Permisos y visibilidad

### Visibilidad de datos

- **Vendedor:** ve clientes y comprobantes de su cartera.
- **Supervisor:** universo ampliado según configuración.
- **Cliente:** ve solo su propio código de cliente.

### Permisos de acción (ejemplos)

| Acción | Depende de |
|--------|------------|
| Consultar listados | Permiso de consulta (`Permiso_Repo`) por procedimiento |
| Alta / grabación | Permiso de alta |
| Edición | Permiso de modificación + estado del comprobante |
| Eliminación pedido | Permiso de baja + estado ingresado |
| Modificar precio / bonif. en carga | Parámetros `ModificaPrecio`, `ModificaBonArt*`, `ModificaBonCli`, `ModificaListaPrec` |

Si una acción no aparece en la grilla, el usuario **no tiene permiso** o el estado del comprobante no lo permite.

---

## 13. Validaciones habituales en carga

- Antes de grabar, cumplir los **requisitos de cabecera y renglones** (§6.10).
- No duplicar el mismo **código de artículo** en un comprobante.
- Al **cambiar cliente** con renglones cargados, el sistema pide confirmación porque se perderán las líneas.
- Bonificaciones y precios pueden estar **deshabilitados** según permisos ERP.

---

## 14. Mensajes de error y advertencia

Este manual **no enumera todos los mensajes** que puede mostrar el circuito de carga, grabación, consultas o integración. Los textos exactos dependen del **idioma activo** y pueden variar según la validación concreta (pantalla o servidor). Sí define la **interpretación funcional** habitual, alineada con [Generalidades §10](./Generalidades.md).

### Cómo interpretar un mensaje en pantalla

1. Leer el aviso mostrado: indica la causa inmediata (dato faltante, permiso, estado del comprobante, etc.).
2. Corregir lo señalado — en grabación, revisar primero los **lookups obligatorios** de cabecera (§6.10) y los renglones.
3. Si el mensaje persiste o no es claro, anotar **usuario**, **hora**, **número o código de comprobante** (si aplica) y escalar a soporte técnico.

### Situaciones frecuentes (no catálogo exhaustivo)

| Situación | Interpretación funcional | Acción sugerida |
|-----------|------------------------|-----------------|
| No permite grabar | Cabecera incompleta, sin renglones o sin permiso | Completar lookups obligatorios (§6.10); agregar renglones; verificar permisos |
| Grilla vacía en consulta | Filtros activos o sin datos en cartera | Revisar filtros y layout; ampliar criterios |
| No puedo editar un pedido | Bloqueo en edición (-1) de otro usuario, o sin permiso | Esperar liberación del bloqueo o contactar soporte |
| Mail no enviado tras grabar | Fallo de correo; la grabación sí se realizó | Revisar parámetros mail en ERP; usar canal alternativo si aplica |
| Totales distintos al esperado | Bonificaciones o IVA interpretados distinto | Verificar bonificación neta de cabecera y % IVA en renglones |

Para mensajes de **acceso, sesión o permisos generales**, consultar [Generalidades §10](./Generalidades.md).

---

## 15. Problemas frecuentes

- Confundir **presupuesto** con **pedido** al grabar (usar botón correcto en toolbar).
- Editar cabecera esperando que cambie el **cliente** sin perder renglones (el sistema advierte antes).
- Buscar un artículo en stock **cero** y asumir error del sistema (puede ser disponibilidad real).
- Esperar **tratativas** completas en MVP (alcance parcial / Should).
- No ver **Detalle de pedidos** en menú (requiere seed de menú y permiso; contactar administrador).

---

## 16. Recomendaciones de uso

- Completar y **verificar lookups obligatorios** de cabecera antes de grabar (§6.10).
- Completar **cabecera** (especialmente lista de precios) antes de cargar muchos renglones — la lista define precios.
- Usar **layouts** de grilla en consultas frecuentes ([Generalidades §16](./Generalidades.md)).
- Tras grabar, verificar el **número visible** en el mensaje de confirmación.
- En edición, usar **Cancelar** para liberar bloqueo si no se grabará.
- Revisar **Consulta de parámetros** (General) para entender flags como mail, minutos de edición o permisos de modificación.

---

## 17. Preguntas frecuentes

### ¿Qué necesito para grabar un pedido o presupuesto?

Cliente seleccionado, **todos los lookups obligatorios** de cabecera con valor (perfil, condición de venta, transporte, dirección de entrega, lista de precios) y al menos **un renglón** con artículo. Detalle en §6.10.

### ¿Puedo tener pedido y presupuesto abiertos a la vez en la misma pantalla?

No. La pantalla de carga trabaja un comprobante a la vez. Use consultas para alternar entre documentos.

### ¿El cliente puede cargar pedidos?

Sí, si tiene perfil **cliente** y permisos de alta; verá su cliente fijo sin combobox de selección.

### ¿Puedo exportar el detalle de pedidos?

Sí, desde **Detalle de pedidos** con el botón Exportar de la grilla (si hay datos visibles).

### ¿Por qué no veo bonificaciones editables?

El parámetro ERP o su rol puede inhibir **ModificaBonCli** / **ModificaBonArt***.

### ¿La conversión presupuesto → pedido borra el presupuesto?

Genera un **pedido nuevo** vinculado al origen; el presupuesto origen sigue su ciclo de vida (consultar reglas comerciales de la empresa).

---

## 18. Resumen operativo

PedidosWeb concentra la operatoria comercial web en cuatro ejes:

1. **Dashboard** — panorama del día.
2. **Carga** — pedidos y presupuestos con cabecera completa y renglones.
3. **Consultas** — comprobantes, detalle plano e informes comerciales.
4. **Soporte** — logs de integración y consulta de parámetros (General).

El comportamiento de **grillas**, **idioma** y **acceso** se rige por [Generalidades.md](./Generalidades.md). Los permisos y parámetros ERP determinan qué ve y qué puede modificar cada usuario.

---

## Referencias técnicas (soporte)

| Tema | Documento producto |
|------|-------------------|
| Carga UI | [pantalla-carga-comprobante-ui.md](../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) |
| Consultas cabecera | [consulta-comprobantes-cabecera.md](../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md) |
| Detalle pedidos | [consulta-detalle-pedidos.md](../02-producto/PedidosWeb/consulta-detalle-pedidos.md) |
| Dashboard | [patron-dashboard-operativo-ui.md](../02-producto/PedidosWeb/patron-dashboard-operativo-ui.md) |
| Historias de usuario | [101-PedidosWeb/README.md](../03-historias-usuario/101-PedidosWeb/README.md) |
