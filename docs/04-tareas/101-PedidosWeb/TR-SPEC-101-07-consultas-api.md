# TR-SPEC-101-07 — Consultas API (listados y metadata)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-015](../../03-historias-usuario/101-PedidosWeb/HU-101-015-consulta-pedidos-ingresados.md) … [HU-101-018](../../03-historias-usuario/101-PedidosWeb/HU-101-018-consulta-stock.md), [HU-101-021](../../03-historias-usuario/101-PedidosWeb/HU-101-021-consulta-deuda.md) … [HU-101-023](../../03-historias-usuario/101-PedidosWeb/HU-101-023-historial-ventas.md) |
| **SPEC relacionada** | [SPEC-101-07-consultas-api](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md) |
| **Épica** | 101 — PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | TR-SPEC-101-06 (visibilidad); SPEC-101-03 (repositories); contexto [SPEC-001-04](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) para `DiasVentasDetalladas` |
| **Estado** | Pendiente de Revisión — **Bloques 1–2** (gestión + pedidos/presupuestos) |
| **Última actualización** | 2026-06-02 |

**Origen:** HU-101-015, HU-101-016, HU-101-017, HU-101-018, HU-101-021, HU-101-022, HU-101-023  
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

**Response 200:** ítems `codArticulo`, `descripcion`, `stock`, `unidad`, etc.; `metadata.fecha_proceso`.

---

#### GET `/api/v1/consultas/deuda`

**Autorización:** `Permiso_Repo` + visibilidad

**Query:** `cod_cliente` opcional (supervisor/vendedor); cliente fijo implícito si perfil `cliente`

**Response 200:** ítems con saldo, vencimiento, saldo acumulado; `metadata.fecha_proceso`.

---

#### GET `/api/v1/consultas/cheques`

**Autorización:** `Permiso_Repo` + visibilidad

**Regla:** cheques en cartera o aplicados con fecha **posterior al día** (§17.5).

**Response 200:** ítems + `metadata.fecha_proceso`.

---

#### GET `/api/v1/consultas/historial-ventas`

**Autorización:** `Permiso_Repo` + visibilidad

**Query:** `cod_cliente` (requerido salvo perfil cliente); paginación

**Regla:** ventas desde `today - DiasVentasDetalladas` días (parámetro ERP/tenant).

**Response 200:** ítems resumen; detalle vía endpoint opcional `GET .../historial-ventas/{id}/detalle` o payload embebido acordado en implementación (documentar en OpenAPI). Incluir `metadata.fecha_proceso` y `metadata.dias_ventas_detalladas`.

**Response 422:** `cod_cliente` ausente cuando es obligatorio (`error` 1000, clave `validation.*`).

### 5.3 OpenAPI (L5-Swagger)

- [ ] Controller `ConsultasController` (o por recurso) anotado
- [ ] `security`: Bearer + header `X-Paq-Cliente`
- [ ] Respuestas 401, 403, 422 documentadas
- [ ] Ejemplos envelope en descripción
- [ ] Verificado en `/api/documentation`

### 5.4 Actualización matriz permisos

- [ ] Una fila por endpoint §5.1 en `matriz-permisos-mvp.md` con TR origen `TR-SPEC-101-07`

---

## 6) Cambios Frontend

Ninguno en este slice (API only). TR-SPEC-101-11 consumirá estos endpoints.

**data-testid:** N/A (backend).

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Repositories consulta + visibilidad | Unit tests filtros |
| T2 | Backend | 7 endpoints GET + DTO respuesta | OpenAPI |
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

### Checklist del slice (completo épica)
- [ ] AC-01…AC-10
- [ ] 7 endpoints operativos
- [ ] Metadata `fecha_proceso` en consultas ERP

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401 y 403 cuando aplique documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

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

### OpenAPI
- `app/OpenApi/PedidosWebOpenApiPaths.php`

### Docs
- `matriz-permisos-mvp.md` (pendiente filas consultas gestión)
