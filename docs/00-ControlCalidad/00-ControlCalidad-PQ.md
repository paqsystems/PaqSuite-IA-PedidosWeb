# Control de Calidad — PQ

| Campo | Valor |
|-------|--------|
| **ID archivo** | `00-ControlCalidad-PQ` |
| **Responsable** | Pablo Quarracino (PQ) |
| **Alcance** | Hallazgos de pruebas manuales y mejoras solicitadas por cliente en PaqSuite PedidosWeb |
| **Metodología** | Open-Spec / SDD — [`_OPEN-SPEC-METODOLOGIA.md`](../_base/_OPEN-SPEC-METODOLOGIA.md) |
| **Dispatcher** | Parte **G** (volcado), **H** (cierre opcional), ciclo **G → D → E → F → I** |

## Propósito

Registro operativo de **incidencias y mejoras** detectadas fuera del flujo automatizado de tests. Cada sesión de control se numera secuencialmente y conserva trazabilidad hasta su derivación a **SPEC-update**, **HU-update** y **TR-update** en `docs/.../updates/`.

Este archivo **no sustituye** SPEC, HU ni TR: es la **entrada** del circuito de correcciones (Parte G).

## Convenciones

| Tema | Regla |
|------|-------|
| **Fecha** | Formato `dd/MM/yyyy` en todo el documento |
| **Bloques** | `## Control de Calidad #N` — numeración incremental |
| **Ítems** | Preferir `### HU-XXX-slug` cuando la HU sea identificable |
| **Marcas de gestión** | `*Procesado*` tras volcado G; `*Sugerencia: HU-…*` si aún no hay HU asociable |
| **Comando de volcado** | `Corrige los errores del dd/MM/yyyy de PQ` (o *Realiza las mejoras…* / *Procesa las solicitudes…*) |

## Estados del bloque (*Referencia del control*)

| Estado | Significado |
|--------|-------------|
| **Pendiente** | Control registrado; ítems sin volcar a `updates/` |
| **Con Sugerencias** | Volcado parcial: quedan ítems con sugerencia de HU sin archivo generado |
| **A Programar** | Todas las entradas marcadas `*Procesado*`; pendiente cierre formal G/H |
| **Especificado** | Parte G (o H) cerró el bloque: volcado documental completo; cola activa en `docs/.../updates/` — **no** implica código implementado ni **`Finalizado`** en metadatos de HU/TR |

> El **Estado** bajo *Referencia del control* es independiente del **Estado** en metadatos de HU/TR ([`07-estado-hu-tr.md`](../../.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md)).

## Flujo tras registrar un control

1. Registrar hallazgos en el bloque `#N` con estado **Pendiente**.
2. Ejecutar **Parte G** (`Corrige… dd/MM/yyyy de PQ`): §0 SPEC-update si cambia el alcance; HU-update / TR-update.
3. Implementar (**D**), tests (**E**), verificación (**F**).
4. Marcar updates **`Finalizado`** (manual) y **Parte I** (unificar).

## Índice de controles

| # | Fecha | Estado | Resumen |
|---|-------|--------|---------|
| 5 | 09/06/2026 | Finalizado (Parte I) | Listbox artículos carga: disponible solo `stock − comprometido` (sin pedidos ingresados); display con base opcional — unificado 11/06/2026 |
| 4 | 10/06/2026 | Finalizado (Parte I) | Vista pivot en informes: Detalle, Deudas, Cheques, Stock — unificado 16/06/2026 |
| 1 | 04/06/2026 | Finalizado (Parte I) | 10 familias HU — CC PQ; updates unificados 09/06/2026 |
| 2 | 05/06/2026 | Finalizado (Parte I) | GEN-03 layouts/export Excel formateado — CC PQ #2; unificado 09/06/2026 |
| 3 | 09/06/2026 | Finalizado (Parte I) | Cartel cargando, layouts totales, performance carga, parámetros — unificado 09/06/2026 |

---

## Control de Calidad #6

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 15/06/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Pendiente |

### Hallazgos

en carga/edición de pedidos, Verificar que se esté inicializando el control de Perfiles con el parámetro correspondiente. y que al grabar se estén haciendo todas las validaciones indispensables.

### Errores encontrados - Mejoras solicitadas

#### i18n - procesos donde no se está aplicando.

no se está traduciendo como corresponde :
- la grilla de Consulta de Parámetros (ni descripción, valor ni comentario)
- los captions o títulos de datos en los pivots.
corregir y colocarlo como fuente de verdad.

#### HU-101-005-inicializacion-cabecera

Cuando se comienza un nuevo comprobante, después de elegir el cliente, el control de selección de perfiles se debe inicializar con el valor INT del parámetro "CodPerfilPedidos"

#### HU-101-005-inicializacion-cabecera

Rediseñar estéticamente la pantalla conforme la imagen que se ve en `docs\02-producto\PedidosWeb\Diseño Carga Pedidos.png`

#### HU-101-009-grabar-pedido · HU-101-010-grabar-presupuesto

Cuando se graba un pedido o presupuesto, hay que verificar que se hayan completado todos estos controles, con un valor válido (qué exista en tabla correspondiente):
- Cliente
- Vendedor
- perfil
- condición venta
- transporte
- Dirección Entrega
- Lista de Precios
Además agregar estas validaciones : 
- que exista por lo menos un renglón en el detalle de artículos
- Si parámetro “NivelExtremo” es True, ese dato solo puede valer 0 ó 100.
- Si parámetro "Artículopreciocero" o "Articulossinprecio" es false, no puede haber registros con precio cero. 
- que el cliente tenga el atributo inhabilitado=FALSE (0).


---

## Control de Calidad #5

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 09/06/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Finalizado (Parte I 11/06/2026) |
| **Entorno probado** | Local — PHPUnit CC PQ #5 + QA manual PQ |
| **Build / rama** | `v1.1.0` working tree |

### Hallazgos

Corrección en el **listbox de artículos** de la pantalla de **carga de pedidos**: el cálculo de disponible para el texto del ítem **no debe** consultar ni descontar cantidades de **pedidos ingresados** (`estado = 0` u otro agregado tipo `comprometido_web`). Aplica tanto al artículo como al artículo **base** (si existe).

**Comportamiento vigente a corregir:** `StockConsultaService::lookupDisponibilidadPorCodigos` y producto [pantalla-carga-comprobante-ui.md](../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) §3.1 usan `disponible_neto = stock − comprometido − comprometido_web`. Esa regla permanece válida para la **consulta de stock**; en el lookup de carga debe usarse solo columnas de `pq_pedidosweb_stock`.

**Referencia producto:** [pantalla-carga-comprobante-ui.md](../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) — combobox artículos, `GET /articulos`.

### Errores encontrados - Mejoras solicitadas

#### HU-101-005-inicializacion-cabecera · HU-101-006-carga-renglones

En el listbox/combobox de artículos de carga de pedidos:

a) **No** incluir en el disponible la búsqueda ni el descuento de pedidos ingresados para calcular disponibilidad — ni del artículo en sí, ni del artículo base.

b) Disponible artículo = `pq_pedidosweb_stock.stock` menos `pq_pedidosweb_stock.comprometido` (sin `comprometido_web` ni equivalente desde `pq_pedidosweb_pedidos` / `pq_pedidosweb_pedidosdetalle`).

c) Disponible artículo base (solo si el artículo posee `base` en `pq_pedidosweb_articulos`): misma fórmula sobre el stock del código base — `stock − comprometido` en `pq_pedidosweb_stock` del artículo base.

d) **Formato de cada ítem** en la lista:

| Parte | Contenido |
|-------|-----------|
| 1 | Código artículo |
| 2 | ` - ` |
| 3 | Descripción |
| 4 | ` - ` |
| 5 | Disponible artículo (resultado de b) |
| 6 | Entre paréntesis: disponible del artículo base (resultado de c), **solo si posee base** |

Ejemplo sin base: `ART001 - Tornillo M8 — Disp. 150,00`

Ejemplo con base: `ART002 - Kit ensamble — Disp. 80,00 (120,00)`

e) **Alcance:** solo lookup/browse de artículos en carga (`GET /articulos` sin `codigos`); no alterar la consulta de stock ni otros procesos que deban seguir usando disponible neto con pedidos web.

*Procesado* → [SPEC-101-10-pantalla-carga](../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) · [HU-101-005-inicializacion-cabecera](../03-historias-usuario/101-PedidosWeb/HU-101-005-inicializacion-cabecera.md) · [TR-SPEC-101-10-pantalla-carga](../04-tareas/101-PedidosWeb/TR-SPEC-101-10-pantalla-carga.md) — Parte I 11/06/2026

---

## Control de Calidad #4

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 10/06/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Finalizado (Parte I 16/06/2026) |
| **Entorno probado** | Local — Vitest + E2E pivot informes + **QA manual PQ** (Parte F 11/06/2026) |
| **Build / rama** | `v1.1.0` working tree — Parte G 11/06/2026 |

### Hallazgos

Mejora solicitada: incorporar **vista pivot** (tabla dinámica) en los informes **Detalle de pedidos**, **Deudas**, **Cheques** y **Stock**, siguiendo la norma transversal [SPEC-001-08-pivots](../05-open-spec/001-Generaliddes/SPEC-001-08-pivots.md) (patrón ya validado en **Historial ventas**). El resto de consultas del portal (p. ej. Pedidos ingresados, pendientes, presupuestos) queda **fuera de alcance** en esta entrega.

**Dependencia documental:** SPEC-001-08 (B1 cerrado); infra transversal HU-GEN-08-* y TR-GEN-08-* a derivar en Parte G antes o en paralelo a la adopción en 101.

**Referencias producto vigentes:**

| Informe | Producto | Proceso UI / permiso |
|---------|----------|----------------------|
| Detalle de pedidos | [consulta-detalle-pedidos.md](../02-producto/PedidosWeb/consulta-detalle-pedidos.md) | `pw_detallepedidos` |
| Deudas | [consulta-deuda.md](../02-producto/PedidosWeb/consulta-deuda.md) | `pw_deuda` / `pw_deudaclientes` |
| Cheques | [consulta-cheques.md](../02-producto/PedidosWeb/consulta-cheques.md) | `pw_cheques` / `pw_consultacheques` |
| Stock | [consulta-stock.md](../02-producto/PedidosWeb/consulta-stock.md) | `pw_stock` / `pw_consultastock` |

### Errores encontrados - Mejoras solicitadas

#### HU-101-028-consulta-detalle-pedidos · HU-101-021-consulta-deuda · HU-101-022-consulta-cheques · HU-101-018-consulta-stock

Incorporar en cada informe listado la alternancia **grilla / pivot** según norma MONO (`pivots.md`, SPEC-001-08):

a) Vista inicial **grilla** (sin cambiar comportamiento operativo actual); toggle para pasar a **PivotGrid** DevExtreme cuando el usuario lo elija.

b) Consultas pivotables en estas cuatro pantallas: metadata en catálogo `pq_pivots_*` por `consulta_id` / proceso, con `pivotBase` útil según columnas de cada informe:

| Informe | Dimensiones sugeridas (pivotBase) | Métricas sugeridas |
|---------|-----------------------------------|--------------------|
| Detalle de pedidos | cliente, artículo, período | cantidad, importes, precio neto |
| Deudas | cliente, tipo comprobante, vencimiento | saldo |
| Cheques | cliente, banco, estado, vencimiento | importe |
| Stock | artículo, depósito (si aplica) | stock, comprometido, disponible neto |

c) Paridad GEN-03 en bloque pivot: diseños guardados (`pq_pivots_config`), plantilla inicial, Actualizar (`pivotRefresh`), export Excel básico y tabla dinámica — según [SPEC-001-08 / HU-GEN-08](../03-historias-usuario/001-Generaliddes/README.md).

d) **Fuera de alcance v1:** Pedidos ingresados, pedidos pendientes, presupuestos, historial ventas (ya adoptado como piloto transversal) y demás consultas SPEC-101-11; no habilitar pivot allí en este control salvo las cuatro anteriores.

e) Derivar como **update** (no reemplazar HU/TR base): SPEC-update slice 101 (adopción pivot informes), HU-update sobre HU-101-028, HU-101-021, HU-101-022 y HU-101-018; TR-update sobre TR-SPEC-101-07 / TR-SPEC-101-11; referenciar SPEC-001-08 y contexto `_mono/pivots/`.

*Procesado* → [SPEC-101-11-consultas-ui](../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) · [HU-101-028](../03-historias-usuario/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos.md) · [HU-101-021](../03-historias-usuario/101-PedidosWeb/HU-101-021-consulta-deuda.md) · [HU-101-022](../03-historias-usuario/101-PedidosWeb/HU-101-022-consulta-cheques.md) · [HU-101-018](../03-historias-usuario/101-PedidosWeb/HU-101-018-consulta-stock.md) · [TR-SPEC-101-11-consultas-ui](../04-tareas/101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md) — Parte G 11/06/2026 · **Parte I 16/06/2026**

---

## Control de Calidad #3

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 09/06/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Finalizado (Parte I 09/06/2026) |
| **Entorno probado** | Local — Vitest + Playwright E2E + PHPUnit + **QA manual PQ** (09/06/2026) |
| **Build / rama** | `v1.1.0` working tree |

### Hallazgos

Registrar cada ítem bajo la HU correspondiente (`### HU-XXX-slug`) o como bullet con contexto suficiente para derivar HU en Parte G.

### Errores encontrados - Mejoras solicitadas

#### Regla General UI

Mientras se está completando la lista de un listbox/combobox, que aparezca un cartel en tamaño pequeño "cargando..." y bloquear el acceso a esa lista hasta que finalice el proceso.

*Procesado* → [SPEC-001-01-experiencia-base.md](../05-open-spec/001-Generaliddes/SPEC-001-01-experiencia-base.md) · [HU-GEN-01-shell-layout.md](../03-historias-usuario/001-Generaliddes/HU-GEN-01-shell-layout.md) · [TR-GEN-01-shell-layout.md](../04-tareas/001-Generaliddes/TR-GEN-01-shell-layout.md) — Parte I 09/06/2026

#### Regla General Plantillas Grillas (Layouts)

Cuando se guarda un modelo, no se están guardando las totalizaciones definidas en el footer.

*Procesado* → [SPEC-001-03-ui-transversal.md](../05-open-spec/001-Generaliddes/SPEC-001-03-ui-transversal.md) · [HU-GEN-03-layouts-grilla.md](../03-historias-usuario/001-Generaliddes/HU-GEN-03-layouts-grilla.md) · [TR-GEN-03-layouts-grilla.md](../04-tareas/001-Generaliddes/TR-GEN-03-layouts-grilla.md) — Parte I 09/06/2026

#### Listas/Combobox : general

Si es factible, que si al escribir un texto para búsqueda encuentra un solo item, ya lo coloque directamente como resultado.

*Procesado* → [SPEC-001-01-experiencia-base.md](../05-open-spec/001-Generaliddes/SPEC-001-01-experiencia-base.md) · [HU-GEN-01-shell-layout.md](../03-historias-usuario/001-Generaliddes/HU-GEN-01-shell-layout.md) — Parte I 09/06/2026

#### HU-101-005-inicializacion-cabecera

a) aplicar el punto anterior en la lista de clientes.
b) Analizar la posibilidad de optimizar tiempos para completar la lista de clientes
c) Analizar la posibilidad de optimizar tiempos para completar la lista de artículos, que demora mucho más que la de clientes
d) Analizar la posibilidad de optimizar tiempos para el recálculo de precios de los artículos en la grilla cuando se cambia la lista de precios.
e) Lista de artículos: que muestre también el código (separar con " - " con la descripción)

*Procesado* → [SPEC-101-10-pantalla-carga.md](../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) · [HU-101-005-inicializacion-cabecera.md](../03-historias-usuario/101-PedidosWeb/HU-101-005-inicializacion-cabecera.md) · [TR-SPEC-101-10-pantalla-carga.md](../04-tareas/101-PedidosWeb/TR-SPEC-101-10-pantalla-carga.md) — Parte I 09/06/2026

#### Consulta de Parámetros

La columna "Valor" que muestre título y datos centralizado.

*Procesado* → [SPEC-001-04-configuracion-global.md](../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) · [HU-GEN-04-consulta-parametros.md](../03-historias-usuario/001-Generaliddes/HU-GEN-04-consulta-parametros.md) · [TR-GEN-04-consulta-parametros.md](../04-tareas/001-Generaliddes/TR-GEN-04-consulta-parametros.md) — Parte I 09/06/2026

---

## Control de Calidad #2

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 05/06/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Finalizado (Parte I 09/06/2026) |
| **Entorno probado** | Local — QA manual PQ (Excel + reset plantilla) |
| **Build / rama** | v1.1.0-paq |

### Hallazgos

Registrar cada ítem bajo la HU correspondiente (`### HU-XXX-slug`) o como bullet con contexto suficiente para derivar HU en Parte G.

### Errores encontrados - Mejoras solicitadas

#### HU-GEN-03-layouts-grilla

a) en la descripción de los layouts a seleccionar, destacar de algún modo los que son propios por ej: colocando " (*)" al terminar la descripción.
b) que la opción "plantilla del sistema" presente la grilla original provista por el sistema, considerar que cualquier cambio que se haga sobre la misma, el botón "Guardar" se debe comportar como "Guardar como..." (no alterar la plantilla base).
c) Que estos puntos a) y b) se agregue a las reglas de especificación standard de layouts-grilla.

*Procesado* → [HU-GEN-03-layouts-grilla.md](../03-historias-usuario/001-Generaliddes/HU-GEN-03-layouts-grilla.md) · [TR-GEN-03-layouts-grilla.md](../04-tareas/001-Generaliddes/TR-GEN-03-layouts-grilla.md) — Parte I 09/06/2026

#### HU-GEN-03-exportaciones

a) en la exportación a Excel en modo "Formateado", No se visualiza ninguna diferencia con la básica. Debería respetarse : 
Fecha : Formato fecha según i18n
Entero : Numérico sin decimales
Decimales : Númerico según decimales del campo (si no se puede determinar, 2 decimales)
booleanos : VERDADERO o FALSO 
Titulos resaltados, y si es factible con cambio de color (BackColor tonalidad Gris)
Totalizar columnas numéricas y decimales
b) Que se agreguen estas definiciones a las reglas de especificación standard de layouts-grilla.

*Procesado* → [HU-GEN-03-exportaciones.md](../03-historias-usuario/001-Generaliddes/HU-GEN-03-exportaciones.md) · [TR-GEN-03-exportaciones.md](../04-tareas/001-Generaliddes/TR-GEN-03-exportaciones.md) — Parte I 09/06/2026

---

## Control de Calidad #1

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 04/06/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Finalizado (Parte I 09/06/2026) |
| **Entorno probado** | *(completar: local / staging / producción)* |
| **Build / rama** | *(completar si aplica)* |

### Hallazgos

Registrar cada ítem bajo la HU correspondiente (`### HU-XXX-slug`) o como bullet con contexto suficiente para derivar HU en Parte G.

### Errores encontrados - Mejoras solicitadas

#### HU-GEN-02-expiracion-inactividad

Está considerando el tiempo de expiración desde que inició la sesión, en lugar de considerar desde la ultima acción del usuario (o hay alguna acción que no se está considerando)

*Procesado* → *Unificado Parte I 09/06/2026* → [HU-GEN-02-expiracion-inactividad.md](../03-historias-usuario/001-Generaliddes/HU-GEN-02-expiracion-inactividad.md)

#### HU-101-004-seleccion-cliente

Agregar en la descripción, además de la razón social, el nombre de fantasía y el código de cliente (entre paréntesis).
Formato: `(codigo) {razonSocial} - {nombreFantasia}`
Habilitar opción de ordenamiento: código, razón social o nombre fantasía.

*Procesado* → *Unificado Parte I 09/06/2026* → [HU-101-004-seleccion-cliente.md](../03-historias-usuario/101-PedidosWeb/HU-101-004-seleccion-cliente.md)

#### HU-101-005-inicializacion-cabecera

a) Que la tercera bonificación admita negativos: rango admitido -99.99 a 99.99
b) Agregar en la grilla de renglones el "precio neto unitario" (ver punto anterior).
c) Al cambiar precio de lista o alguna bonificación → recalcular los precios e importes si ya hay renglones ingresados en el detalle

*Procesado* → *Unificado Parte I 09/06/2026* → [HU-101-005-inicializacion-cabecera.md](../03-historias-usuario/101-PedidosWeb/HU-101-005-inicializacion-cabecera.md)

#### HU-101-006-carga-renglones

a) Búsqueda artículos: no incluir artículos BASE (`pq_pedidosweb_articulos.usa_esc = 'B'`)
b) Agregar "precio neto unitario", no editable, que sea el precio de lista menos el descuento renglón y el descuento cabecera.
NOTA: Persistir en el campo existente `pq_pedidosweb_pedidosdetalle.precio_neto` (no requiere nuevo atributo en BD).

*Procesado* → *Unificado Parte I 09/06/2026* → [HU-101-006-carga-renglones.md](../03-historias-usuario/101-PedidosWeb/HU-101-006-carga-renglones.md) · [SPEC-101-10](../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) · [TR-SPEC-101-10](../04-tareas/101-PedidosWeb/TR-SPEC-101-10-pantalla-carga.md)

#### Consultas (HU-101-015, HU-101-016, HU-101-017, HU-101-028)

a) Incluir el nombre comercial del cliente como columna nueva
b) Revisar el formato fecha del título "Fecha Ultimo Proceso": `dd/MM/yyyy HH:mm`, respetando i18n, sin segundos ni milésimas
c) Agregar un icono "Actualizar" que refresque la información de la grilla

*Procesado* → *Unificado Parte I 09/06/2026* → HU-101-015/016/017/028 + [SPEC-101-07](../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md) / [SPEC-101-11](../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md)

#### HU-101-017-consulta-pedidos-pendientes

Agregar el icono "Copiar" como se encuentra en "Pedidos Ingresados" y "Presupuestos Ingresados"

*Procesado* → *Unificado Parte I 09/06/2026* → [HU-101-017-consulta-pedidos-pendientes.md](../03-historias-usuario/101-PedidosWeb/HU-101-017-consulta-pedidos-pendientes.md)

#### HU-101-028-consulta-detalle-pedidos

a) Mostrar columna **Precio neto unitario** mapeada a `precio_neto` (ver punto más arriba). Solo afecta HU-101-028

*Procesado* → *Unificado Parte I 09/06/2026* → [HU-101-028-consulta-detalle-pedidos.md](../03-historias-usuario/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos.md)

#### HU-101-019-mail-grabar

a) Incluir en los renglones del mail **Precio neto unitario** desde `precio_neto` (ver punto más arriba)
b) Está imprimiendo el "importe neto" sin los descuentos
c) Está imprimiendo el "importe bruto" sin los descuentos

*Procesado* → *Unificado Parte I 09/06/2026* → [HU-101-019-mail-grabar.md](../03-historias-usuario/101-PedidosWeb/HU-101-019-mail-grabar.md) · [SPEC-101-13](../05-open-spec/101-PedidosWeb/SPEC-101-13-mails.md)

#### HU-101-025-dashboard

a) En los dashboard existentes, que además de cantidad de comprobantes e importe total, muestre cantidad de unidades (suma de `pq_pedidosweb_pedidosdetalle.cantidad`)
b) Agregar un nuevo dashboard mensual con los mismos datos, considerando estados 0, 1, 2, 3, 98 y 99

*Procesado* → *Unificado Parte I 09/06/2026* → [HU-101-025-dashboard.md](../03-historias-usuario/101-PedidosWeb/HU-101-025-dashboard.md) · [SPEC-101-14](../05-open-spec/101-PedidosWeb/SPEC-101-14-dashboard.md)

---

## Referencias

- Dispatcher Parte G/H/I: [`.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md`](../../.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md)
- Estados HU/TR: [`.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md`](../../.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md)
- Gobernanza SPEC-update: [`.cursor/rules/base/00-arquitectura/08-open-spec-gobernanza.md`](../../.cursor/rules/base/00-arquitectura/08-open-spec-gobernanza.md)
