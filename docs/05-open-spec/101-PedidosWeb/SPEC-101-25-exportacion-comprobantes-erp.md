# SPEC-101-25 — API exportación comprobantes (PedidosWeb → ERP)

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) (extensión integración bidireccional) |
| **Producto** | [APIs-Exportacion-comprobantes-erp.md](../../02-producto/PedidosWeb/APIs-Exportacion-comprobantes-erp.md) |
| **Hermano** | [SPEC-101-22](SPEC-101-22-importacion-maestros-erp.md) · [SPEC-101-23](SPEC-101-23-importacion-transacciones-erp.md) (ERP → portal) |
| **Estado** | **Borrador Parte A** (2026-11-18) — pendiente A1 |
| **Prioridad épica** | **Must** (descarga TANGO vía HTTP; sustituye dependencia exclusiva de SQL §6.2) |
| **Ámbito** | Backend HTTP GET servidor-a-servidor; **sin** UI |
| **HU relacionadas** | *Pendiente Parte B* — `HU-101-055` plataforma · `HU-101-056` contrato datos |
| **TR relacionadas** | *Pendiente Parte C* — `TR-SPEC-101-25-integracion-export-plataforma` · `TR-SPEC-101-25-integracion-export-datos` |

## Objetivo

Exponer `GET /api/v1/integracion/comprobantes` para que el **ERP** obtenga comprobantes web (pedidos y presupuestos) en formato **anidado** `cabecera` + `renglones[]`, filtrados por **rangos inclusivos de `estado`**, con **paginación por cabecera**, payload **camelCase**, autenticación de integración y lectura por **SP** (regla 74).

El portal **no** cambia las consultas existentes (`/consultas/*`).

**Adopción Framework:** GEN-19 (tenancy) · GEN-20 (envelope). **No** GEN-36 (no async). **No** reutilizar visibilidad GEN-02 del operador en este endpoint.

## Fuentes

| Fuente | Rol |
|--------|-----|
| [APIs-Exportacion-comprobantes-erp.md](../../02-producto/PedidosWeb/APIs-Exportacion-comprobantes-erp.md) | SoT producto |
| [consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md) | Campos cabecera |
| [consulta-detalle-pedidos.md](../../02-producto/PedidosWeb/consulta-detalle-pedidos.md) | Campos renglón |
| [PedidosWeb_Modelo_Datos_Final.md](../../02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md) §2.1–2.2 | Tablas y estados |
| [SPEC-101-22](SPEC-101-22-importacion-maestros-erp.md) | Auth integración `pw_integracion_importacion` |
| [SPEC-101-07](SPEC-101-07-consultas-api.md) | Paginación y metadata (referencia; sin visibilidad) |

## Decisiones humanas (Parte A — propuesta)

| Tema | Decisión |
|------|----------|
| Ruta canónica | `GET /api/v1/integracion/comprobantes` |
| Método | **GET** síncrono **200** (no 202) |
| Auth | Igual SPEC-101-22: token Sanctum con `pw_integracion_importacion`; header `X-Paq-Cliente` |
| Visibilidad | **Tenant completo**; **no** `VisibleClientsResolver` ni permisos consulta/carga |
| Forma JSON | **Anidado:** cada ítem = `{ cabecera, renglones, cierre? }` |
| Naming JSON | **camelCase** en request query y response |
| Rangos estado | Parámetro **`estadoRangos`** obligatorio; cada par `desde:hasta` **inclusivo**; `desde === hasta` válido (`0:0`) |
| Dominio estado | Cada extremo ∈ **[-1, 99]**; unión **OR** entre pares; deduplicar por `codPedido` |
| Paginación | Por **cabecera** (`total`, `totalPages` sobre comprobantes); renglones completos de cada cabecera del page |
| `pageSize` | Default **20**, máximo **1000** (misma magnitud que consultas) |
| Consultas portal | **Sin cambios**; no agregar `includeDetalle` a `/consultas/*` en este SPEC |
| Persistencia | SP `pq_sp_integracion_export_comprobantes_list` (nombre final en TR) |
| Escritura ERP | **Fuera** de alcance (marcar descargado, estado 1, etc.) |

### Estados de referencia (producto)

| Estado | Significado |
|--------|-------------|
| -1 | En modificación web |
| 0 | Ingresado, pendiente descarga ERP |
| 1 | Pendiente en ERP |
| 2 | Cerrado/cumplido ERP |
| 3 | Facturado (dashboard/i18n) |
| 98 | Presupuesto cerrado |
| 99 | Presupuesto activo |

El filtro admite **cualquier** entero en **[-1, 99]** aunque el catálogo UI no muestre aún ese código.

## Alcance funcional

### In scope

1. Controller + ruta bajo prefijo `integracion` con middleware integración.
2. Validación de query (`estadoRangos`, paginación, filtros opcionales).
3. Servicio/repository que invoca SP y mapea a DTO camelCase.
4. OpenAPI: tag **Integración exportación**; schema `ApiEnvelopeIntegracionComprobantesExport`.
5. Feature tests: auth, rangos, paginación por cabecera, anidado, 422 inválidos.
6. Postman: request de ejemplo en colección integración (TR).
7. Log opcional tipo `export_comprobantes` en `pq_pedidosweb_logs_integracion` (Should; detalle en TR plataforma).

### Out of scope

| Fuera | Motivo |
|-------|--------|
| PATCH/POST confirmación descarga | Ciclo ERP posterior |
| `GET` masivo sin `estadoRangos` | Evitar pull accidental total |
| Modificar `ConsultaListadoService` / consultas UI | Contratos estables |
| Gateway agente con SQL libre | Misma política SP que importación |

## Contrato HTTP

### Request

```http
GET /api/v1/integracion/comprobantes?estadoRangos=0:0&estadoRangos=1:2&estadoRangos=98:99&page=1&pageSize=50
Authorization: Bearer …
X-Paq-Cliente: {tenant}
```

Formato `estadoRangos` (MUST aceptar ambos):

- Repetido: `estadoRangos=0:0&estadoRangos=1:2`
- CSV: `estadoRangos=0:0,1:2,98:99`

| Query | Obl. | Regla |
|-------|------|--------|
| `estadoRangos` | Sí | ≥1 par `desde:hasta`; inclusivo; extremos en [-1, 99]; `desde ≤ hasta` |
| `page` | No | ≥ 1, default 1 |
| `pageSize` | No | 1…1000, default 20. **MUST** aceptar alias `page_size` por paridad consultas |
| `codCliente` | No | Igualdad exacta |
| `codPedido` | No | Igualdad exacta |
| `fechaDesde` | No | Inclusivo sobre `cabecera.fecha` |
| `fechaHasta` | No | Inclusivo sobre `cabecera.fecha` |

Orden estable del listado (MUST): `fecha` cabecera **desc**, luego `codPedido` **asc**.

### Response 200

Envelope GEN-20. `resultado`:

| Campo | Tipo | Notas |
|-------|------|--------|
| `items` | array | Longitud ≤ `pageSize`; un elemento por cabecera |
| `items[].cabecera` | object | Ver §Cabecera |
| `items[].renglones` | array | 0…n; orden `renglon` asc |
| `items[].cierre` | object \| null | Solo si `estado === 98` y hay registro de cierre |
| `page` | int | |
| `pageSize` | int | |
| `total` | int | **Cabeceras** que cumplen filtro |
| `totalPages` | int | `ceil(total / pageSize)` |
| `metadata.fechaProceso` | string ISO | Marca de lectura |

### § Cabecera (camelCase)

**MUST** incluir al menos el superset documentado en [consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md) §2, con estas reglas:

- Clave del GUID: siempre **`codPedido`** (también para presupuestos; no usar solo `codPresupuesto` en integración).
- **MUST** además: `talPedidoTango`, `nroPedidoTango`, `codUsuarioWeb`.
- **SHOULD** si existen en BD: `codPedidoOrigen`, `codPresupuestoOrigen`, `origenComprobante`.
- **Prohibido** en integración: `puedeEditar`, `puedeEliminar`, `puedeCopiar`, `puedeConvertir`, `puedeCerrar`, `guidSufijo`.

Joins de descripción (razón social, vendedor, condición venta, etc.): mismo criterio que `ConsultaListadoService::mapComprobanteItem`.

### § Renglón (camelCase)

Alineado a [consulta-detalle-pedidos.md](../../02-producto/PedidosWeb/consulta-detalle-pedidos.md) §4:

`renglon`, `codArticulo`, `descripcionArticulo`, `especial`, `cantidad`, `cantidadVenta`, `porcBonif`, `precioLista`, `precioNeto`, `importeBruto`, `importeNeto`, `ivaNeto`, `importeNetoConIva`.

**Prohibido** repetir campos de cabecera dentro de cada renglón.

### § Cierre presupuesto

Si `estado === 98`, **SHOULD** incluir `cierre` con el mismo shape que devuelve hoy `ConsultaListadoService` para presupuestos cerrados (`mapPresupuestoCierre`).

## Errores (envelope)

| HTTP | Condición |
|------|-----------|
| 401 | Sin Bearer válido |
| 403 | Sin `pw_integracion_importacion` |
| 400 / 404 | Tenant inválido (misma convención integración) |
| 422 | Sin `estadoRangos`; par mal formado; extremo fuera de [-1, 99]; `desde > hasta`; fechas inválidas |

## Reglas de negocio

1. **RN-01:** Un comprobante que cae en varios rangos aparece **una vez** en la página.
2. **RN-02:** `total` cuenta cabeceras, no renglones.
3. **RN-03:** Cabecera sin detalle se exporta con `renglones: []`.
4. **RN-04:** No aplicar reglas de edición (`estado -1` bloqueo ERP); el ERP recibe el estado actual tal cual en BD.
5. **RN-05:** Lectura **solo** vía SP; prohibido Query Builder ad-hoc en controller (regla 74).

## Criterios de aceptación (SPEC — base para HU)

| ID | Criterio |
|----|----------|
| CA-01 | Token integración + tenant válido → 200 con envelope y `items` anidados |
| CA-02 | `estadoRangos=0:0` solo devuelve cabeceras con `estado === 0` |
| CA-03 | `estadoRangos=-1:-1,0:0,98:99` unión OR correcta sin duplicados |
| CA-04 | `page` / `pageSize` paginan por cabecera; renglones completos por ítem |
| CA-05 | Extremos 99:99 y -1:-1 válidos si hay datos |
| CA-06 | Falta `estadoRangos` → 422 |
| CA-07 | `1:0` → 422 |
| CA-08 | Usuario portal con consulta **no** sustituye auth integración (403 sin ability) |
| CA-09 | OpenAPI documenta query, response y ejemplos camelCase |

## Plan de TR (orientativo Parte C)

| TR | Contenido |
|----|-----------|
| **25a plataforma** | Ruta, middleware, validación request, controller, OpenAPI, tests auth/422, rate limit alineado integración |
| **25b datos** | SP listado + mapeo cabecera/renglones, tests datos con fixture multi-estado |

## Definición de listo (épica)

- [ ] A1 apto
- [ ] HU + TR generadas y C1 apto
- [ ] SP desplegado en pipeline install/update
- [ ] OpenAPI + entrada Postman integración
- [ ] Feature tests CA-01…CA-09
- [ ] Documentación producto al día
- [ ] Smoke: TANGO (o job ERP) lee página `0:0` y recibe cabecera+renglones

## Plantilla SDD (invocación)

```text
[Proceso] exportación comprobantes ERP: adoptar GEN-19/GEN-20. Motor = GET integración + SP.
No reimplementar consultas /consultas/*. SoT: SPEC-101-25. Host: pq_sp_integracion_export_comprobantes_*.
```

## Historial

| Fecha | Evento |
|-------|--------|
| 2026-11-18 | Parte A borrador — rangos inclusivos, [-1,99], paginación cabecera, camelCase (decisiones usuario) |