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
| 14 | 27/05/2026 | Pendiente | *(en curso)* |

---

## Control de Calidad #1

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 04/06/2026 |
| **Responsable** | Pablo Quarracino (PQ) |
| **Estado** | Pendiente |
| **Entorno probado** | *(completar: local / staging / producción)* |
| **Build / rama** | *(completar si aplica)* |

### Hallazgos

Registrar cada ítem bajo la HU correspondiente (`### HU-XXX-slug`) o como bullet con contexto suficiente para derivar HU en Parte G.

### Errores encontrados - Mejoras solicitadas

#### HU-GEN-02-expiracion-inactividad

Está considerando el tiempo de expiración desde que inició la sesión, en lugar de considerar desde la ultima acción del usuario (o hay alguna acción que no se está considerando)

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

#### HU-101-004-seleccion-cliente

Agregar en la descripción, además de la razón social, el nombre de fantasía y el código de cliente (entre paréntesis).
Formato : (xxxxxx) {razónsocial} - nombre_fantasia{}}
habilitar opción de ordenamiento : codigo, razón social o nombre fantasía

#### HU-101-005-inicializacion-cabecera

a) Que la tercera bonificación admita negativos : rango admitido : -99.99 a 99.99
b) agregar en la grilla de renglones el "precio neto unitario" (ver punto anterior).
c) Al cambiar precio de lista o alguna bonificación -> Recalcular los precios e importes si ya hay renglones ingreados en el detalle

#### HU-101-006-carga-renglones

a) busqueda articulos : no incluir artículos BASE (atributo pq_pedidosweb_articulos.base="")
b) Agregar "precio neto unitario", no editable, que sea el precio de lista menos el descuento renglon y el descuento cabecera.
NOTA : Si lo amerita, según los requerimientos a posteriori sobre este dato, generar un atributo nuevo en la tabla pq_pedidosweb_pedidosdetalle.

#### Consultas
HU-101-015-consulta-pedidos-ingresados
HU-101-016-consulta-presupuestos
HU-101-017-consulta-pedidos-pendientes
HU-101-028-consulta-detalle-pedidos

a) incluir el nombre comercial del cliente como columna nueva
b) Revisar el formato fecha del titulo "Fecha Ultimo Proceso". debe ser "dd/MM/yyyy hh:mm", respetando el i18n, y sin segundos ni milecimas de segundo
c) Agregar un icono "Actualizar" que refresque la información de la grilla.

#### HU-101-017-consulta-pedidos-pendientes

Agregar el icono "Copiar" como se encuentra en "Pedidos Ingresados" y "Presupuestos Ingresados"

#### HU-101-028-consulta-detalle-pedidos

a) Agregar atributo "Precio Neto Unitario" (ver punto más arriba). solo afecta el HU-101-028

#### HU-101-019-mail-grabar

a) agregar en los renglones el Agregar atributo "Precio Neto Unitario" (ver punto más arriba)
b) Está imprimiendo el "importe neto" sin los descuentos. 
c) Está imprimiendo el "importe bruto" sin los descuentos

#### HU-101-025-dashboard

a) En los dashboard existentes, que además de cantidad de comprobantes e importe total, muestre Cantidade de unidades (suma de pq_pedidosweb_pedidosdetalle.cantidad)
b) Agregar un nuevo dashboard con los mismos datos del anterior, pero a nivel mensual, considerando todos los estados : pedidos ingresados (estado 0), pedidos pendientes (estado 1), pedidos aprobados (estado 2), pedidos facturados (estado 3), prespuestos cerrados (estado 98) y presupuestos abiertos (estado 99)

---

## Referencias

- Dispatcher Parte G/H/I: [`.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md`](../../.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md)
- Estados HU/TR: [`.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md`](../../.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md)
- Gobernanza SPEC-update: [`.cursor/rules/base/00-arquitectura/08-open-spec-gobernanza.md`](../../.cursor/rules/base/00-arquitectura/08-open-spec-gobernanza.md)
