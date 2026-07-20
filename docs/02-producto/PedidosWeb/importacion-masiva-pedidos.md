# Importación Masiva de Pedidos / Presupuestos

| Campo | Valor |
|-------|--------|
| **Estado** | Definición conceptual — **A1 + B1 + C + C1** (2026-07-19); autoriza D1 |
| **Ámbito** | PedidosWeb — web desktop únicamente (excluido de mobile Capacitor, igual que importación Excel) |
| **Proceso** | `pw_importacionmasiva` — menú «Importación masiva» (permiso propio) |
| **Plantilla** | Misma estructura que [Importación Pedido Individual desde Excel](./Importación%20Pedido%20Individual%20desde%20Excel.md); proceso Excel `PEDIDO_MASIVO` |
| **Relacionados** | [pantalla-carga-comprobante-ui.md](./pantalla-carga-comprobante-ui.md), [consulta-comprobantes-cabecera.md](./consulta-comprobantes-cabecera.md) |
| **SPEC** | [SPEC-101-21](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |
| **HU** | [043](../../03-historias-usuario/101-PedidosWeb/HU-101-043-proceso-excel-pedido-masivo.md) · [044](../../03-historias-usuario/101-PedidosWeb/HU-101-044-pantalla-importacion-masiva.md) · [045](../../03-historias-usuario/101-PedidosWeb/HU-101-045-consultar-borrador-importacion-masiva.md) |
| **TR** | [21a](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-proceso-excel-pedido-masivo.md) · [21b](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-pantalla-importacion-masiva.md) · [21c](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-consultar-borrador-importacion-masiva.md) — [C1](../../04-tareas/101-PedidosWeb/F-101-21-cierre-c1-importacion-masiva.md) |

---

## 1) Objetivo

Permitir incorporar **varios** pedidos o presupuestos desde un único archivo Excel (misma plantilla que la importación individual), agruparlos por cabecera en una grilla de trabajo, elegir tipo (pedido vs presupuesto) en masa o por fila, revisar en solo lectura y **grabar el lote** reutilizando los mismos procesos de grabación actuales (incluido envío de mails), con tolerancia a fallos parciales.

---

## 2) Alcance y fuera de alcance

### 2.1 Incluye

- Nueva pantalla / proceso de menú **Importación masiva**.
- Importación Excel multi-comprobante con agrupación por cabecera.
- Grilla de cabeceras (trabajo en memoria) con toggle de tipo y acciones.
- Marcado masivo pedido / presupuesto.
- Consulta en pantalla tradicional de carga en **solo lectura total**.
- Eliminación de filas del borrador (con confirmación).
- Grabación en lote con éxito/error por comprobante.
- Guardas de navegación con datos sin grabar.
- Pregunta de reemplazo vs. agregar al reimportar con grilla no vacía.

### 2.2 Fuera de alcance

- Mobile / Capacitor (exclusión funcional de importación Excel).
- Edición de cabecera o renglones desde la grilla masiva o desde “Consultar”.
- Persistencia de borrador en servidor (solo sesión del navegador).
- Cambiar la plantilla Excel respecto de la importación individual (se reutiliza).
- Indicar tipo pedido/presupuesto **desde el Excel** (el tipo se decide solo en la grilla).

---

## 3) Modelo mental del proceso

1. El usuario entra al proceso: **grilla vacía**.
2. Importa un Excel (plantilla vigente de pedido individual, usándola para **N** comprobantes).
3. El sistema **agrupa filas** por combinación de cabecera y arma un comprobante por grupo.
4. Cada comprobante aparece como fila de la grilla, **inicialmente como Pedido**.
5. El usuario ajusta tipos (fila a fila o botones masivos), consulta detalle en solo lectura, elimina filas no deseadas.
6. Pulsa **Grabar**: se procesa el lote; los OK salen de la grilla; los errores permanecen con mensaje.
7. Puede corregir (reimportar / eliminar / volver a grabar solo lo pendiente) o abandonar con confirmación.

**Persistencia del borrador:** únicamente en memoria/sesión del navegador. Al cerrar pestaña, logout o pérdida de sesión, el trabajo no grabado se pierde (previa guarda de UI cuando aplique; ver §9).

---

## 4) Agrupación desde Excel

### 4.1 Clave de agrupación

Por cada **combinación distinta** de:

| Campo | Rol |
|-------|-----|
| Cliente | Parte de la clave (`cod_cliente` del Excel) |
| Vendedor | Parte de la clave — **no** viene en plantilla; se resuelve del maestro cliente (§4.5) |
| Nivel | Parte de la clave — campo de cabecera del comprobante (§4.4) |

se confecciona **un comprobante distinto**.

El resto de datos de cabecera (condición de venta, depósito, observaciones, fechas, etc.) forman parte de la **clave de agrupación** junto con cliente y vendedor: si cambian, se genera **otro** pedido/presupuesto (un mismo cliente puede aportar varios comprobantes en el lote). Solo se consolidan renglones cuando la cabecera completa coincide (ver SPEC-101-21).

### 4.2 Tipo inicial

- Toda fila generada por importación entra como **Pedido** (`esPedido = true`).
- El Excel **no** define ni mezcla tipos; presupuesto solo vía UI (toggle o botones masivos).

### 4.3 Validación de plantilla

- Misma estructura/columnas e i18n que la importación individual; proceso Excel `PEDIDO_MASIVO`.
- Comportamiento distinto al individual: la importación masiva **sí** admite múltiples cabeceras (varios comprobantes) en un solo archivo.
- Errores de estructura/parseo/validación del archivo: **no** se aplica el lote parcial a la grilla (fallo de importación de archivo). Una vez en grilla, las filas pueden fallar **más tarde** en la grabación (ver §8).

### 4.4 Campo nivel (cabecera)

Es el mismo atributo **nivel** de la carga tradicional de comprobantes (`pq_pedidosweb_pedidoscabecera.nivel` / UI `cabecera-nivel`):

- Entero de cabecera, conceptualmente en el rango **0–100**.
- Incide en reglas de compatibilidad de cantidades en renglones (definición conceptual §12.3).
- Si el parámetro **`NivelExtremo`** está activo, solo se admiten valores **0** o **100** (validación al grabar; la importación Excel individual ya es coherente con esa restricción cuando aplica).
- **No** es la lista de precios.

En la grilla masiva se muestra el valor importado/agrupado; no se edita desde la grilla (solo lectura en esta pantalla). La edición seguiría siendo vía carga tradicional, fuera de alcance de este proceso en modo consultar.

### 4.5 Origen del vendedor

La plantilla Excel **no** incluye columna vendedor. Al leer la planilla y armar/validar cada comprobante, el sistema toma **siempre** el vendedor que posee el cliente en maestro (`pq_pedidosweb_clientes.cod_vended` y nombre vía relación), del mismo modo en que se resuelve la razón social u otros defaults de cabecera (`CabeceraInicialService`). Ese `cod_vended` resuelto forma parte de la clave de agrupación y de las columnas visibles en grilla.

### 4.6 Perfil cliente (C)

Si el proceso lo ejecuta un usuario de tipo **cliente**, todo `cod_cliente` del Excel **debe coincidir** con el cliente de la sesión. Divergencia → error de importación (lote de archivo no se aplica a la grilla).

---

## 5) Pantalla — grilla de trabajo

### 5.1 Estado inicial

Grilla **vacía** (sin filas). Acciones de importación disponibles.

### 5.2 Columnas (datos básicos de cabecera)

| Orden | Columna / control | Notas |
|-------|-------------------|--------|
| 1 | Toggle **tipo** | `false` = Presupuesto · `true` = Pedido. Valor inicial: Pedido. Editable por fila. |
| — | Cliente | Código + razón social |
| — | Vendedor | Código + descripción (resueltos del maestro cliente, §4.5) |
| — | Nivel | Entero 0–100 de cabecera (§4.4); solo lectura en esta grilla |
| — | Total sin IVA | Calculado tras importación (mismos cálculos de renglón / totales que el flujo Excel individual) |
| — | Total con IVA | Idem |
| — | Error | Columna fija; se completa tras fallo de grabación |
| — | Acciones | Iconos **Consultar** y **Eliminar** |

### 5.3 Toolbar / acciones de proceso

| Acción | Comportamiento |
|--------|----------------|
| Importar Excel | Ver §7 |
| Exportar / descargar plantilla | Misma plantilla modelo que importación individual |
| Marcar todos como **Pedidos** | Pone el toggle de **todas** las filas en Pedido |
| Marcar todos como **Presupuestos** | Pone el toggle de **todas** las filas en Presupuesto |
| **Grabar** | Procesa el lote (§8) |

Los botones masivos **no** impiden el cambio individual posterior del toggle.

UI: DevExtreme (`DataGrid`, `Switch`/`CheckBox` según patrón de toggle, `Button` con iconos, `Popup` para confirmaciones). i18n obligatorio. `data-testid` estables (definir en TR).

---

## 6) Acciones por fila

### 6.1 Toggle Pedido / Presupuesto

- Cambia solo el tipo del comprobante en el borrador.
- No recalcula la clave de agrupación ni reimporta el Excel.
- Visible y editable mientras la fila exista en la grilla (antes o después de un intento de grabación fallido).

### 6.2 Consultar

- Navega (o abre) la **pantalla tradicional de carga** del comprobante correspondiente.
- Modo **solo lectura total**: sin editar cabecera ni líneas y **sin** poder Grabar desde esa pantalla.
- Debe existir forma clara de **volver** a la grilla masiva (mismo proceso / solapa) sin perder el resto del borrador de sesión.

### 6.3 Eliminar

- Quita el comprobante **solo** del borrador en memoria (aún no existe en ERP como grabado del lote).
- **Siempre** solicita confirmación modal antes de eliminar.

---

## 7) Reimportar con grilla no vacía

Si el usuario inicia una nueva importación Excel **habiendo filas** en la grilla, el sistema pregunta (modal):

1. **Eliminar lo existente** e importar el nuevo archivo (reemplazo total del borrador).
2. **Agregar** al contenido actual (nuevos grupos se suman; ver regla de colisión abajo).
3. **Cancelar** la importación.

**Colisión al agregar:** si un grupo genera la misma clave visual (cliente + vendedor resuelto + nivel) que una fila ya existente → **crear otra fila** de borrador con `idInterno` distinto (**no** fusionar).

---

## 8) Grabar el lote

### 8.1 Reglas

- Procesa **todos** los comprobantes presentes en la grilla.
- Orden: **orden de aparición en la grilla**.
- Orquestación: **N llamadas secuenciales desde el frontend** a las APIs de grabación individuales (pedido/presupuesto según toggle). Sin endpoint de lote síncrono en MVP.
- Durante el ciclo, la UI muestra progreso i18n **«Cargando x de N»**.
- Cada comprobante usa el **mismo proceso de grabación** que hoy aplica a pedido o presupuesto (validaciones, persistencia, integración, **envío de mails**, etc.).
- Error en un comprobante **no inhibe** la grabación del resto (best-effort por ítem).

### 8.2 Resultado en UI

- Al finalizar: **resumen** tipo toast/mensaje con cantidad OK / cantidad con error (textos i18n).
- Filas grabadas con éxito: **se quitan** de la grilla.
- Filas con error: **permanecen** en la grilla, con mensaje en columna **Error**.
- El usuario puede corregir datos reimportando, eliminar la fila, o reintentar **Grabar** sobre lo que quedó.

### 8.3 Contraste con importación individual

| Aspecto | Importación individual (carga) | Importación masiva |
|---------|--------------------------------|--------------------|
| Cantidad de cabeceras | Una (pedido en edición) | Varias |
| Error de grabación / proceso | Según flujo individual | No bloquea al resto del lote |
| Tipo P/P | Flujo de la pantalla de carga | Toggle en grilla (siempre parte como Pedido) |

---

## 9) Salida del proceso con datos sin grabar

Si hay comprobantes en la grilla (borrador no vacío) y el usuario intenta:

- cambiar de solapa / cerrar la solapa del proceso, o
- cambiar de opción de menú, o
- salir del proceso de forma equivalente,

presentar **modal** con tres opciones:

| Opción | Efecto |
|--------|--------|
| **Cancelar el proceso** | Descarta el borrador de sesión y abandona |
| **Grabar todo** | Ejecuta el mismo flujo de §8; abandonar el proceso **solo** si el 100 % de filas grabó OK; con errores parciales permanece |
| **Retornar al proceso** | Cierra el modal y permanece en la grilla |

---

## 10) Permisos, menú y plataforma

- Procedimiento / menú: **`pw_importacionmasiva`** (i18n ES de referencia: «Importación masiva»).
- **Permiso propio**, independiente de `pw_cargapedidos` y de la importación Excel embebida en carga.
- **Solo web** desktop; no entra al menú MVP mobile ni a `pedidosWebMobilePolicy` como proceso habilitado.

---

## 11) Criterios de aceptación (borrador)

1. Al abrir el proceso, la grilla está vacía.
2. Con la plantilla Excel vigente se pueden generar **varios** comprobantes; uno por cada combinación distinta cliente + vendedor + nivel.
3. Toda fila nueva aparece como **Pedido**; el Excel no define el tipo.
4. Existen acciones para marcar **todos** como pedidos y **todos** como presupuestos; el toggle por fila sigue permitiendo cambio individual.
5. Consultar abre la carga tradicional en **solo lectura total** (sin edición ni grabación).
6. Eliminar pide confirmación y solo saca la fila del borrador en memoria.
7. Grabar = N llamadas FE secuenciales; UI «Cargando x de N»; fallo de uno no detiene al resto; OK salen; errores quedan con mensaje; toast resumen final.
8. Reimportar con grilla no vacía pregunta eliminar existente vs. agregar vs. cancelar.
9. Intentar salir con borrador no vacío muestra modal: cancelar proceso / grabar todo / retornar.
10. El borrador no se persiste en servidor; solo sesión.
11. Mobile: proceso no disponible.

---

## 12) Decisiones de producto (cerradas — trazadas en SPEC-101-21)

| Tema | Decisión |
|------|----------|
| Colisión al Agregar | Permitir fila duplicada (`idInterno` distinto); no fusionar |
| Columnas cliente/vendedor | Código + descripción |
| Error post-grabación | Columna fija en grilla + toast resumen |
| Modal salida → Grabar todo | Salir solo con 100 % OK |
| Menú / permiso | `pw_importacionmasiva` |
| Límite lote | Sin tope de producto en MVP |
| Validaciones | Import: estructura/fila Excel (sin parcial de archivo). Grabar: reglas de grabación individual |
| Vendedor | Del maestro cliente al armar/validar |
| Perfil C | `cod_cliente` Excel = cliente de sesión |
| Grabación lote | N llamadas FE secuenciales + progreso «Cargando x de N» |
| i18n plantilla | Reutilizar `PEDIDO_INDIVIDUAL.*` |
| Consultar | `/pedidos/carga` readonly + Volver |
| Coherencia grupo | Todos los campos cabecera 101-16 idénticos dentro del grupo |
| Orden grilla | Primera aparición del grupo en el Excel |

---

## 13) Trazabilidad documental

1. ~~OpenSpec~~ → [SPEC-101-21](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) (**Parte A** + **A1**).
2. ~~Parte B / B1~~ → HU-101-043…045.
3. ~~Parte C / C1~~ → TR 21a/21b/21c ([C1](../../04-tareas/101-PedidosWeb/F-101-21-cierre-c1-importacion-masiva.md)); siguiente **D1**.
4. Actualizar exclusiones mobile y menú MVP en implementación.
)
