# PedidosWeb — Parámetros que el usuario ve (solo lectura)

| Campo | Valor |
|-------|--------|
| **Versión documento** | 2026-09-01 |
| **Dónde consultarlos** | **General → Consulta de parámetros** |
| **Manual de la pantalla** | [Generalidades.md §18](./Generalidades.md) |
| **Público** | Operadores y soporte que necesitan interpretar *por qué* el portal se comporta de cierta forma |

---

## 1. Cómo usar este documento

Los parámetros **no se editan** en PedidosWeb. Los carga el ERP. Aquí se explica el **efecto visible** de los más usados, con el **nombre que suele aparecer en la consulta** (el identificador interno entre paréntesis es para soporte).

Valores **Sí/No** y fechas se muestran según el idioma del portal. Si un texto no tiene traducción, verá el original del ERP.

Si el comportamiento de una pantalla «no coincide con el manual», el primer paso de soporte es abrir **Consulta de parámetros** y contrastar esta tabla.

---

## 2. Sesión, edición y bloqueos globales

| Nombre en consulta (orientativo) | Clave habitual | Efecto si está activo / con valor |
|----------------------------------|----------------|-----------------------------------|
| Minutos de inactividad web | `MinutosWeb` | Cierra la sesión sin interacción. También es la ventana en la que un pedido **en modificación (-1)** sigue bloqueado para otros. |
| Impide modificar pedidos | `NOmodificaPedido` | **Nadie** ve Editar (pedidos ingresados ni presupuestos activos), aunque el rol tenga permiso. |
| Impide eliminar pedidos | `NOeliminaPedido` | **Nadie** ve Eliminar en pedidos ingresados. |

---

## 3. Carga de cabecera y renglones

| Nombre en consulta | Clave habitual | Efecto |
|--------------------|----------------|--------|
| Perfil de pedidos por defecto | `CodPerfilPedidos` | Perfil inicial al elegir cliente en un **alta nueva**. Si está vacío o en cero, el operador debe elegir perfil. |
| Inicializar leyenda 1…5 desde cliente | `ClienteLeyenda1` … `ClienteLeyenda5` | En alta nueva, copia esa leyenda del maestro cliente. Cada una es independiente. Las leyendas tienen tope de **60 caracteres**. |
| Solo niveles 0 y 100 | `NivelExtremo` | El campo nivel de cabecera solo admite 0 o 100. |
| Admitir artículos con precio cero | `ArticulosPrecioCero` | Si **No**, no se graba (ni se copia, según modo) un renglón a precio 0. |
| Admitir artículos sin precio en lista | `ArticulosSinPrecio` | Si **No**, no se graba un artículo sin precio válido en la lista. |
| Actualizar precios al copiar comprobante | `ActualizarPrecioCopia` | **No** (habitual): copia precios del origen (igual los valida hoy). **Sí**: toma precios de la lista de la cabecera copiada. |
| Carga unidades de venta | `CargaUnidadesVenta` | La cantidad del renglón se carga en unidades de venta (bultos); el sistema muestra la equivalencia en unidades de stock. El mail puede mostrar **Bultos** y **Unidades**. |
| Incluye artículos no stockeables | (informativo) | Lo usa quien alimenta el maestro de artículos. En PedidosWeb, los no stockeables **no muestran stock** en la búsqueda ni en el informe Stock. |
| Carga recurrente post grabación | (según instalación) | Tras grabar, limpia la pantalla para un nuevo comprobante o vuelve al listado. |

### Permisos comerciales (vendedor / supervisor)

Cada uno existe en versión **V** (vendedor) y **S** (supervisor). El perfil **cliente** no modifica precio, lista ni bonificaciones, aunque estos flags estén en Sí para V/S.

| Nombre orientativo | Clave | Si está en **No** |
|--------------------|-------|-------------------|
| Modifica precio | `ModificaPrecioV` / `S` | No puede cambiar el precio de lista del renglón |
| Modifica bonificación de artículo | `ModificaBonArtV` / `S` | No edita a mano la bonificación de línea (el descuento **por cantidad** del maestro igual puede aplicarse) |
| Modifica bonificación del cliente | `ModificaBonCliV` / `S` | No altera bonificaciones 1–3 de cabecera |
| Modifica lista de precios | `ModificaListaPrecV` / `S` | No cambia la lista respecto de la del cliente |

Otros flags de cabecera (condición de venta, dirección de entrega, expreso, nivel) habilitan o bloquean esos campos según perfil C/V/S.

---

## 4. Correo al grabar

| Nombre orientativo | Efecto |
|--------------------|--------|
| Envío de mail al grabar / modificar | Si está inactivo, no se envía correo (el comprobante **sí** se graba). |
| Incluir detalle en mail | Agrega la tabla de renglones (con precio neto; **Bultos** y **Unidades** si hay unidades de venta). |
| Destinatarios (cliente, vendedor, supervisor, lista extra, CCO) | Quién recibe el aviso. |

Si el correo **falla**, verá un aviso y el pedido **igual quedó grabado**. Eso no se «deshace».

---

## 5. Consultas e informes

| Nombre orientativo | Clave | Efecto |
|--------------------|-------|--------|
| Días de ventas detalladas | `DiasVentasDetalladas` | Acota el historial de ventas (además puede filtrar fecha desde/hasta en pantalla). |
| Motivo de cierre exitoso | `CodMotivoCierreExitoso` | Motivo **positivo** que se aplica **solo** al convertir presupuesto → pedido. Si está mal configurado, la conversión falla. |

---

## 6. Qué no es un parámetro de esta pantalla

- Idioma y apariencia del usuario → **Preferencias** / avatar.
- Habilitar pivot, Excel o el ABM de seguridad → configuración del **tenant / instalación**, no una fila más de esta grilla.
- Precios de lista, stock, clientes → **maestros ERP**, no esta consulta.

---

## 7. Preguntas frecuentes

### ¿Por qué no puedo editar si mi compañero sí?

Si *Impide modificar pedidos* está en **Sí**, **nadie** edita. Si está en **No**, compare permisos de rol y el estado del comprobante.

### ¿Por qué al copiar me rechazó precios que el pedido original tenía?

Los parámetros de precio cero / sin precio se evalúan **hoy**. Un pedido viejo pudo grabarse con otras reglas. Ver [PedidosWeb §6.9.2](./PedidosWeb.md).

### ¿El chat sabe el valor de mis parámetros?

No. El Chat Asistente IA explica **qué hacen**. El valor de **su** empresa está en esta consulta.
