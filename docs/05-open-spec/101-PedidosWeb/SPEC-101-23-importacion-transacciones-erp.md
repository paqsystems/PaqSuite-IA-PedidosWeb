# SPEC-101-23 — APIs importación transacciones (ERP → PedidosWeb)

Jira: PIAPEDWEB-35

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) (extensión post-MVP integración ERP) |
| **Producto** | [APIs-Importacion-transacciones.md](../../02-producto/PedidosWeb/APIs-Importacion-transacciones.md) |
| **Hermano (otro SPEC)** | [SPEC-101-22-importacion-maestros-erp.md](SPEC-101-22-importacion-maestros-erp.md) — contrato común producto **§4**; **no** mezclar alcance |
| **Estado** | **D1 listo para Parte D** — precedencia de fechas cerrada |
| **Prioridad épica** | **Must** (alimenta consultas stock/deuda/cheques/historial) |
| **Revisión A1** | [F-101-23-cierre-a1-importacion-transacciones-erp](../../04-tareas/101-PedidosWeb/F-101-23-cierre-a1-importacion-transacciones-erp.md) (2026-10-21) |
| **Última actualización** | 2026-10-22 (Parte D1) |
| **Ámbito** | Backend HTTP servidor-a-servidor; **sin** pantalla de operador |
| **HU relacionadas** | [HU-101-049](../../03-historias-usuario/101-PedidosWeb/HU-101-049-integracion-transacciones-plataforma.md), [HU-101-050](../../03-historias-usuario/101-PedidosWeb/HU-101-050-integracion-transacciones-saldos.md), [HU-101-051](../../03-historias-usuario/101-PedidosWeb/HU-101-051-integracion-transacciones-historial-ventas.md) |
| **TR relacionadas** | [TR-23a](../../04-tareas/101-PedidosWeb/TR-SPEC-101-23-integracion-transacciones-plataforma.md), [TR-23b](../../04-tareas/101-PedidosWeb/TR-SPEC-101-23-integracion-transacciones-saldos.md), [TR-23c](../../04-tareas/101-PedidosWeb/TR-SPEC-101-23-integracion-transacciones-historial-ventas.md) |
| **Dependencia de implementación** | Token `pw_integracion_importacion` y contrato lote de **101-22**. Las rutas 23 pueden implementarse **en paralelo** (tests con maestros ya en fixture); runtime de producción exige artículos/clientes cargados |

## Objetivo

Exponer `POST /api/v1/integracion/transacciones/{recurso}` para que el ERP **empuje** las **cuatro tablas de transacciones / saldos** que leen las consultas del portal:

| Recurso | Tabla | Consulta portal |
|---------|-------|-----------------|
| `stock` | `pq_pedidosweb_stock` | [consulta-stock](../../02-producto/PedidosWeb/consulta-stock.md) |
| `deuda` | `pq_pedidosweb_deuda` | [consulta-deuda](../../02-producto/PedidosWeb/consulta-deuda.md) |
| `cheques` | `pq_pedidosweb_cheques` | [consulta-cheques](../../02-producto/PedidosWeb/consulta-cheques.md) |
| `historial-ventas` | `pq_pedidosweb_ventadetallada` | [consulta-historial-ventas](../../02-producto/PedidosWeb/consulta-historial-ventas.md) |

El portal **solo lee** esas tablas en UI. Este SPEC **no** cubre maestros ni parámetros.

**Adopción Framework:** reutiliza GEN-19 / GEN-20 y el permiso `pw_integracion_importacion` de [SPEC-101-22](SPEC-101-22-importacion-maestros-erp.md). Persistencia por **SP** (regla 74): familia `pq_sp_integracion_transacciones_*`. **No** GEN-14. **No** cron en PedidosWeb.

## Fuentes

| Fuente | Rol |
|--------|-----|
| [APIs-Importacion-transacciones.md](../../02-producto/PedidosWeb/APIs-Importacion-transacciones.md) | Definición de producto |
| [APIs-Importacion-datos-maestros.md](../../02-producto/PedidosWeb/APIs-Importacion-datos-maestros.md) **§4** | SoT auth, envelope, forma del lote |
| [SPEC-101-22](SPEC-101-22-importacion-maestros-erp.md) | Decisiones A1 de plataforma (tope 1500, errores 50, 401/403, sin Idempotency-Key MVP) |
| Modelo §4 | Claves y columnas |
| SPEC-101-07 / 101-08 | Consultas GET existentes; logs |
| SPEC-101-02 | Tablas; no redefinir DDL salvo gap documentado en TR |

## Decisiones humanas (cerradas en Parte A)

| Tema | Decisión |
|------|----------|
| Auth | **Mismo** token Sanctum + `pw_integracion_importacion` que 101-22. Vendedor portal **403** |
| Contrato lote | **Idéntico** a maestros §4 en forma JSON. Envelope GEN-20. Éxito parcial HTTP **200** `error: 0` **solo en `delta`** (A1-C02). Snapshot atómico: aborto **422** |
| Un endpoint por recurso | Path `{recurso}` = `stock` \| `deuda` \| `cheques` \| `historial-ventas` |
| Modo stock / deuda / cheques | Habitual **`snapshot`**. `delta` permitido (upsert de claves enviadas; **no** recomendado en prod). `items: []` + snapshot → **422** |
| Modo historial | Solo **`delta`**. `modo=snapshot` → **422** + `integracion.transaccion.modoNoSoportado`. No borrar filas fuera del lote |
| Snapshot SQL | **Una transacción por request** de recurso snapshot (stock/deuda/cheques): replace-conjunto vía SP, **sin** `TRUNCATE`/`DROP` desde PHP |
| `resumen-cuenta` | **Fuera de MVP** (update futuro si hay pantalla) |
| Tope ítems | **1500** por request (igual 101-22). Historial pagina por ventana del origen |
| `Idempotency-Key` | **Fuera de MVP** (igual 101-22) |
| Visibilidad usuario | Solo en **GET** de consulta; el import no filtra cartera |
| Pedidos portal | **No** se importan ni se pisan |
| Mobile / Excel | **Sin cambios** |
| Recurso desconocido | HTTP **404** |
| Sin Bearer / sin ability | **401** / **403** |

## Decisiones cerradas en A1 (2026-10-21)

| ID | Tema | Decisión |
|----|------|----------|
| AMB-C-101-23-01 | Snapshot atómico | Un ítem inválido o clave duplicada en snapshot stock/deuda/cheques → **no escribe**. HTTP **422**, `error ≠ 0`, log `error` |
| AMB-C-101-23-02 | 200 parcial | Solo `delta`. Snapshot nunca 200 si el universo no se aplicó |
| AMB-C-101-23-03 | Delta saldos | Merge upsert (columnas omitidas no pisan); no borra ausentes |
| AMB-C-101-23-04 | Duplicados en `items[]` | Snapshot: aborta lote. Delta: primero aplica; siguientes misma clave `rechazados` |
| AMB-C-101-23-05 | JSON extra | `comprometidoWeb` / `*Base` / `disponibleNeto*` → ítem rechazado. Otras claves desconocidas **ignoradas** |

### Decisiones menores A1

| ID | Tema | Decisión |
|----|------|----------|
| AMB-M-101-23-01 | Fechas proceso | `fechaProceso` de carátula en todas las filas aplicadas (`uma_fecha` / `fecha_proceso`) |
| AMB-M-101-23-02 | Historial MUST | `idGva53`, `codCliente`, `tipo`, `numero`, `fechaEmision` |
| AMB-M-101-23-03 | Negativos | Permitidos (ERP) |
| AMB-M-101-23-04 | `omitidos` | Snapshot OK = 0; duplicados delta = `rechazados` |
| AMB-M-101-23-05 | Path desconocido | 404 `integracion.transaccion.recursoDesconocido` |
| AMB-M-101-23-06 | Log | `info` / `warning` (delta parcial) / `error` (aborto) |
| AMB-M-101-23-07 | SP | Uno por recurso (nombres § Dependencias) |
| AMB-M-101-23-08 | Cheques clave | `(interno, numero)` |
| AMB-M-101-23-09 | Orden vs 22 D | HU 23 no bloqueadas; tests con fixtures |
| AMB-M-101-23-10 | `items: []` delta | No-op 200 |

### Decisión D1 cerrada (2026-10-22)

En `historial-ventas`, `fechaEmision` representa la fecha del comprobante. La
`fechaProceso` de la carátula representa el momento del proceso y prevalece para
todas las filas aplicadas; se persiste en `fecha_proceso`. Una fecha de proceso
por fila no reemplaza la fecha de carátula.

## Alcance (in scope)

### 1. Rutas

Prefijo: `POST /api/v1/integracion/transacciones/{recurso}`

| `{recurso}` | Tabla | Clave natural | Modo MVP |
|-------------|-------|---------------|----------|
| `stock` | `pq_pedidosweb_stock` | `cod_articulo` | snapshot (delta opcional) |
| `deuda` | `pq_pedidosweb_deuda` | `(cod_cliente, t_comp, n_comp, fecha_vto)` | snapshot (delta opcional) |
| `cheques` | `pq_pedidosweb_cheques` | `(interno, numero)` | snapshot (delta opcional) |
| `historial-ventas` | `pq_pedidosweb_ventadetallada` | `id_gva53` | **solo delta** |

### 2. Contrato transversal

**No redefinir.** Copiar comportamiento de [SPEC-101-22 §2](SPEC-101-22-importacion-maestros-erp.md) y producto maestros §4:

- `X-Paq-Cliente`, Bearer, JSON, `resultado` con `recibidos` / `aplicados` / `omitidos` / `rechazados` / `errores[]` (tope **50**)
- i18n en `respuesta` y `codigo` (prefijo sugerido `integracion.transaccion.*`)
- Rate limit 60 req/min por token+tenant (misma TR de plataforma que 22 o TR 23-plataforma si se implementa primero 23)

`resultado.recurso` = slug del path.

### 3. Stock

Ítem mínimo: `codArticulo`, `stock`, `comprometido`.

| Regla | Detalle |
|-------|---------|
| FK | `codArticulo` debe existir en `pq_pedidosweb_articulos`; si no → ítem `rechazado` |
| `uma_fecha` | = `fechaProceso` de carátula |
| Prohibido persistir | `comprometidoWeb`, `stockBase`, `comprometidoBase`, `disponibleNeto*` (la consulta los calcula) |
| `stockeable = 0` | Ítem **aceptado** si viene; la consulta stock los excluye |
| Snapshot | El SP deja el conjunto de filas de stock del tenant **igual** al lote (claves no enviadas **salen**). `items: []` rechazado |

### 4. Deuda

Ítem mínimo: `codCliente`, `tComp`, `nComp`, `fechaEmis`, `fechaVto`, `saldo`.

| Regla | Detalle |
|-------|---------|
| FK | Cliente debe existir; si no → `rechazado` |
| Razón social | **No** viaja en el lote; consulta hace join a `clientes.razon_soci` |
| Escritura | **Esquema canónico** (`cod_cliente`, `t_comp`, `n_comp`, `fecha_emis`, `fecha_vto`, `saldo`). No escribir columnas legacy |
| Snapshot | Lote = universo vigente de comprobantes pendientes. `fecha_proceso` de carátula alimenta metadata de consulta |
| Delta | Upsert de claves enviadas; puede dejar deudas viejas (no recomendado) |

### 5. Cheques

Ítem mínimo: `interno`, `numero`, `codCliente`, `banco`, `fecha`, `importe`, `origen`, `estado`.

| Regla | Detalle |
|-------|---------|
| FK | Cliente debe existir |
| Nombre cliente | Consulta usa `clientes.nombre`; **no** persistir nombre en cheques |
| Columnas BD | Mapear camelCase → `Banco`, `Importe`, `Origen`, `Estado` (y aliases legacy en lectura de consulta, no en escritura) |
| Filtro `fecha >= hoy` | **Solo consulta** (§17.5). El import guarda lo que manda el ERP |
| Snapshot | Universo vigente de cartera/export ERP |

### 6. Historial de ventas

Ítem: MUST A1-M02 `idGva53`, `codCliente`, `tipo`, `numero`, `fechaEmision` + resto de [consulta-historial-ventas §2](../../02-producto/PedidosWeb/consulta-historial-ventas.md) nullable. `fechaProceso` de fila o de carátula → `fecha_proceso`; **no** se expone en GET de ítems.

| Regla | Detalle |
|-------|---------|
| Upsert | Por `id_gva53`; reenvío actualiza, no duplica |
| Snapshot | **422** |
| Borrado | **No** borrar filas ausentes del lote |
| `cod_cli` | Debe existir en clientes; si no → `rechazado` |
| `cod_articu` | **No** exige artículo en maestro (histórico denormalizado) |
| `DiasVentasDetalladas` | Lo aplica el **GET** de consulta, no el import |

### 7. Orden respecto de maestros

Responsabilidad del origen (el API no reordena):

1. Ciclo maestros 101-22
2. `stock` (exige artículos)
3. `deuda`, `cheques`, `historial-ventas` (exigen clientes)

FK faltante → ítem `rechazado`. En **`delta`**: el resto del lote sigue (200 parcial). En **`snapshot`**: lote **atómico** (A1-C01) — nada se aplica, **422**.

### 8. Observabilidad

| Campo lógico | Valor |
|--------------|--------|
| `tipo` | `transaccion_import` |
| `severidad` | `info` / `warning` (solo si se permite parcial; **no** aplica a snapshot atómico OK/KO) / `error` |
| Payload | recurso, modo, `fechaProceso`, contadores |

Pantalla `pw_logsintegracion` sin UI nueva.

### 9. OpenAPI y tests

- Tag **Integración transacciones**; 4 paths.
- Tests: auth, tenant, snapshot vacío 422, snapshot atómico (un ítem malo no pisa tabla), historial snapshot 422, historial upsert `id_gva53`, stock no persiste `comprometidoWeb`, deuda canónica, vendedor 403.

### 10. Seguridad

Igual 101-22: sin SQL/stack en `respuesta`; sin DDL en JSON.

## Fuera de scope

| Fuera | Motivo |
|-------|--------|
| `/integracion/maestros/*` | SPEC-101-22 |
| `resumen-cuenta` | No MUST |
| GET `/consultas/*` | Ya en 101-07 |
| Comprometido web / `*Base` | Consulta stock |
| Pedidos/presupuestos | Circuito portal |
| UI nueva | Consultas y logs existentes |
| Cron PedidosWeb | GEN-13 en ERP |

## Dependencias

| Dependencia | Notas |
|-------------|--------|
| SPEC-101-22 | Auth, envelope, tope, usuario técnico (puede estar implementado o mockeado en tests) |
| SPEC-101-02 | Tablas §4 |
| SPEC-101-07 | Contratos GET no cambian |
| SPEC-101-08 | Logs |
| SP | `pq_sp_integracion_transacciones_stock` / `_deuda` / `_cheques` / `_historial_ventas` (nombres finales en TR) |

## Criterios de aceptación (verificables en D/E)

1. Las cuatro rutas existen; mismo token de integración que maestros; vendedor **no**.
2. Snapshot deuda/cheques/stock sin `TRUNCATE` ad-hoc; atómico; auditado en logs.
3. Reenviar historial con el mismo `idGva53` no duplica.
4. Stock no pisa `comprometidoWeb`.
5. Tras snapshot OK, GET de consulta refleja el lote (visibilidad sigue en GET).
6. OpenAPI cubre las cuatro rutas.
7. Este SPEC **no** expone `/integracion/maestros/*`.

## Preguntas abiertas (post-A1)

Ninguna bloqueante. Detalle OpenAPI campo a campo → Parte C.

## Definición de listo (SPEC)

- [x] Parte A — este documento
- [x] Parte A1 — [F-101-23-cierre-a1](../../04-tareas/101-PedidosWeb/F-101-23-cierre-a1-importacion-transacciones-erp.md) — **apto con observaciones**
- [x] Parte B — HU-101-049…051 (2026-10-22)
- [x] Parte B1 — [F-101-23-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-23-cierre-b1-importacion-transacciones-erp.md) — **apto con observaciones**
- [x] Parte C — [F-101-23-cierre-c](../../04-tareas/101-PedidosWeb/F-101-23-cierre-c-importacion-transacciones-erp.md) — **3 TR generadas; pendiente C1**
- [x] Parte C1 — [F-101-23-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-23-cierre-c1-importacion-transacciones-erp.md) — **apto con observaciones; autoriza D1**
- [x] Parte D1 — [D1-PLAN-101-23](../../04-tareas/101-PedidosWeb/D1-PLAN-101-23-integracion-transacciones-erp.md) — **listo para Parte D**
- [ ] Parte D

## Historial de cambios

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 2026-10-21 | Parte A | SPEC-101-23 desde producto APIs-Importacion-transacciones; alineado a 101-22 |
| 2026-10-21 | Parte A1 | Cierre ambigüedad C01–C05; autoriza Parte B |
| 2026-10-22 | Parte B | HU-101-049 plataforma, HU-101-050 saldos y HU-101-051 historial; Jira PIAPEDWEB-40…42 |
| 2026-10-22 | Parte B1 | Enriquecimiento de HU-101-049…051; autoriza Parte C |
| 2026-10-22 | Parte C | TR-23a plataforma, TR-23b saldos y TR-23c historial; pendiente C1 |
| 2026-10-22 | Parte C1 | Revisión de las 3 TR; apto con observaciones; autoriza D1 |
| 2026-10-22 | Parte D1 | Plan 23a→23b→23c; carátula prevalece en `fecha_proceso` de historial |
