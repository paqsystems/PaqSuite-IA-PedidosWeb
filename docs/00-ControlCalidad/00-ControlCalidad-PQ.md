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
| 1 | 04/06/2026 | Finalizado (Parte I) | 10 familias HU — CC PQ; updates unificados 09/06/2026 |
| 2 | 05/06/2026 | Pendiente | GEN-03 layouts/grillas/export |
| 14 | 27/05/2026 | Pendiente | *(en curso)* |

---

## Control de Calidad #3

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 09/06/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Pendiente |
| **Entorno probado** | *(completar: local / staging / producción)* |
| **Build / rama** | *(completar si aplica)* |

### Hallazgos

Registrar cada ítem bajo la HU correspondiente (`### HU-XXX-slug`) o como bullet con contexto suficiente para derivar HU en Parte G.

### Errores encontrados - Mejoras solicitadas

#### cartel "cargando..." y bloquear busqueda clientes

#### lentitud armado listado artículos

#### lentitud actualizar precios tras cambio de lista

---

## Control de Calidad #2

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 05/06/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Pendiente |
| **Entorno probado** | *(completar: local / staging / producción)* |
| **Build / rama** | *(completar si aplica)* |

### Hallazgos

Registrar cada ítem bajo la HU correspondiente (`### HU-XXX-slug`) o como bullet con contexto suficiente para derivar HU en Parte G.

### Errores encontrados - Mejoras solicitadas

#### HU-GEN-03-layouts-grilla

a) en la descripción de los layouts a seleccionar, destacar de algún modo los que son propios por ej: colocando " (*)" al terminar la descripción.
b) que la opción "plantilla del sistema" presente la grilla original provista por el sistema, considerar que cualquier cambio que se haga sobre la misma, el botón "Guardar" se debe comportar como "Guardar como..." (no alterar la plantilla base).
c) Que este punto b) se agregue a las reglas de especificación standard de layouts-grilla.


#### HU-GEN-03-grillas-listados

a) Definir dentro de la regla de especificaciones standard de grillas, que cuando son procesos de tipo "Informes", agregue un icóno "actualizar" (con tooltip correspondiente en formato i18n), preferentemente junto al icono de "selección de campos", que vuelva a obtener los datos que se presentan en la grilla.

#### HU-GEN-03-exportaciones

en la exportación a Excel en modo "Formateado", No se visualiza ninguna diferencia con la básica. Debería respetarse : 
Fecha : Formato fecha según i18n
Entero : Numérico sin decimales
Decimales : Númerico según decimales del campo (si no se puede determinar, 2 decimales)
booleanos : VERDADERO o FALSO 
Titulos resaltados, y si es factible con cambio de color (BackColor tonalidad Gris)
Totalizar columnas numéricas y decimales

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
