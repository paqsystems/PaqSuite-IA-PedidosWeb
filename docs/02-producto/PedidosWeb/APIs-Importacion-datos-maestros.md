# APIs de importación de datos maestros (ERP → PedidosWeb)

Jira: PIAPEDWEB-34

| Campo | Valor |
|-------|--------|
| **Estado** | **Finalizado** (SPEC-101-22, Parte I 2026-10-29) |
| **OpenSpec** | [SPEC-101-22-importacion-maestros-erp.md](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Ámbito** | PedidosWeb — **backend de integración** (sin pantalla de operador en el portal) |
| **Origen de producto** | [Definición conceptual](./PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md) §6.1 · [Modelo de datos](./PedidosWeb_Modelo_Datos_Final.md) §3 y §6 |
| **Hermano** | [APIs-Importacion-transacciones.md](./APIs-Importacion-transacciones.md) — deuda, cheques, historial de ventas, stock (SPEC propio) |
| **No confundir con** | Importación **Excel de pedidos** ([individual](./Importación%20Pedido%20Individual%20desde%20Excel.md), [masiva](./importacion-masiva-pedidos.md)) — GEN-14 |
| **Observabilidad** | [Logs de integración](../../05-open-spec/101-PedidosWeb/SPEC-101-08-logs-integracion.md) (`pq_pedidosweb_logs_integracion`) |
| **Framework** | GEN-20 envelope · GEN-19 tenancy · **GEN-36** recepción async (202 + GET) en host · GEN-13 (orquestador ERP) · negocio por **SP** (regla 74) |
| **Jira** | [PIAPEDWEB-34](https://paqsystems.atlassian.net/browse/PIAPEDWEB-34) |

Este archivo es la **fuente del contrato transversal** (auth, envelope, lote) que reutiliza el documento de transacciones. El SPEC de maestros **no** implementa deuda/cheques/historial/stock.

---

## 1. Objetivo

Permitir que el **ERP** (Tango Gestión u otro origen autorizado) **empuje** hacia la base del tenant PedidosWeb los **maestros comerciales** (clientes, artículos, listas, escalas, etc.) y, si aplica, **parámetros** de negocio originados en el ERP.

El portal **no** administra esos catálogos por UI y **no** consulta SQL del ERP.

Hoy el portal opera sobre tablas `pq_pedidosweb_*` ya pobladas (bootstrap/dev o procesos externos no documentados). Falta un **contrato HTTP estable, autenticado y por tenant** para actualizarlas de forma repetible, auditable e idempotente.

---

## 2. Modelo mental

```text
ERP / agente / job (lado Tango o PaqSuite ERP)
        │  POST lote (JSON)
        │  headers: Authorization + X-Paq-Cliente
        ▼
API PedidosWeb  /api/v1/integracion/maestros/{recurso}
        │  valida auth integración + tenant
        │  llama SP upsert / replace-lote
        ▼
Tablas pq_pedidosweb_*  (+ log en pq_pedidosweb_logs_integracion)
        │
        ▼
UI / APIs de consulta y carga (sin cambio de contrato para el usuario)
```

- **Push, no pull:** PedidosWeb **no** abre la base del ERP.
- **Lote por recurso:** un request = un maestro (p. ej. `clientes`). No mezclar recursos ni incluir transacciones.
- **Frecuencia:** periódica (objetivo inicial: **1 vez al día**, configurable en el **orquestador del ERP** / GEN-13 allí). El portal **acepta** también disparo manual del mismo contrato.
- **Novedades:** el origen puede enviar **delta** (solo cambios) o **snapshot** (universo completo del recurso). El modo se declara en el request; el default es **delta upsert**.
- Tras un ciclo de maestros, el origen puede llamar las APIs de [transacciones](./APIs-Importacion-transacciones.md) (otro SPEC).

---

## 3. Alcance

### 3.1 Incluye

1. Familia `POST /api/v1/integracion/maestros/{recurso}` — catálogo §5.
2. Autenticación de **cuenta de integración** (no el login de vendedor/cliente del portal).
3. Upsert por **clave natural** de cada tabla.
4. Modo **delta** y modo **snapshot** según política §6.
5. Respuesta eventual: recepción `202` y consulta `GET` del resultado final, con detalle completo de errores.
6. Registro en `pq_pedidosweb_logs_integracion`.
7. OpenAPI (tag **Integración maestros**) y tests de contrato.
8. Orden de carga entre maestros (§7).

### 3.2 Fuera de alcance

| Fuera | Motivo |
|-------|--------|
| Deuda, cheques, historial de ventas, stock | Documento y SPEC hermanos: [APIs-Importacion-transacciones.md](./APIs-Importacion-transacciones.md) |
| UI de ABM de maestros en PedidosWeb | El operador sigue usando el ERP |
| Importación Excel de **pedidos** | SPEC-101-16 / 101-21 y GEN-14 |
| Lectura SQL directa al ERP desde Laravel | El origen llama a PedidosWeb |
| Descarga **portal → ERP** de pedidos/presupuestos | §6.2 de la definición conceptual |
| Alta/edición de usuarios, roles y menú | GEN-06 |
| Mobile / Capacitor | APIs servidor a servidor |
| Motor de cron **dentro** de PedidosWeb | GEN-13 en el orquestador ERP |
| Reemplazo de `GET /api/v1/clientes` u otras APIs de consulta | Visibilidad de usuario autenticado |

---

## 4. Contrato transversal (MUST) — SoT para maestros y transacciones

El SPEC de [transacciones](./APIs-Importacion-transacciones.md) **reutiliza este §4** (auth, envelope, forma del lote). No redefinir un segundo envelope.

### 4.1 Transporte y tenancy

| Pieza | Regla |
|-------|--------|
| Prefijo (este SPEC) | `/api/v1/integracion/maestros/{recurso}` |
| Prefijo (SPEC hermano) | `/api/v1/integracion/transacciones/{recurso}` |
| Método | `POST` (lote; upsert). Baja de una clave: flag en el ítem o recurso dedicado — no borrar por query string masiva. |
| Header tenant | `X-Paq-Cliente` obligatorio; resuelve `EMPRESAS_CONEXION` (GEN-19). Sin tenant válido → 400/404 según envelope vigente, **sin** escribir. |
| Auth | Bearer de **usuario/token de integración**. Permiso/ability `pw_integracion_importacion` (maestros **y** transacciones: un token, dos prefijos). **Prohibido** reutilizar `pw_carga` / visibilidad de vendedor. |
| Envelope | GEN-20: `{ error, respuesta, resultado }` con `error: 0` en éxito. |
| Content-Type | `application/json`. |
| Tamaño | Tope de ítems por request (SPEC/TR; orden de magnitud **500–2000**). El origen pagina. **Prohibido** `WHERE IN` de PKs de tamaño incierto (regla 77). |
| Idempotencia | Reenviar el mismo lote no duplica filas; upsert por clave natural. Header opcional `Idempotency-Key` a definir en SPEC. |

### 4.2 Cuerpo mínimo del lote

```json
{
  "modo": "delta",
  "fechaProceso": "2026-10-20T00:00:00-03:00",
  "origen": "tango",
  "items": []
}
```

| Campo | Obligatorio | Notas |
|-------|-------------|--------|
| `modo` | Sí | `delta` \| `snapshot` |
| `fechaProceso` | Sí | Carátula del lote; se refleja en `fecha_proceso` / `uma_fecha` cuando existan |
| `origen` | No | Texto corto (`tango`, `manual`, …) para el log |
| `items` | Sí | Array del recurso; vacío = no-op en `delta`. Snapshot con `items: []` **permitido** y representa un universo vacío; ejecuta las bajas previstas por el recurso |

`resultado` típico:

```json
{
  "recurso": "clientes",
  "modo": "delta",
  "recibidos": 10,
  "procesados": 9,
  "agregados": 2,
  "modificados": 6,
  "eliminados": 1,
  "agregadosErroneos": 0,
  "modificadosErroneos": 0,
  "eliminadosErroneos": 0,
  "omitidos": 0,
  "errores": [
    { "indice": 3, "clave": "CL-404", "codigo": "integracion.maestro.itemInvalido" }
  ]
}
```

La suma `agregados + modificados + eliminados + agregadosErroneos + modificadosErroneos + eliminadosErroneos` debe ser igual a `procesados`. `omitidos` no forma parte de esa suma: representa ítems que no generaron una operación, por ejemplo una baja impedida por referencia.

La recepción de cada lote responde HTTP **202** con el identificador del ciclo. El resultado definitivo se consulta mediante:

```text
GET /api/v1/integracion/importaciones/{importacionId}
```

Mientras el ciclo no finaliza, `agregados`, `modificados`, `eliminados`, `agregadosErroneos`, `modificadosErroneos`, `eliminadosErroneos` y `errores` deben devolverse como `null`; no se exponen resultados parciales aunque la tabla técnica se actualice internamente. El estado final devuelve el detalle completo de errores. HTTP **4xx/5xx** + `error ≠ 0` se reserva para rechazo de recepción (auth, JSON inválido, tenant, concurrencia o ciclo inválido).

Mensajes de `respuesta` y `codigo` de error: **claves i18n**.

### 4.3 Persistencia

- Escritura de negocio **MUST** por **stored procedures** (regla 74): familia `pq_sp_integracion_maestros_*`.
- Gateway/agente: misma firma lógica.
- Transacción: recomendación de producto **por ítem** en delta; snapshot de catálogo chico puede ser por lote (SPEC).
- No `TRUNCATE` / `DROP` desde la API.

---

## 5. Catálogo de recursos — maestros (§3 modelo)

Claves = PK documentada. Campos = modelo vigente; el SPEC listará JSON camelCase 1:1.

| Recurso URL | Tabla | Clave natural | Notas |
|-------------|-------|---------------|--------|
| `vendedores` | `pq_pedidosweb_vendedores` | `cod_vended` | Cargar **antes** que clientes. No crear `users` ni `cod_login`. |
| `provincias` | `pq_pedidosweb_provincias` | `cod_provin` | Antes de direcciones. |
| `condventa` | `pq_pedidosweb_condventa` | `codigo` | |
| `transportes` | `pq_pedidosweb_transportes` | `codigo` | |
| `perfiles` | `pq_pedidosweb_perfil` | `cod_perfil` | El parámetro default de pedidos **no** se pisa salvo recurso `parametros`. |
| `listaprecios` | `pq_pedidosweb_listaprecios` + `pq_pedidosweb_listaprecios_articulos` | `cod_lista` y `cod_articulo` en hijos | Una lista contiene `articulos[]`; cada hijo tiene `codArticulo` y `precio`. Reemplaza a los dos endpoints anteriores. |
| `escalas` | `pq_pedidosweb_escalas_cabecera` + detalle | `cod_escala` (+ `cod_valor` en hijos) | Lote anidado: cabecera + `valores[]`. |
| `articulos` | `pq_pedidosweb_articulos` | `codigo` | Tipos canónicos (`usa_esc` char, `equivalencia_ventas`, `stockeable`, `especial`). |
| `descuentocantidad` | `pq_pedidosweb_descuentocantidad` | `(cod_articu, cantidad)` | |
| `clientes` | `pq_pedidosweb_clientes` + `pq_pedidosweb_clientesde` + `pq_pedidosweb_clientescontactos` | `cod_client` y claves de hijos | El cliente contiene `direcciones[]` y `contactos[]`; no se exponen endpoints separados. |
| `parametros` | `PQ_parametros_gral` (o contrato GEN-10 vigente) | código de parámetro | **Whitelist** de claves que el ERP puede pisar; no auth/plataforma. |

---

## 6. Políticas de borrado y snapshot (maestros)

| Recurso | `snapshot` | `delta` |
|---------|------------|---------|
| Catálogo chico (condventa, transportes, perfiles, provincias, escalas) | Puede **desactivar o borrar** códigos ausentes **si no están referenciados**. Si hay FK lógica → omitido, no borrar. | Solo upsert; no borra. |
| Listas de precios | Para una lista ausente, eliminar primero sus precios hijos cuando sea factible. Luego, si la lista está referenciada, inhabilitarla/inactivarla si existe un atributo para ello; si no existe, conservarla. Si no está referenciada, eliminarla. | Upsert de la lista y de sus `articulos[]`; no inferir bajas de precios hijos por ausencia dentro de una lista en esta primera versión. |
| Clientes, artículos, vendedores | **No** borrar por ausencia salvo flag `eliminarSiAusente` **por ítem**. Para clientes, la baja física exige ausencia de referencias en pedidos, deuda, cheques y ventas detalladas; luego elimina contactos, direcciones y cliente en ese orden dentro de una transacción. Inhabilitación solo si el modelo ya tiene el campo. | Upsert. |
| Contactos / direcciones | Snapshot **por cliente** (todos los DE/contactos de los `cod_client` del lote) opcional en SPEC. Default: delta. | Upsert. |
| Parámetros | Solo claves de la whitelist presentes en el lote; no borrar el resto de `PQ_parametros_gral`. | Upsert de claves enviadas. |

Pedidos y presupuestos (`pq_pedidosweb_pedidoscabecera` / `detalle`) **no** se importan.

---

## 7. Orden de carga (maestros)

El origen **debe** respetar este orden. El API **no** reordena. Si falta FK, la fila se rechaza.

1. `condventa` (primera etapa de implementación)
2. `articulos` (segunda etapa)
3. `provincias`, `transportes`, `perfiles`, `vendedores`, `listaprecios` con `articulos[]`, `escalas`, `descuentocantidad`
4. `clientes` con `direcciones[]` y `contactos[]` en el mismo ítem
5. `parametros` (independiente; puede ir primero o último)

Después de este ciclo: [transacciones](./APIs-Importacion-transacciones.md) (stock tras artículos; deuda/cheques/historial tras clientes).

---

## 8. Autorización y seguridad

- Token de integración **por tenant** (o usuario técnico por instalación con el mismo `X-Paq-Cliente`).
- Rate limit a definir en TR.
- No dumps de SQL ni stack en `respuesta`.
- No aceptar SQL, nombres de tabla ni DDL en el JSON.
- `cod_login` de clientes/vendedores: **ignorado**.
- Leyendas cliente: recorte a 60.

---

## 9. Logs y operación

Cada request escribe en `pq_pedidosweb_logs_integracion`:

- tipo `maestro_import`
- recurso, modo, `fechaProceso`, contadores
- severidad `info` / `warning` (parcial) / `error` (lote abortado)

La pantalla **Logs de integración** (`pw_logsintegracion`) consulta esos registros; no hay UI nueva.

---

## 10. Relación con Framework / otros procesos

| Capacidad | Relación |
|-----------|----------|
| Documento transacciones | Mismo §4; otro SPEC; no mezclar alcance D |
| GEN-14 Excel | No usar para maestros |
| GEN-13 Tareas | El ERP programa el push |
| GEN-10 Parámetros | Recurso `parametros`; UI PedidosWeb sigue solo lectura |
| GEN-20 / 19 | Envelope y tenant |
| SPEC-101-08 | Servicio de log |

---

## 11. Criterios de aceptación de producto (futuro SPEC maestros)

1. Un origen autenticado con `X-Paq-Cliente` puede upsert artículos y clientes; un vendedor del portal **no**.
2. Reenviar el mismo lote no duplica PKs.
3. Un ítem inválido no impide aplicar el resto en modo delta.
4. OpenAPI documenta todos los recursos §5.
5. Pedidos existentes no se modifican.
6. Este SPEC **no** expone `/integracion/transacciones/*`.
7. Mobile y Excel de pedidos no se tocan.

---

## 12. Preguntas abiertas (A1 / SPEC)

**Cerradas en A1** (2026-10-20) — ver [F-101-22-cierre-a1](../../04-tareas/101-PedidosWeb/F-101-22-cierre-a1-importacion-maestros-erp.md) y decisiones en [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md).

Resumen: Sanctum usuario técnico + `pw_integracion_importacion`; sin campo inhabilitado nuevo; un endpoint por recurso; semántica delta/snapshot unificada; whitelist parámetros = seed PedidosWeb; sin `Idempotency-Key` en MVP.

La actualización 22d agrega estas decisiones:

- El endpoint de listas de precios es único: `POST /api/v1/integracion/importaciones/listaprecios`; `listaprecios-articulos` deja de exponerse.
- El identificador `importacionId` lo genera el ERP. Se usa `ultimoEnvio`; no se agrega un endpoint de finalización.
- Se acepta `202 Accepted` y procesamiento eventual. La consulta es `GET /api/v1/integracion/importaciones/{importacionId}`.
- Se permite snapshot vacío. En ese caso el universo recibido es vacío y se ejecutan las bajas según las reglas de referencias.
- La concurrencia por tenant y recurso se evita: si hay otro ciclo en proceso para el mismo recurso, la nueva recepción se rechaza y el ERP debe detener los lotes siguientes y registrar/avisar el error.
- El límite inicial recomendado es **10 MiB por request** y hasta **1500 ítems de primer nivel**. El ERP debe partir por tamaño y cantidad; si la medición real lo exige, se ajustará sin cambiar la semántica.
- Se conserva el JSON original de cada lote para auditoría y reintentos. Los errores se devuelven completos inicialmente; se incorporará paginación o descarga separada si la medición demuestra que el tamaño de la respuesta es impracticable.

### 12.1. Etapas de implementación

| Etapa | Alcance |
|---|---|
| A | Probar `condventa`, una sola pasada, con recepción `202`, consulta final, contadores y errores |
| B | Probar `articulos`, incluyendo snapshot y sus reglas de baja |
| C | Una vez aprobadas A y B, implementar el resto de maestros en conjunto |
| D | Consolidar `clientes` con `direcciones[]` y `contactos[]` como nodos hijos; eliminar los endpoints separados de direcciones/contactos |

El desarrollo de cada etapa debe actualizar OpenAPI, SP, tests y colección Postman antes de avanzar a la siguiente.

---

## 13. Próximo paso SDD

1. **A0** — hecho: [PIAPEDWEB-34](https://paqsystems.atlassian.net/browse/PIAPEDWEB-34).
2. **A** — hecho: [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) (2026-10-20).
3. **A1** — hecho: [F-101-22-cierre-a1](../../04-tareas/101-PedidosWeb/F-101-22-cierre-a1-importacion-maestros-erp.md).
4. **B / B1** — hecho: HU-101-046…048 — [F-101-22-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-22-cierre-b1-importacion-maestros-erp.md).
5. **C** — hecho: TR [22a](../../04-tareas/101-PedidosWeb/TR-SPEC-101-22-integracion-maestros-plataforma.md) · [22b](../../04-tareas/101-PedidosWeb/TR-SPEC-101-22-integracion-maestros-catalogo-articulos.md) · [22c](../../04-tareas/101-PedidosWeb/TR-SPEC-101-22-integracion-maestros-clientes-parametros.md).
6. **C1 → D1** — revisión TR e implementación (orden 22a → 22b → 22c).
7. **Transacciones** — Parte A hecha: [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) (Epic [PIAPEDWEB-35](https://paqsystems.atlassian.net/browse/PIAPEDWEB-35)); siguiente **A1**.

No implementar código hasta SPEC + HU + TR.
