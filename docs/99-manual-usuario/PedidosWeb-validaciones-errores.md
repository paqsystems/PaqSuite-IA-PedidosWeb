# PedidosWeb — Validaciones, mensajes y errores

| Campo | Valor |
|-------|--------|
| **Versión documento** | 2026-06-22 |
| **Ámbito** | Todas las validaciones que impiden grabar, importar, editar o eliminar comprobantes |
| **Manual principal** | [PedidosWeb.md](./PedidosWeb.md) |
| **Circuito de estados** | [PedidosWeb-circuito-estados.md](./PedidosWeb-circuito-estados.md) |

---

## 1. Cómo interpretar los mensajes

- Los textos en pantalla dependen del **idioma activo** del portal.
- Al grabar, si hay varios problemas, el sistema puede mostrar un **diálogo con lista** de errores (no solo el primero).
- Los mensajes de negocio suelen indicar **qué corregir** antes de reintentar.
- Una grabación **rechazada** no deja datos parciales en el servidor: debe corregir y volver a pulsar Grabar.

---

## 2. Requisitos previos a grabar (checklist)

Antes de **Grabar pedido** o **Grabar presupuesto**, verifique:

### Cabecera obligatoria

| Campo | Requisito |
|-------|-----------|
| Cliente | Seleccionado (o fijo en perfil cliente) |
| Vendedor | Completo automáticamente desde el cliente; solo lectura |
| Perfil de pedido | Seleccionado |
| Condición de venta | Seleccionada |
| Transporte | Seleccionado |
| Dirección de entrega | Seleccionada y válida para ese cliente |
| Lista de precios | Seleccionada |

### Renglones

| Requisito | Detalle |
|-----------|---------|
| Al menos un artículo | Con código de artículo no vacío |
| Cantidad | Mayor a cero en cada renglón activo |
| Precio | Mayor a cero si los parámetros de precio cero están inactivos (ver §4) |
| Sin duplicados | El mismo código de artículo **no puede repetirse** en un comprobante (validación en pantalla antes de agregar) |

### Botones de grabación deshabilitados

Si **Grabar pedido** / **Grabar presupuesto** aparecen deshabilitados (gris), lo habitual es:

- Aún no terminó de cargar la **cabecera** tras elegir cliente.
- No hay cliente seleccionado.
- No es un bloqueo de validación de renglones: esos se evalúan al pulsar Grabar.

---

## 3. Validaciones de cabecera al grabar (servidor)

| Mensaje habitual (ES) | Causa | Qué hacer |
|------------------------|-------|-----------|
| Debe seleccionar un cliente | `cod_cliente` vacío | Elegir cliente en combobox |
| El cliente no existe o no está disponible | Cliente fuera de visibilidad o inexistente | Verificar código y cartera; perfil cliente solo ve su código |
| El cliente está inhabilitado | Cliente marcado inhabilitado en maestro | Cambiar cliente o solicitar habilitación en ERP |
| Debe indicar un vendedor | Vendedor vacío en cabecera | Revisar ficha del cliente en ERP; re-elegir cliente |
| El vendedor no es válido | Código de vendedor inexistente | Corregir vendedor asignado al cliente en ERP |
| Debe seleccionar un perfil de pedido | Perfil vacío | Elegir perfil; revisar parámetro *Perfil de pedidos por defecto* |
| El perfil de pedido no es válido | Código no existe en catálogo | Elegir perfil del listado ERP |
| Debe seleccionar una condición de venta | Condición vacía o ≤ 0 | Completar lookup de condición |
| La condición de venta no es válida | Código inexistente | Elegir del catálogo |
| Debe seleccionar un transporte | Transporte vacío | Completar lookup |
| El transporte no es válido | Código inexistente | Elegir del catálogo |
| Debe seleccionar una dirección de entrega | `id_de` vacío o ≤ 0 | Elegir dirección del cliente |
| La dirección de entrega no es válida | Dirección no pertenece al cliente | Re-elegir cliente o dirección |
| Debe seleccionar una lista de precios | Lista vacía o ≤ 0 | Elegir lista antes de artículos |
| La lista de precios no es válida | Código inexistente | Elegir del catálogo |
| El nivel del comprobante no es válido | Con *Solo niveles 0 y 100* activo, nivel distinto de 0 o 100 | Cambiar nivel a 0 o 100, o desactivar parámetro en ERP |
| Debe agregar al menos un artículo | Sin renglones con artículo | Agregar líneas en la grilla |
| Hay artículos con precio cero o sin precio | Precio ≤ 0 y parámetros restrictivos activos | Cambiar lista, artículo o activar parámetros en ERP |

---

## 4. Parámetros ERP que afectan precios y artículos

Consultables en **General → Consulta de parámetros**:

| Parámetro (clave técnica) | Si está en **Sí** | Efecto en grabación |
|---------------------------|-------------------|---------------------|
| **Admitir artículos con precio cero** (`ArticulosPrecioCero`) | Permite renglones con precio 0 | Si **No**, rechaza grabación con precio cero |
| **Admitir artículos sin precio en lista** (`ArticulosSinPrecio`) | Permite artículos sin precio en lista | Si **No**, rechaza cuando no hay precio válido |
| **Solo niveles 0 y 100** (`NivelExtremo`) | Solo admite nivel 0 o 100 | Si **No**, acepta otros niveles del maestro |
| **Procesar clientes inhabilitados** (`ClientesInhabilitados`) | Permite operar clientes inhabilitados | Si **No**, clientes inhabilitados quedan fuera de selección |

**Nota:** el stock mostrado en carga es **informativo**; **no bloquea** la grabación aunque el disponible sea cero o negativo.

---

## 5. Permisos de modificación comercial (servidor)

Si el usuario **cambió** precio, bonificación de renglón, bonificaciones de cabecera o lista de precios sin permiso, la grabación falla:

| Mensaje habitual (ES) | Causa | Qué hacer |
|------------------------|-------|-----------|
| No tiene permiso para modificar precios | `precio_modificado` sin `ModificaPrecioV/S` | Dejar precio de lista o solicitar permiso en ERP |
| No tiene permiso para modificar bonificaciones de artículos | `porc_bonif_modificado` sin `ModificaBonArtV/S` | No editar bonif. de línea manualmente |
| No tiene permiso para modificar la bonificación del cliente | `descuento_modificado` sin `ModificaBonCliV/S` | No alterar bonif. 1–3 de cabecera |
| No tiene permiso para modificar la lista de precios | Lista cambiada sin `ModificaListaPrecV/S` | Usar lista del cliente |

### Perfil cliente (C)

Los usuarios con perfil **cliente** **nunca** pueden modificar precio, bonificaciones de artículo, bonificaciones de cliente ni lista de precios, aunque los parámetros estén en Sí para vendedor.

### Parámetros relacionados (por perfil V/S/C)

| Parámetro | Perfil |
|-----------|--------|
| `ModificaPrecioV` / `ModificaPrecioS` | Vendedor / Supervisor |
| `ModificaBonArtV` / `ModificaBonArtS` | Vendedor / Supervisor |
| `ModificaBonCliV` / `ModificaBonCliS` | Vendedor / Supervisor |
| `ModificaListaPrecV` / `ModificaListaPrecS` | Vendedor / Supervisor |
| `ModificaCondVtaC/V/S`, `ModificaDirEntrC/V/S`, `ModificaExpresoC/V/S`, `ModificaNivelC` | Habilitan edición de esos campos en cabecera |

Si un campo aparece **deshabilitado** en carga, suele deberse a estos flags o al modo solo lectura (Ver).

---

## 6. Validaciones en pantalla (antes del servidor)

| Situación | Mensaje / comportamiento | Acción |
|-----------|--------------------------|--------|
| Artículo ya cargado | «El artículo ya está en el comprobante» | Editar cantidad del renglón existente o quitar y volver a agregar |
| Cambio de cliente con renglones | Diálogo: se perderán renglones | Confirmar solo si acepta perder líneas |
| Combobox artículos deshabilitado | Sin lista de precios válida | Completar lista de precios en cabecera |
| Bonificación 3 fuera de rango | Control numérico limita -99,99 a 99,99 | Ajustar valor |
| Importación Excel con cliente ya elegido (vendedor) | Botón importar deshabilitado | Importar al inicio o limpiar pantalla |

---

## 7. Errores al editar, eliminar o convertir

| Clave / situación | Cuándo ocurre | Qué hacer |
|-------------------|---------------|-----------|
| **Edición en curso** | Otro usuario tiene el pedido en -1 dentro de MinutosWeb | Esperar, pedir que cancele/grabe, o esperar expiración |
| **Estado no editable** | Pedido no está en 0/-1, o presupuesto no está en 99 | Verificar estado en consulta |
| **Impide modificar pedidos** (`NOmodificaPedido`) | Parámetro global en Sí | No hay edición en portal; consultar parámetros |
| **Impide eliminar pedidos** (`NOeliminaPedido`) | Parámetro global en Sí | No hay eliminación; consultar parámetros |
| **Solo se puede eliminar ingresados** | Pedido no está en estado 0 | Solo pedidos ingresados son eliminables |
| **Presupuesto origen inválido** | Conversión desde presupuesto no activo (≠99) | Usar presupuesto activo |
| **Pedido origen inválido** | Conversión pedido→presupuesto con estado ≠0 | Solo pedidos ingresados |
| **Motivo de cierre inválido** | Catálogo de motivos sin el id configurado | Revisar `CodMotivoCierreExitoso` y catálogo ERP |
| **Presupuesto no editable** | Cierre o edición sobre presupuesto ≠99 | Solo activos se cierran o editan |
| **Comprobante no encontrado** | Código inexistente o fuera de visibilidad | Verificar permisos y cartera |

---

## 8. Importación Excel — errores por fila y por lote

Disponible solo en **carga nueva** sin renglones, si el tenant la habilitó. Regla general: **si una fila falla, no se importa nada** (todo o nada).

### 8.0 Formato de la planilla (reglas estructurales)

| Regla | Detalle |
|-------|---------|
| Columnas extra | **Permitidas** — se ignoran si el título no corresponde a la plantilla |
| Orden de columnas | **Libre** — puede reordenar o intercalar columnas de la plantilla |
| Quitar columnas de la plantilla | **No permitido** — deben estar **todas** las columnas del modelo descargado |
| Renombrar columnas | **No permitido** — usar los títulos de la plantilla (idioma de descarga o equivalente i18n) |
| Fila de títulos | **Obligatoria en fila 1**; datos desde fila 2 |
| Filas en blanco | **No permitidas** entre renglones de datos |
| Cabecera repetida por fila | Los campos de cabecera deben ser **idénticos en todas las filas** con datos |
| Tras importar | **Inicializa** pantalla de carga; **no graba**; puede editar y agregar renglones antes de Grabar |

### Errores por fila (habituales)

| Mensaje habitual (ES) | Causa | Corrección |
|------------------------|-------|------------|
| Columna no editable para su perfil | Completó precio, bonif., lista, etc. sin permiso | Dejar vacías columnas no editables; el sistema las resuelve |
| La cantidad debe ser mayor a cero | Cantidad ≤ 0 o vacía | Corregir cantidad |
| Artículo inexistente | Código no está en maestro | Verificar código |
| El artículo no puede ser de tipo BASE | Artículo marcado BASE en ERP | Usar presentación vendible, no el código base |
| Precio cero no permitido | Precio resuelto en 0 con parámetros restrictivos | Cambiar lista/artículo o parámetros |
| Nivel inválido para la configuración actual | Nivel ≠ 0 ni 100 con NivelExtremo activo | Usar 0 o 100 |
| El código cliente debe coincidir con su sesión | Perfil cliente importó otro cliente | Usar solo su código de cliente |

### Errores de coherencia del archivo (lote)

| Mensaje habitual (ES) | Causa | Corrección |
|------------------------|-------|------------|
| Todas las filas deben tener el mismo código cliente | Distintos `cod_cliente` en filas | Un solo cliente por archivo |
| Los datos de cabecera deben ser idénticos en todas las filas | Perfil, transporte, lista, etc. difieren entre filas | Repetir mismos valores de cabecera en cada fila |

### Tras importar correctamente

- El **vendedor** mostrado es el del **cliente** en ERP, no necesariamente el usuario logueado.
- Debe revisar cabecera, lookups y totales antes de grabar.
- La importación **no graba** el comprobante: **inicializa** cabecera y renglones en la pantalla de carga.
- Puede **modificar** cabecera o renglones importados y **agregar más líneas** antes de **Grabar pedido** o **Grabar presupuesto** (mismas validaciones que carga manual).

---

## 9. Correo al grabar

| Situación | ¿Se grabó el comprobante? | Acción |
|-----------|---------------------------|--------|
| Mail enviado correctamente | Sí | Ninguna |
| Fallo de envío de correo | **Sí** — la grabación no se revierte | Revisar configuración SMTP y parámetros mail en ERP; verificar destinatarios |

Parámetros relevantes: *Incluir detalle de renglones en mail*, *Destinatarios adicionales*, *Dirección remitente*, *Copia oculta global*.

---

## 10. Errores en consultas e informes

| Situación | Causa probable | Acción |
|-----------|----------------|--------|
| Grilla vacía | Filtros activos, sin datos en cartera, o criterios muy restrictivos | Limpiar filtros; **Actualizar**; revisar layout |
| No ve comprobante que existe en ERP | Fuera de universo visible | Verificar cartera del vendedor / cliente de sesión |
| Exportar deshabilitado | Sin filas visibles | Ampliar criterios o quitar filtros |
| Pivot no visible | Tenant sin pivots habilitados | Usar solo grilla o contactar administración |

---

## 11. Tabla rápida: «No puedo grabar» → revisar en orden

1. ¿Cliente y cabecera cargados? (lookups completos)
2. ¿Al menos un renglón con cantidad > 0?
3. ¿Artículos con precio válido según parámetros?
4. ¿Nivel 0 o 100 si NivelExtremo está activo?
5. ¿Cliente habilitado?
6. ¿Modificó precio/bonif./lista sin permiso?
7. ¿Mensaje de diálogo con lista de errores? Leer cada ítem y corregir.

---

## 12. Tabla rápida: «No puedo editar / eliminar» → revisar en orden

1. **General → Consulta de parámetros:** *Impide modificar pedidos* / *Impide eliminar pedidos*
2. Permiso de menú (modificación / baja)
3. Estado del comprobante (pedido 0, presupuesto 99)
4. Bloqueo -1 por otro usuario (MinutosWeb)
5. Visibilidad del comprobante en su cartera

---

## 13. Preguntas frecuentes sobre validaciones

### ¿Por qué me deja cargar un artículo sin stock?

El disponible es **informativo**. La política comercial puede permitir pedidos sin stock físico.

### ¿Puedo tener el mismo artículo dos veces?

No en el mismo comprobante. Puede agregar cantidades en un solo renglón.

### ¿La bonificación por cantidad respeta mis permisos?

La regla de descuento por cantidad del maestro se aplica al cambiar cantidad **aunque** no tenga permiso de modificar bonificación de artículo; la edición **manual** de bonificación sí respeta permisos.

### ¿Grabar presupuesto valida distinto que grabar pedido?

Las validaciones de cabecera y renglones son las **mismas**; cambia el estado resultante y la matriz de conversión (ver [circuito de estados](./PedidosWeb-circuito-estados.md)).

### ¿El chat asistente puede ver por qué falló mi grabación?

Solo si describe el mensaje que ve en pantalla. El asistente no accede al comprobante ni al ERP; orienta según este manual.
