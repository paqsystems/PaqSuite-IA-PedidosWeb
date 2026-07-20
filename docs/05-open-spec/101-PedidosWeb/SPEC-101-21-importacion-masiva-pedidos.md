# SPEC-101-21 — Importación masiva de pedidos / presupuestos

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Producto** | [importacion-masiva-pedidos.md](../../02-producto/PedidosWeb/importacion-masiva-pedidos.md) |
| **Estado** | Especificado — **A1 + B1 + C + C1** (2026-07-19); autoriza D1 |
| **Prioridad épica** | Should (extensión post-MVP; reutiliza plantilla / motor GEN-07 y grabación 101-04/13) |
| **Revisión A1** | [F-101-21-cierre-a1-importacion-masiva](../../04-tareas/101-PedidosWeb/F-101-21-cierre-a1-importacion-masiva.md) |
| **HU relacionadas** | [HU-101-043](../../03-historias-usuario/101-PedidosWeb/HU-101-043-proceso-excel-pedido-masivo.md) · [HU-101-044](../../03-historias-usuario/101-PedidosWeb/HU-101-044-pantalla-importacion-masiva.md) · [HU-101-045](../../03-historias-usuario/101-PedidosWeb/HU-101-045-consultar-borrador-importacion-masiva.md) |
| **TR relacionadas** | [21a](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-proceso-excel-pedido-masivo.md) · [21b](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-pantalla-importacion-masiva.md) · [21c](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-consultar-borrador-importacion-masiva.md) |

## Objetivo

Permitir a un usuario autorizado incorporar **varios** pedidos o presupuestos desde un único archivo Excel (misma plantilla de columnas que la importación individual), agruparlos por cabecera en una **grilla de trabajo en sesión**, elegir tipo pedido/presupuesto (masivo o por fila), consultar el detalle en la pantalla tradicional de carga en **solo lectura**, y **grabar el lote** reutilizando los mismos procesos de grabación vigentes (validaciones, persistencia, integración y **mails**), con tolerancia a errores parciales.

## Fuentes

| Fuente | Rol |
|--------|-----|
| [importacion-masiva-pedidos.md](../../02-producto/PedidosWeb/importacion-masiva-pedidos.md) | Definición de producto |
| [SPEC-101-16](SPEC-101-16-importacion-pedido-individual-excel.md) | Plantilla `PEDIDO_INDIVIDUAL`, i18n columnas, defaults, validaciones de fila Excel |
| [SPEC-001-07-importar-excel](../001-Generaliddes/SPEC-001-07-importar-excel.md) | Motor genérico Excel |
| [SPEC-101-10](SPEC-101-10-pantalla-carga.md) / [pantalla-carga-comprobante-ui.md](../../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) | Pantalla de carga (consultar solo lectura) |
| [SPEC-101-04](SPEC-101-04-services-pedidos.md) | Grabación pedido / presupuesto |
| [SPEC-101-13](SPEC-101-13-mails.md) | Envío de mails post-grabación |
| [SPEC-101-06](SPEC-101-06-seguridad-visibilidad.md) | Visibilidad cliente / perfiles |
| [CabeceraInicialService](../../../backend/app/Services/PedidosWeb/CabeceraInicialService.php) | Resolución `cod_vended` / nombre desde cliente |

## Decisiones humanas (cerradas en Parte A)

| Tema | Decisión |
|------|----------|
| Tipo inicial | Toda fila importada nace como **Pedido**; el Excel **no** indica tipo |
| Clave de agrupación | `cod_cliente` + `cod_vended` (resuelto) + **firma completa de cabecera** (campos SPEC-101-16). Cabeceras distintas → comprobantes distintos (un mismo cliente puede tener varios pedidos en el lote). Misma cabecera → renglones del mismo comprobante |
| Origen vendedor | **No** viene en plantilla. Al armar/validar cada comprobante se toma el vendedor del **maestro cliente** (`pq_pedidosweb_clientes.cod_vended` + nombre vía relación), igual patrón que razón social / defaults de cabecera |
| Plantilla | Misma estructura de columnas / i18n que `PEDIDO_INDIVIDUAL` (SPEC-101-16 §2); sin columnas nuevas |
| Proceso Excel | Nuevo código de proceso catálogo **`PEDIDO_MASIVO`** reutilizando el mismo set de campos; plantilla descargable equivalente a la individual |
| Parcial archivo | Errores de estructura/parseo/validación de filas Excel del **lote de importación** → **no** se aplica el lote a la grilla (sin parcial de importación). Distinto de la grabación del lote de comprobantes (§8) |
| Colisión al Agregar | Si un grupo tiene la misma clave visual que una fila ya en grilla → **permitir otra fila** de borrador (`idInterno` distinto); no fusionar |
| Grabar lote | Orden de grilla; **N llamadas FE secuenciales** a APIs individuales; progreso «Cargando x de N»; best-effort; OK salen; errores quedan con mensaje |
| Resumen post-grabar | Toast/mensaje con cantidades OK / Error |
| Columna error | Columna fija de mensaje de error en grilla tras fallo de grabación |
| Modal salida → Grabar todo | Abandonar el proceso **solo** si el 100 % de filas grabó OK; con errores parciales permanece en el proceso |
| Eliminar fila | Solo borrador en memoria + **siempre** confirmación modal |
| Consultar | Pantalla tradicional de carga en **solo lectura total** (sin editar ni Grabar) + vuelta a la grilla masiva sin perder borrador de sesión |
| Persistencia borrador | Solo memoria/sesión del navegador; sin borrador en servidor |
| Menú / permiso | Proceso **`pw_importacionmasiva`**; etiqueta i18n ES de referencia: «Importación masiva» |
| Columnas cliente/vendedor | Código + razón social / código + descripción |
| Validaciones | Importación: estructurales + de fila tipo Excel individual (defaults, catálogos, permisos `Modifica*`, nivel extremo, etc.). Grabación: mismas reglas que grabar pedido/presupuesto individual |
| Límite lote | Sin límite de producto en MVP (aplican límites técnicos del motor Excel si existen) |
| Plataforma | **Solo web**; excluido de mobile Capacitor |
| Perfil cliente (C) | `cod_cliente` en Excel **debe coincidir** con el cliente de sesión; divergencia → error de importación (no entra a grilla vía fallo de lote) |
| Perfiles V/S | Visibilidad de cartera según SPEC-101-06; cada `cod_cliente` del archivo debe ser visible para el usuario |

## Decisiones cerradas (ambigüedades menores)

| ID | Tema | Decisión |
|----|------|----------|
| AMB-M-101-21-01 | i18n columnas Excel | Reutilizar literales `excelImport.column.PEDIDO_INDIVIDUAL.*` (y comentarios asociados); el proceso `PEDIDO_MASIVO` no duplica traducciones |
| AMB-M-101-21-02 | Host Consultar | Reutilizar `/pedidos/carga` con `mode=readonly` (+ origen `from=importacionMasiva` o equivalente) y acción **Volver** a la grilla masiva |
| AMB-M-101-21-03 | Grabación del lote | **N llamadas secuenciales desde el frontend** a las APIs de grabación individuales (pedido/presupuesto). Sin endpoint de lote síncrono en MVP. UI muestra progreso i18n tipo **«Cargando x de N»** (o «Grabando x de N») durante el ciclo; al terminar, toast resumen OK/Error |
| AMB-M-101-21-04 | Cabeceras distintas en el lote | **No** es error: cada combinación distinta de campos de cabecera (firma completa) genera un **comprobante distinto**. Solo se fusionan renglones cuando cliente + vendedor + firma de cabecera coinciden |
| AMB-M-101-21-05 | Orden en grilla | Orden de **primera aparición** del grupo en el archivo Excel |

## Decisiones cerradas en A1 (2026-07-19)

| ID | Tema | Decisión |
|----|------|----------|
| AMB-C-101-21-01 | Hidratar Consultar | Cabecera+renglones del borrador vía **estado de navegación** (location state / store de sesión de pantalla); Volver restaura la grilla masiva |
| AMB-C-101-21-02 | Coherencia dentro del grupo | Comparar **valores crudos del Excel** (antes de defaults); vacío = vacío |
| AMB-C-101-21-03 | Permisos Grabar | **`pw_importacionmasiva`** alcanza para importar y grabar pedidos y presupuestos del lote |
| AMB-M-101-21-06 | Cancelar mid-grab | No cancelable en MVP |
| AMB-M-101-21-07 | Grabar vacío | Botón deshabilitado |
| AMB-M-101-21-08 | Orden al Agregar | Conservar filas existentes; anexar nuevos grupos al final |
| AMB-M-101-21-09 | Cliente sin vendedor | Error de importación (sin parcial) |
| AMB-M-101-21-10 | `EXCEL_IMPORT_ENABLED` | Aplica; si false, ocultar/deshabilitar import y plantilla |
| AMB-M-101-21-11 | Texto Error | Mensaje de negocio del envelope / i18n resuelto |
| AMB-M-101-21-12 | Agrupación | Backend handler `PEDIDO_MASIVO` valida + resuelve + agrupa; FE consume comprobantes armados |
| AMB-M-101-21-13 | Progreso x/N | N al iniciar Grabar; x avanza en cada intento (OK o error) |

## Alcance (in scope)

### 1. Menú, permiso y plataforma

| Aspecto | Regla |
|---------|-------|
| Procedimiento | `pw_importacionmasiva` |
| Menú | Nueva opción web; i18n `menu.pw_importacionmasiva` (o clave acordada en TR); no compartir permiso con `pw_cargapedidos` |
| Mobile | No listar / no habilitar en `pedidosWebMobilePolicy`; exclusión alineada a importación Excel |

### 2. Proceso Excel `PEDIDO_MASIVO`

| Atributo | Valor |
|----------|-------|
| `codigo_proceso` | `PEDIDO_MASIVO` |
| Columnas | Idénticas a SPEC-101-16 §2 (mismos `nombre_campo_interno`); i18n **reutilizado** `excelImport.column.PEDIDO_INDIVIDUAL.*` (AMB-01); **sin** columna vendedor |
| `genera_plantilla` | `true` |
| `permite_procesamiento_parcial` | **`false`** para el **archivo** (si hay error de fila/lote de importación → no entregar grupos a la grilla) |
| `procedimiento_host` | `pw_importacionmasiva` |
| Diferencia vs individual | **Sí** admite múltiples combinaciones de cabecera / varios `cod_cliente` en el mismo archivo (agrupación §3) |

Validaciones de importación (no exhaustivo; hereda 101-16 donde aplique):

| Regla | Detalle |
|-------|---------|
| Estructura / i18n | Parser multilenguaje; obligatorias `cod_cliente`, `cod_articulo`, `cantidad` |
| Perfil C | Todo `cod_cliente` del archivo = cliente de sesión |
| Perfil V/S | Cada `cod_cliente` en cartera visible |
| Coherencia **dentro del grupo** | El grupo se define por cliente + vendedor + firma completa de cabecera. Filas con cabecera distinta **no** se mezclan: forman otro comprobante. Dentro del mismo grupo, la cabecera es idéntica por construcción de la clave |
| Nivel | Rango de negocio 0–100; si `NivelExtremo` → solo `0`/`100` |
| Permisos `Modifica*` | Columnas no editables por el usuario deben venir vacías (igual 101-16) |
| Artículo / cantidad / precio cero / cliente inhabilitado | Igual criterios 101-16 |

### 3. Agrupación y armado de borrador

1. Parsear y validar el archivo completo (sin parcial).
2. Resolver defaults por fila (mismo espíritu que `PedidoIndividual` + `CabeceraInicialService`).
3. Asignar **vendedor del cliente**: `cod_vended` y nombre desde maestro cliente (join vendedor).
4. Agrupar filas por clave `(cod_cliente, cod_vended, firmaCabeceraCompleta)` (valores crudos normalizados). Cabeceras distintas → grupos distintos (varios pedidos del mismo cliente permitidos).
5. Por cada grupo: un comprobante borrador con cabecera (primera fila / valores resueltos coherentes) + renglones del grupo.
6. Ordenar filas de grilla por **primera aparición** del grupo en el Excel (AMB-05).
7. Calcular totales sin IVA / con IVA con las **mismas funciones** que carga / import individual (`renglonesCarga` / servicios 101-04).
8. Tipo `esPedido = true` para todas las filas nuevas.
9. Identificador interno de borrador único por fila de grilla (`idInterno`), independiente de la clave visual.

### 4. Pantalla — grilla de trabajo

Estado inicial: grilla **vacía**.

| Columna / control | Regla |
|-------------------|--------|
| Toggle tipo | 1ª columna; `true` = Pedido, `false` = Presupuesto; editable; iniciales Pedido |
| Cliente | `codCliente` + razón social |
| Vendedor | `codVended` + descripción (resueltos) |
| Nivel | Solo lectura en grilla |
| Total sin IVA / Total con IVA | Calculados |
| Error | Columna fija; vacía hasta fallo de grabación; texto i18n o mensaje de negocio |
| Acciones | Iconos Consultar / Eliminar |

Toolbar:

| Acción | Comportamiento |
|--------|----------------|
| Descargar plantilla | Plantilla `PEDIDO_MASIVO` (equivalente columnas a individual) |
| Importar Excel | §5 |
| Marcar todos Pedidos | Toggle de **todas** las filas → Pedido |
| Marcar todos Presupuestos | Toggle de **todas** las filas → Presupuesto |
| Grabar | §6 |

UI: DevExtreme (`DataGrid`, toggle, `Button`, `Popup`). i18n obligatorio. `data-testid` estables (TR).

### 5. Reimportar con grilla no vacía

Modal obligatorio:

1. **Eliminar existente** e importar (reemplazo total del borrador).
2. **Agregar** (sumar grupos al borrador).
3. **Cancelar**.

Al **Agregar**, si la clave visual ya existe en grilla: **crear fila adicional** con nuevo `idInterno` (permitir duplicado visual; no fusionar renglones). Conservar orden de filas existentes; anexar los nuevos grupos al final (orden de 1ª aparición dentro del archivo nuevo) (AMB-M-08).

### 6. Grabar el lote

| Regla | Detalle |
|-------|---------|
| Orquestación | **Frontend:** N llamadas **secuenciales** a las APIs de grabación individuales ya existentes (pedido o presupuesto según toggle). **No** endpoint de lote síncrono en MVP (AMB-03) |
| Progreso UI | Mensaje/overlay i18n **«Cargando x de N»** (`pedidos.importacionMasiva.grabandoProgreso`). N = filas al iniciar; x avanza en cada intento (OK o error); **no cancelable** en MVP (AMB-M-06/13). Bloquear acciones concurrentes hasta terminar |
| Vacío | Botón Grabar **deshabilitado** si no hay filas (AMB-M-07) |
| Alcance | Todos los comprobantes presentes en la grilla al iniciar Grabar |
| Orden | Orden de aparición en la grilla |
| Por ítem | Pedido o presupuesto según toggle; autorización de lote = `pw_importacionmasiva` (AMB-C-03); mismos servicios backend (incl. mails 101-13) |
| Fallo | Un error **no** inhibe el resto del loop FE |
| OK | Quitar fila de la grilla |
| Error | Dejar fila; completar columna Error (mensaje envelope/i18n); permitir reintento / eliminar / reimportar |
| Resumen | Al finalizar el ciclo: toast/mensaje i18n con cantidades OK / Error |

### 7. Consultar / Eliminar / salida

**Consultar:** reutiliza `/pedidos/carga` con `mode=readonly` y origen desde importación masiva (AMB-02): **solo lectura total**. Hidrata cabecera+renglones del borrador vía **estado de navegación** / store de sesión de pantalla (AMB-C-01); **no** lee ni escribe BD. Botón/acción **Volver** a la grilla masiva conservando el borrador de sesión.

**Eliminar:** confirmación modal; quita solo del borrador en memoria.

**Salida** (cerrar solapa, cambiar menú u equivalente) con borrador no vacío → modal:

| Opción | Efecto |
|--------|--------|
| Cancelar el proceso | Descarta borrador y abandona |
| Grabar todo | Ejecuta §6; si quedan errores → **permanece**; si 100 % OK → puede abandonar |
| Retornar al proceso | Cierra modal; sigue en grilla |

### 8. Entregables verificables

- Catálogo `PEDIDO_MASIVO` + campos (seeder) + handler / orquestación de agrupación.
- Pantalla host `pw_importacionmasiva` + menú + permiso.
- Orquestación FE de grabación secuencial + progreso «x de N» + tests de éxito parcial.
- Guardas de navegación + modales de reimport / salida / eliminar.
- Tests: unit agrupación/clave/vendedor desde cliente; feature import+grabar; Vitest UI toggles/modales; E2E camino feliz web.
- Exclusión mobile documentada y en policy.

## Fuera de alcance

- Mobile / Capacitor.
- Edición de cabecera o renglones desde la grilla masiva o desde Consultar.
- Persistencia de borrador en servidor.
- Columna o indicador de tipo pedido/presupuesto en el Excel.
- Cambiar columnas de la plantilla individual (salvo reutilizarlas en `PEDIDO_MASIVO`).
- Fusionar comprobantes al Agregar cuando hay misma clave.
- Grabación automática al importar (solo al pulsar Grabar / Grabar todo).

## Dependencias

| Dependencia | Motivo |
|-------------|--------|
| SPEC-101-16 / GEN-07 | Plantilla, i18n, patrones de validación Excel |
| SPEC-101-04 / 101-05 | Grabación pedido/presupuesto |
| SPEC-101-13 | Mails |
| SPEC-101-10 | Consultar en solo lectura |
| SPEC-101-06 | Cartera / perfil C |
| SPEC-001-04 | `NivelExtremo`, `Modifica*`, flags Excel |

## Flujo extremo a extremo

```mermaid
sequenceDiagram
  participant U as Usuario
  participant G as GrillaMasiva
  participant X as Excel PEDIDO_MASIVO
  participant API as API import/agrupar
  participant GR as Servicios grabación

  U->>G: Abre pw_importacionmasiva (grilla vacía)
  U->>X: Importar Excel
  X->>API: Validar lote (sin parcial)
  alt Error importación
    API-->>X: Errores; grilla sin cambio
  else OK
    API-->>G: Grupos (cliente+vendedor cliente+nivel) como Pedidos
  end
  U->>G: Ajusta toggles / consulta RO / elimina
  U->>G: Grabar
  Note over G,U: UI: «Cargando x de N»
  loop Cada fila en orden (FE secuencial)
    G->>GR: API grabar pedido o presupuesto (1 request)
    alt OK
      GR-->>G: Quitar fila
    else Error
      GR-->>G: Dejar fila + mensaje Error
    end
  end
  G-->>U: Toast N OK / M error
```

## Criterios de aceptación medibles

- [ ] **CA-01:** Al abrir el proceso con permiso, grilla vacía; sin permiso, proceso no accesible.
- [ ] **CA-02:** Plantilla descargable con mismas columnas conceptuales que individual; sin columna vendedor.
- [ ] **CA-03:** Archivo con ≥2 combinaciones distintas `(cliente, vendedor-resuelto, nivel)` genera ≥2 filas; todas como Pedido.
- [ ] **CA-04:** Vendedor en grilla = `cod_vended` + nombre del maestro del cliente (no del Excel).
- [ ] **CA-05:** Perfil C: Excel con otro `cod_cliente` → importación rechazada (sin parcial a grilla).
- [ ] **CA-06:** Botones marcar todos pedidos / presupuestos + cambio individual del toggle.
- [ ] **CA-07:** Consultar abre carga en solo lectura total; Volver conserva borrador.
- [ ] **CA-08:** Eliminar pide confirmación y solo afecta borrador.
- [ ] **CA-09:** Reimportar con grilla no vacía ofrece reemplazar / agregar / cancelar; al agregar, misma clave → segunda fila (`idInterno` distinto).
- [ ] **CA-10:** Grabar = N llamadas FE secuenciales en orden de grilla; UI muestra «Cargando x de N»; un fallo no detiene el resto; OK salen; errores quedan con columna Error; al final toast resumen.
- [ ] **CA-11:** Grabación exitosa dispara el mismo flujo de mails que grabación individual (101-13).
- [ ] **CA-12:** Salida con borrador → modal cancelar / grabar todo / retornar; grabar todo solo cierra proceso si 100 % OK.
- [ ] **CA-13:** Borrador no aparece tras logout / nueva sesión (solo memoria).
- [ ] **CA-14:** Mobile: proceso no disponible.

## Definición de listo (Partes posteriores)

- [x] A1 cerrado (ambigüedad)
- [x] HU Parte B + B1 cerrados (043–045)
- [x] TR Parte C + C1 cerrados (21a–21c) — autoriza D1
- [ ] Menú/permiso seed + i18n 5 locales + `data-testid`
- [ ] Tests unit/feature/E2E del slice
- [ ] Manual usuario (Parte Q) — post implementación

## Ambigüedades menores

Cerradas en Parte A (01–05) y A1 (06–13) — ver §§ Decisiones cerradas.

## Revisión A1 — cierre (2026-07-19)

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Acta** | [F-101-21-cierre-a1-importacion-masiva](../../04-tareas/101-PedidosWeb/F-101-21-cierre-a1-importacion-masiva.md) |

Observaciones no bloqueantes: detalle de store de borrador en FE, contrato exacto del payload del handler `PEDIDO_MASIVO`, `data-testid` e i18n de progreso — a fijar en TR.

### Veredicto

**Apto con observaciones.** Autoriza **Parte B** (HU).

## Parte B — generación HU (2026-07-19)

| HU | Foco | Orden C sugerido |
|----|------|------------------|
| [HU-101-043](../../03-historias-usuario/101-PedidosWeb/HU-101-043-proceso-excel-pedido-masivo.md) | Catálogo/handler `PEDIDO_MASIVO` + agrupación | 1 |
| [HU-101-044](../../03-historias-usuario/101-PedidosWeb/HU-101-044-pantalla-importacion-masiva.md) | Menú, grilla, import UI, grabación FE, modales | 2 |
| [HU-101-045](../../03-historias-usuario/101-PedidosWeb/HU-101-045-consultar-borrador-importacion-masiva.md) | Consultar readonly hidratado | 3 |

**Estado:** Generadas + **B1 cerrado** — [F-101-21-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-21-cierre-b1-importacion-masiva.md).

## Parte C — generación TR (2026-07-19)

| TR | HU | Foco |
|----|----|------|
| [21a](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-proceso-excel-pedido-masivo.md) | 043 | Catálogo/handler `PEDIDO_MASIVO` |
| [21b](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-pantalla-importacion-masiva.md) | 044 | Pantalla + grabación FE |
| [21c](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-consultar-borrador-importacion-masiva.md) | 045 | Consultar readonly |

**Acta C:** [F-101-21-cierre-c](../../04-tareas/101-PedidosWeb/F-101-21-cierre-c-importacion-masiva.md).

## Parte C1 — cierre (2026-07-19)

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto** |
| **Puede pasar a D1** | **Sí** |
| **Acta** | [F-101-21-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-21-cierre-c1-importacion-masiva.md) |

Decisiones C1: `grupos[]`; store OR permiso; sessionStorage al Consultar. Orden D1: **21a → 21b → 21c**.

## Historial

| Fecha | Resumen |
|-------|---------|
| 2026-07-19 | Parte A — SPEC inicial desde producto + decisiones humanas de chat |
| 2026-07-19 | Cierre AMB-M-101-21-01…05 (i18n individual, Consultar readonly, grabación FE secuencial + progreso x/N, coherencia cabecera grupo, orden 1ª aparición) |
| 2026-07-19 | **A1 cerrado** — Apto con observaciones; AMB-C-01…03 + AMB-M-06…13; autoriza Parte B |
| 2026-07-19 | **Parte B** — HU-101-043 / 044 / 045 generadas |
| 2026-07-19 | **B1 cerrado** — 3 HU enriquecidas; autoriza Parte C |
| 2026-07-19 | **Parte C** — TR 21a/21b/21c generadas |
| 2026-07-19 | **C1 cerrado** — Apto; autoriza D1 |
