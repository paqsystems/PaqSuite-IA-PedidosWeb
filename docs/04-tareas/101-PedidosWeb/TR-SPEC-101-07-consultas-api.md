# TR-SPEC-101-07 — Consultas API (listados y metadata)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-015](../../03-historias-usuario/101-PedidosWeb/HU-101-015-consulta-pedidos-ingresados.md) … [HU-101-023](../../03-historias-usuario/101-PedidosWeb/HU-101-023-historial-ventas.md), [HU-101-028](../../03-historias-usuario/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos.md) |
| **SPEC relacionada** | [SPEC-101-07-consultas-api](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md) |
| **Épica** | 101 — PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | TR-SPEC-101-06 (visibilidad); SPEC-101-03 (repositories); contexto [SPEC-001-04](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) para `DiasVentasDetalladas` |
| **Estado** | Finalizado |
| **Última actualización** | 2026-06-09 (Parte I — CC PQ #1) |

**Origen:** HU-101-015, HU-101-016, HU-101-017, HU-101-018, HU-101-021, HU-101-022, HU-101-023, **HU-101-028**  
**Referencia SPEC:** [SPEC-101-07-consultas-api](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Exponer endpoints GET de consultas comerciales con paginación, visibilidad por perfil y metadata `fecha_proceso`.

### Narrativa
Como **usuario comercial autorizado**,  
quiero **consultar pedidos, presupuestos, stock, deuda, cheques e historial vía API**,  
para **alimentar grillas DevExtreme (SPEC-101-11) y exportación Excel (GEN-03)**.

### In scope / Out of scope
- **In scope:** GET bajo prefijo `/api/v1/consultas/*`; filtros básicos; paginación envelope; `fecha_proceso` en metadata de respuesta; visibilidad cliente/vendedor/supervisor; estados según producto §17; parámetro `DiasVentasDetalladas` para historial.
- **Out of scope:** pantallas React (TR-SPEC-101-11); PDF (futuro SPEC-001-06); mutaciones de comprobantes (TR-SPEC-101-05/10).

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** `GET .../pedidos-ingresados` devuelve estados **0** y **-1** cuando aplique control operativo; columnas mínimas producto §17.1.
- **AC-02:** `GET .../pedidos-pendientes` devuelve solo estado **1**; sin acciones de edición en contrato (solo lectura).
- **AC-03:** `GET .../presupuestos` con `estado=99` (activos) y `estado=98` (cerrados) en rutas o query acordada; cerrados incluyen datos de `presupuestos_cierres`.
- **AC-04:** Stock, deuda, cheques, historial aplican reglas §17.4–17.7; stock **no** restringido por cliente en filtro base (búsqueda artículo).
- **AC-05:** Toda respuesta de consulta ERP-sincronizada incluye `resultado.metadata.fecha_proceso` (ISO-8601) para carátula UI.
- **AC-06:** Paginación estándar en `resultado`: `items`, `page`, `page_size`, `total`, `total_pages`.
- **AC-07:** Filtro `cod_cliente` opcional; si se omite, listado acotado al universo `visibleClientsForUser`.
- **AC-08:** Historial: rango de fechas = hoy − `DiasVentasDetalladas` (días); valor desde parámetro tenant (SPEC-001-04) con default documentado en TR si SPEC-001-04 pendiente.
- **AC-09:** 401 sin token; 403 sin `Permiso_Repo` (o regla acordada); 404 en detalle fuera de visibilidad.
- **AC-10:** OpenAPI + matriz permisos actualizados; feature test por endpoint Must.
- **AC-11 (Bloque 3):** `GET .../detalle-pedidos` devuelve join cabecera+detalle; todos los estados; paginación por **renglón**; sin flags `puede*`.

### Escenarios Gherkin

```gherkin
Feature: Consultas API PedidosWeb

  Scenario: Pedidos ingresados paginados
    Given un vendedor con Permiso_Repo y 50 pedidos visibles en estado 0
    When GET /api/v1/consultas/pedidos-ingresados?page=1&page_size=20
    Then HTTP 200 con resultado.items de longitud 20
    And resultado.total es 50
    And resultado.metadata.fecha_proceso está presente

  Scenario: Presupuestos cerrados
    Given un supervisor autenticado
    When GET /api/v1/consultas/presupuestos?estado=98
    Then solo presupuestos estado 98 del tenant visible
    And cada ítem incluye referencia a cierre cuando exista

  Scenario: Historial respeta DiasVentasDetalladas
    Given parámetro DiasVentasDetalladas = 90
    When GET /api/v1/consultas/historial-ventas?cod_cliente=CLI001
    Then el servicio limita ventas a los últimos 90 días
```

---

## 3) Reglas de Negocio

1. **RN-01:** Visibilidad siempre en backend (`visibleClientsForUser`); query `cod_cliente` ajeno → **404** o lista vacía según operación (detalle → 404).
2. **RN-02:** Pedidos ingresados: estados **0** y **-1** (producto §17.1 / SPEC MVP §6.3).
3. **RN-03:** Pedidos pendientes: solo estado **1** (§17.3).
4. **RN-04:** Presupuestos activos **99**; cerrados **98** (§17.2 / §17.2.1); **sin** DELETE en API.
5. **RN-05:** `fecha_proceso`: valor único por snapshot ERP en metadata; no repetir en cada fila si la UI usa carátula.
6. **RN-06:** `DiasVentasDetalladas`: entero ≥ 1; lectura vía servicio de parámetros (SPEC-001-04); fallback MVP `90` documentado hasta existir seed parámetro.
7. **RN-07:** Deuda/cheques: por cliente o agregado según perfil (§17.4–17.5).
8. **RN-08:** Preparar contrato estable para export Excel (GEN-03): mismos filtros/query que listado.
9. **RN-09 (Bloque 3):** Detalle pedidos: sin filtro de estado por defecto; paginar filas detalle; `id` lógico `{codPedido}-{renglon}` en UI.

---

## 3.1) Informe C1 — Bloque 3 detalle pedidos (2026-06-03)

**Fuentes revisadas:** HU-101-028, [consulta-detalle-pedidos.md](../../02-producto/PedidosWeb/consulta-detalle-pedidos.md), [consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md), `ConsultaListadoService::mapComprobanteItem`, `PqPedidoswebPedidoDetalle`, bloques 1–2 ya implementados en esta TR.

> **Nota:** Bloques 1–2 pasaron C1/D1 en 2026-06-02. Esta revisión cubre **solo el delta Bloque 3**.

### Resultado general

- **Estado:** Apto con observaciones
- **Ambigüedades bloqueantes:** 0
- **Puede pasar a D1:** **Sí** (aplicar resoluciones §3.2)

### Ambigüedades críticas

| ID | Tema | Riesgo | Estado | Resolución (→ D1) |
|----|------|--------|--------|-------------------|
| AMB-C01 | Paginación cabecera vs renglón | Total incorrecto en UI | **Cerrado** (R-C1-01) | Paginar por **renglón**; `total` = count join filtrado. |
| AMB-C02 | Reutilizar `mapComprobanteItem` | Duplicación / drift contrato | **Cerrado** (R-C1-02) | Mismo mapper cabecera + campos detalle en ítem. |
| AMB-C03 | `fecha_proceso` | Sin snapshot ERP dedicado | **Cerrado** (R-C1-03) | `now()->toIso8601String()` en `metadata` (igual bloques 1–2). |
| AMB-C04 | Filtro `q` | Alcance búsqueda | **Cerrado** (R-C1-04) | `cod_articulo`, `descripcion_articulo`, fallback `articulos.descripcion`. |
| AMB-C05 | `precioLista` | `precio` vs `importe_lista` | **Cerrado** (R-C1-05) | `precioLista` = `precio` si no null; si no `importe_lista`. |
| AMB-C06 | Cabeceras sin renglones | INNER JOIN excluye filas | **Cerrado** (R-C1-06) | **INNER JOIN** MVP; cabecera sin detalle no aparece (aceptado producto). |

### Ambigüedades menores

| ID | Tema | Resolución (→ D1) |
|----|------|-------------------|
| AMB-M01 | Servicio dedicado vs extensión | **`DetallePedidosConsultaService`** nuevo; delega mapper cabecera a helper compartido. |
| AMB-M02 | Eager load maestros cabecera | Reutilizar relaciones de `mapComprobanteItem`; evitar N+1 con `with()` en query join. |
| AMB-M03 | Permiso procedimiento | `pw_detallepedidos` en `paqsuite_visibility.php`; matriz permisos nueva fila. |
| AMB-M04 | OpenAPI | Añadir path en `PedidosWebOpenApiPaths` + feature happy path. |

### Contradicciones TR ↔ HU ↔ producto

| Contradicción | Resolución |
|---------------|------------|
| Producto §3 visible `estadoTexto` vs API solo `estado` numérico | UI traduce con i18n (`consultas.comprobanteEstado.*`); no campo API duplicado. |
| Producto §4 `descuento` vs modelo `porc_bonif` | API `porcBonif`; UI caption «Descuento» vía i18n. |
| Producto descripción: preferir congelada | `descripcion_articulo` si no vacío; else join `articulos.descripcion`. |
| Ninguna bloqueante adicional | — |

### Supuestos detectados

- Existen filas en `pq_pedidosweb_pedidosdetalle` para pedidos QA del seed MVP.
- Visibilidad reutiliza `PedidosWebVisibilityGuard` / `resolveCodCliente` (404 cliente ajeno).
- Procedimiento menú `pw_detallepedidos` se agrega en mismo PR que endpoint.

### Preguntas para decisión humana

(Ninguna bloqueante — cerradas en §3.2.)

### Veredicto C1

**Apto con observaciones para D1** (Bloque 3).

---

## 3.2) Resoluciones C1 — pre-D1 (Bloque 3)

| ID | Decisión |
|----|----------|
| R-C1-01 | Paginación estándar sobre query join; `metadata.fecha_proceso` presente. |
| R-C1-02 | Ítem JSON = salida `mapComprobanteItem` + `{ renglon, codArticulo, descripcionArticulo, cantidad, porcBonif, precioLista, precioNeto, importeBruto, importeNeto, ivaNeto, importeNetoConIva }`. |
| R-C1-03 | Sin flags `puede*` en respuesta. |
| R-C1-04 | Orden SQL: `cabecera.fecha DESC`, `cabecera.cod_pedido`, `detalle.renglon`. |
| R-C1-05 | `precioLista`: coalesce `detalle.precio`, `detalle.importe_lista`. |
| R-C1-06 | INNER JOIN detalle; documentar en OpenAPI que solo comprobantes con ≥1 renglón. |
| R-C1-07 | Método `ConsultaController::detallePedidos`; ruta `GET consultas/detalle-pedidos`. |
| R-C1-08 | Tests: happy path supervisor; 404 `cod_cliente` ajeno; paginación `total` coherente. |

---

## 3.3) Plan D1 — Bloque 3 (2026-06-03)

### Alcance entendido

Un endpoint GET paginado con join cabecera+detalle+maestros, visibilidad, metadata, OpenAPI y tests. Sin cambios frontend (TR-101-11).

### Orden sugerido

```text
1. DetallePedidosConsultaService (query + mapItem)
2. ConsultaController::detallePedidos + ruta api.php
3. paqsuite_visibility consultasDetallePedidos → pw_detallepedidos
4. PedidosWebOpenApiPaths + matriz permisos
5. Feature tests (200, 404 visibilidad, paginación)
```

### Dependencias D1

- Bloques 1–2 operativos (`ConsultaListadoService`, visibilidad).
- Columnas cabecera estables (`mapComprobanteItem`).

---

## 4) Impacto en Datos

### Tablas afectadas
- `pq_pedidosweb_pedidoscabecera`, `pq_pedidosweb_pedidosdetalle`
- `pq_pedidosweb_presupuestos_cierres` (join cerrados 98)
- Fuentes ERP/archivos para stock, deuda, cheques, historial (repositories SPEC-101-03)
- Parámetros: `DiasVentasDetalladas` (tabla/config según SPEC-001-04)

### Seed mínimo para tests
- Pedidos estados 0, -1, 1 en clientes de `cliente.mvp` y `vendedor.acotado.mvp`
- Presupuestos 99 y 98 con fila en `presupuestos_cierres`
- Parámetro `DiasVentasDetalladas` en tenant `desarrollo`

---

## 5) Contratos de API y OpenAPI

> Envelope y paginación: [`envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md), [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §2.

**Headers comunes (todas las operaciones):**

| Header | Requerido | Valor |
|--------|-----------|--------|
| `Authorization` | Sí | `Bearer {token}` |
| `X-Paq-Cliente` | Sí | `desarrollo` (MVP) |
| `Accept` | Sí | `application/json` |

**Query común de paginación/filtros:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `page` | int ≥ 1 | Default `1` |
| `page_size` | int 1…100 | Default `20` |
| `cod_cliente` | string | Opcional; validar visibilidad |
| `fecha_desde` / `fecha_hasta` | date | Opcional donde aplique |
| `q` | string | Búsqueda libre (número visible, texto) según endpoint |

**Estructura `resultado` listados:**

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "items": [],
    "page": 1,
    "page_size": 20,
    "total": 0,
    "total_pages": 0,
    "metadata": {
      "fecha_proceso": "2026-06-01T08:30:00Z",
      "dias_ventas_detalladas": 90
    }
  }
}
```

(`dias_ventas_detalladas` solo en historial-ventas si aplica.)

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso | HU |
|--------|------|------|---------|-----|
| GET | `/api/v1/consultas/pedidos-ingresados` | Bearer + tenant | `Permiso_Repo` | HU-101-015 |
| GET | `/api/v1/consultas/pedidos-pendientes` | Bearer + tenant | `Permiso_Repo` | HU-101-017 |
| GET | `/api/v1/consultas/presupuestos` | Bearer + tenant | `Permiso_Repo` | HU-101-016 |
| GET | `/api/v1/consultas/stock` | Bearer + tenant | `Permiso_Repo` | HU-101-018 |
| GET | `/api/v1/consultas/deuda` | Bearer + tenant | `Permiso_Repo` | HU-101-021 |
| GET | `/api/v1/consultas/cheques` | Bearer + tenant | `Permiso_Repo` | HU-101-022 |
| GET | `/api/v1/consultas/historial-ventas` | Bearer + tenant | `Permiso_Repo` | HU-101-023 |
| GET | `/api/v1/consultas/detalle-pedidos` | Bearer + tenant | `Permiso_Repo` | HU-101-028 |

### 5.2 Detalle por operación

#### GET `/api/v1/consultas/pedidos-ingresados`

**Autorización:** `Permiso_Repo` + `visibleClientsForUser`

**Query:** paginación común; `cod_cliente`; `estado` opcional (`0`, `-1`); `q`

**Response 200:** `resultado.items[]` con: `codPedido`, `codCliente`, `razonSocial`, `fecha`, `numeroVisible`, `guidSufijo`, `total`, `estado`, flags `puedeEditar`, `puedeEliminar`, `puedeCopiar` según permiso y estado.

**Response 401 / 403:** estándar MONO.

---

#### GET `/api/v1/consultas/pedidos-pendientes`

**Autorización:** `Permiso_Repo` + visibilidad

**Regla:** solo `estado = 1`; sin flags de edición/eliminación.

**Response 200:** ítems lectura; `metadata.fecha_proceso`.

---

#### GET `/api/v1/consultas/presupuestos`

**Autorización:** `Permiso_Repo` + visibilidad

**Query obligatorio:** `estado` = `99` | `98`

| `estado` | Comportamiento |
|----------|----------------|
| `99` | Activos; flags editar, convertir, cerrar, copiar según permisos |
| `98` | Cerrados; solo lectura; join `motivoCierre`, `tipoCierre`, `fechaCierre` desde `presupuestos_cierres` |

**Response 200:** paginado + `metadata.fecha_proceso`.

---

#### GET `/api/v1/consultas/stock`

**Autorización:** `Permiso_Repo`

**Query:** `q` (código/descripción), `todos` (bool, listado amplio según §17.7)

**Visibilidad:** no filtra por cliente; sí por tenant.

**Response 200:** ítems según [`consulta-stock.md`](../../02-producto/PedidosWeb/consulta-stock.md): `codArticulo`, `descripcion`, `stock`, `comprometido`, `comprometidoWeb`, `disponibleNeto`, métricas `*Base` opcionales; `metadata.fecha_proceso`. Implementación: `StockConsultaService` (SQL agregado).

---

#### GET `/api/v1/consultas/deuda`

**Autorización:** `Permiso_Repo` + visibilidad

**Query:** `cod_cliente` opcional (supervisor/vendedor); cliente fijo implícito si perfil `cliente`

**Response 200:** ítems según [`consulta-deuda.md`](../../02-producto/PedidosWeb/consulta-deuda.md): `codCliente`, `razonSocial`, `tipo`, `numero`, `fecha`, `vencimiento`, `saldo`; `metadata.fecha_proceso`. Implementación: `DeudaConsultaService` (SQL paginado + join clientes).

---

#### GET `/api/v1/consultas/cheques`

**Autorización:** `Permiso_Repo` + visibilidad

**Regla:** cheques en cartera o aplicados con `fecha` **≥ día actual** (§17.5).

**Response 200:** ítems según [`consulta-cheques.md`](../../02-producto/PedidosWeb/consulta-cheques.md): `interno`, `numero`, `codCliente`, `nombreCliente`, `banco`, `fecha`, `importe`, `origen`, `estado`; `metadata.fecha_proceso`. Implementación: `ChequesConsultaService` (SQL paginado + join clientes).

---

#### GET `/api/v1/consultas/historial-ventas`

**Autorización:** `Permiso_Repo` + visibilidad

**Query:** `cod_cliente` (requerido salvo perfil cliente); paginación

**Regla:** ventas desde `today - DiasVentasDetalladas` días (parámetro ERP/tenant).

**Response 200:** ítems según [`consulta-historial-ventas.md`](../../02-producto/PedidosWeb/consulta-historial-ventas.md) (22 campos JSON); `metadata.fecha_proceso` y `metadata.dias_ventas_detalladas`. Implementación: `HistorialVentasConsultaService`.

**Response 422:** `cod_cliente` ausente cuando es obligatorio (`error` 1000, clave `validation.*`).

---

#### GET `/api/v1/consultas/detalle-pedidos`

**Autorización:** `Permiso_Repo` + `visibleClientsForUser`

**Regla:** join `pq_pedidosweb_pedidoscabecera` + `pq_pedidosweb_pedidosdetalle`; **todos los estados**; una fila por renglón.

**Query:** paginación común; `cod_cliente`; `cod_pedido`; `estado` opcional; `q` (código/descripción artículo)

**Response 200:** `resultado.items[]` con:

- Campos cabecera: mismo contrato que `mapComprobanteItem` ([consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md)).
- Campos detalle (camelCase):

| Propiedad | Origen BD |
|-----------|-----------|
| `renglon` | `detalle.renglon` |
| `codArticulo` | `detalle.cod_articulo` |
| `descripcionArticulo` | `detalle.descripcion_articulo` → fallback `articulos.descripcion` |
| `cantidad` | `detalle.cantidad` |
| `porcBonif` | `detalle.porc_bonif` |
| `precioLista` | `detalle.precio` o `detalle.importe_lista` |
| `precioNeto` | `detalle.precio_neto` |
| `importeBruto` | `detalle.precio_bruto` |
| `importeNeto` | `detalle.importe_neto` |
| `ivaNeto` | `detalle.iva` |
| `importeNetoConIva` | `detalle.importe_total` |

Sin flags `puedeEditar` / `puedeEliminar` / etc.

**Orden SQL:** `cabecera.fecha DESC`, `cabecera.cod_pedido`, `detalle.renglon`.

**Implementación:** `DetallePedidosConsultaService` (nuevo) o método en `ConsultaListadoService`.

**Response 404:** `cod_cliente` ajeno al universo visible.

Fuente producto: [consulta-detalle-pedidos.md](../../02-producto/PedidosWeb/consulta-detalle-pedidos.md).

### 5.3 OpenAPI (L5-Swagger)

- [ ] Controller `ConsultasController` (o por recurso) anotado
- [ ] `security`: Bearer + header `X-Paq-Cliente`
- [ ] Respuestas 401, 403, 422 documentadas
- [ ] Ejemplos envelope en descripción
- [ ] Verificado en `/api/documentation`

### 5.4 Actualización matriz permisos

- [x] Una fila por endpoint §5.1 en `matriz-permisos-mvp.md` con TR origen `TR-SPEC-101-07` (incl. `detalle-pedidos`)

---

## 6) Cambios Frontend

Ninguno en este slice (API only). TR-SPEC-101-11 consumirá estos endpoints.

**data-testid:** N/A (backend).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Repositories consulta + visibilidad | Unit tests filtros |
| T2 | Backend | 8 endpoints GET + DTO respuesta | OpenAPI |
| T2b | Backend | `GET detalle-pedidos` + `DetallePedidosConsultaService` | Feature test HU-028 |
| T3 | Backend | Servicio parámetro `DiasVentasDetalladas` | Fallback documentado |
| T4 | Tests | Feature por endpoint: 200, 401, 403, 404 | CI verde |
| T5 | Docs | Matriz permisos | Checklist §10 |

---

## 8) Estrategia de Tests

- **Unit:** cálculo rango historial; mapeo estados 0/-1/1/99/98.
- **Integration:** cada endpoint con perfiles cliente/vendedor/supervisor; paginación; `fecha_proceso` presente.
- **E2E:** diferidos a TR-SPEC-101-11 (grillas).

---

## 9) Riesgos y Edge Cases

- **R1:** SPEC-001-04 pendiente → usar default `DiasVentasDetalladas` y marcar deuda técnica.
- **R2:** `fecha_proceso` nula si ERP no sincronizó → devolver `null` en metadata con `respuesta` clave i18n en UI (no inventar fecha).
- **R3:** Volumen alto sin índices → validar `page_size` máximo.
- **R4:** Presupuestos 98 sin fila cierre → ítem con datos cabecera y cierre parcial vacío.

---

## 10) Checklist final

### Checklist del slice (Bloque 2 — pedidos/presupuestos)
- [x] AC-01 — pedidos ingresados estados 0/-1 + flags `puedeEditar`/`puedeEliminar`/`puedeCopiar`
- [x] AC-02 — pedidos pendientes estado 1, solo lectura en flags
- [x] AC-03 — presupuestos `estado=99|98`; join `presupuestos_cierres` en 98
- [x] AC-07 — `cod_cliente` validado en comprobantes (404)
- [ ] AC-09 — 403 consultas (parcial; 404 visibilidad OK)
- [ ] AC-10 — matriz permisos consultas (parcial)

### Checklist del slice (Bloque 1 — gestión)
- [x] AC-04 — stock/deuda/cheques/historial (§17.4–17.7)
- [x] AC-05 — `metadata.fecha_proceso` en respuestas ERP-sincronizadas
- [x] AC-06 — paginación estándar
- [x] AC-07 — filtro `cod_cliente` con validación visibilidad (`PedidosWebVisibilityGuard` → 404)
- [x] AC-08 — historial respeta `DiasVentasDetalladas` + metadata
- [x] AC-09 — 401 cubierto; 404 `cod_cliente` ajeno (feature + unit)
- [ ] AC-01…03 — pedidos/presupuestos (Bloque 2)
- [ ] AC-10 — OpenAPI/matriz completos (parcial: paths en `PedidosWebOpenApiPaths`)

### Checklist del slice (Bloque 3 — detalle pedidos, HU-101-028)
- [x] AC-11 — endpoint `GET detalle-pedidos` + join cabecera/detalle
- [x] OpenAPI + matriz permisos fila detalle-pedidos

### Checklist del slice (completo épica)
- [x] AC-01…AC-11 (D1; feature 200/403 SQL tanda 2)
- [x] 8 endpoints operativos (+ detalle-pedidos)
- [x] Metadata `fecha_proceso` en consultas ERP

### Checklist normas transversales

- [x] Endpoints nuevos/modificados con policy en código
- [x] Matriz endpoint ↔ permiso actualizada
- [x] OpenAPI en `PedidosWebOpenApiPaths` coherente con código y matriz
- [x] 401 y 403 cuando aplique documentados por operación protegida
- [x] Envelope JSON respetado
- [x] X-Paq-Cliente documentado donde aplique
- [x] Tests API incluyen 401 (y 403 si aplica; skip sin SQL)
- [x] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

### Bloque 2 (2026-06-02) — pedidos/presupuestos
- `backend/app/Services/PedidosWeb/ConsultaListadoService.php` — flags acción, join cierres 98, visibilidad comprobantes
- `backend/app/Services/Visibility/VisibilityPermissionGuard.php` — `hasPermission`
- `backend/app/Models/PqPedidoswebPedidoCabecera.php` — relación `presupuestoCierre`
- `backend/tests/Feature/Api/PedidosWeb/PedidosWebVisibilityFeatureTest.php` — 404 pedidos/presupuestos consultas

### Bloque 1 (2026-06-02) — gestión
- `backend/app/Services/PedidosWeb/ConsultaListadoService.php` — `resolveCodCliente` + `PedidosWebVisibilityGuard`
- `backend/tests/Unit/PedidosWeb/Services/ConsultaListadoServiceTest.php`
- `backend/tests/Feature/Api/PedidosWeb/PedidosWebVisibilityFeatureTest.php` — 404 deuda/cheques/historial

### Backend (implementación previa)
- `app/Http/Controllers/Api/V1/PedidosWeb/ConsultaController.php`
- `app/Services/PedidosWeb/ConsultaListadoService.php`
- Tests `tests/Feature/Api/PedidosWeb/PedidosWebEndpointsAuthTest.php`, `PedidosWebEndpointsHappyPathTest.php`

### Bloque 3 — detalle pedidos (HU-101-028, 2026-06-03)
- `backend/app/Services/PedidosWeb/DetallePedidosConsultaService.php` (nuevo)
- `ConsultaController::detallePedidos`
- `config/paqsuite_visibility.php` — `consultasDetallePedidos` → `pw_detallepedidos`
- Tests feature `PedidosWebEndpointsHappyPathTest` + visibilidad 404

### OpenAPI
- `app/OpenApi/PedidosWebOpenApiPaths.php`

### Docs
- `matriz-permisos-mvp.md` (pendiente filas consultas gestión + detalle-pedidos)

---

## Historial CC PQ #1 (04/06/2026) — Parte I 09/06/2026

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | `nombreFantasia` en DTOs consultas cabecera | `ConsultaListadoService::mapComprobanteItem` |
| T2 | `fecha_proceso` precisión minutos | `ConsultaFechaProcesoFormatter` |
| T3 | `precioNeto` en consulta detalle | `DetallePedidosConsultaService` |
| T4 | Copiar desde pendientes (permiso alta) | `puedeCopiar` en pendientes |
| T5 | Feature tests consultas | `PedidosWebEndpointsHappyPathTest` (CI SQL) |

Producto actualizado: [consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md), [consulta-detalle-pedidos.md](../../02-producto/PedidosWeb/consulta-detalle-pedidos.md).
