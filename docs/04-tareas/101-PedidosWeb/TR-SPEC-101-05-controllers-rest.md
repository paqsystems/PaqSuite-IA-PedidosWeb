# TR-SPEC-101-05 — Controllers REST (API pedidos y presupuestos)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-009](../../03-historias-usuario/101-PedidosWeb/HU-101-009-grabar-pedido.md), [HU-101-010](../../03-historias-usuario/101-PedidosWeb/HU-101-010-grabar-presupuesto.md), [HU-101-011](../../03-historias-usuario/101-PedidosWeb/HU-101-011-editar-pedido.md), [HU-101-012](../../03-historias-usuario/101-PedidosWeb/HU-101-012-eliminar-pedido.md), [HU-101-013](../../03-historias-usuario/101-PedidosWeb/HU-101-013-conversion-presupuesto-pedido.md), [HU-101-024](../../03-historias-usuario/101-PedidosWeb/HU-101-024-conversion-pedido-presupuesto.md), [HU-101-026](../../03-historias-usuario/101-PedidosWeb/HU-101-026-copiar-comprobante.md), [HU-101-027](../../03-historias-usuario/101-PedidosWeb/HU-101-027-cierre-rechazo-presupuesto.md) |
| **SPEC relacionada** | [SPEC-101-05-controllers-rest](../../05-open-spec/101-PedidosWeb/SPEC-101-05-controllers-rest.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | [TR-SPEC-101-04-services-pedidos](TR-SPEC-101-04-services-pedidos.md); [TR-GEN-02-politicas-endpoints](../001-Generaliddes/TR-GEN-02-politicas-endpoints.md); [TR-GEN-02-visibilidad-datos-pedidosweb](../001-Generaliddes/TR-GEN-02-visibilidad-datos-pedidosweb.md); [matriz-permisos-mvp.md](../001-Generaliddes/matriz-permisos-mvp.md) |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 |

**Origen:** [SPEC-101-05](../../05-open-spec/101-PedidosWeb/SPEC-101-05-controllers-rest.md)  
**Referencia SPEC:** [SPEC-101-05-controllers-rest](../../05-open-spec/101-PedidosWeb/SPEC-101-05-controllers-rest.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
API REST `api/v1` para pedidos y presupuestos: controllers delgados, envelope MONO, OpenAPI y permisos `pw_cargapedidos`.

### Narrativa
Como **frontend PedidosWeb**,  
quiero **consumir endpoints REST autenticados que deleguen en los services de negocio**,  
para **grabar, editar, consultar, convertir, copiar y cerrar comprobantes con respuestas envelope consistentes y autorización alineada al menú ERP**.

### In scope / Out of scope

**In scope:**

- Rutas bajo prefijo `/api/v1` con header **`X-Paq-Cliente`** y Bearer Sanctum.
- **Pedidos:** `POST`, `PUT`, `GET` por `cod_pedido`; `DELETE` solo pedido **estado 0**.
- **Presupuestos:** `POST`, `PUT`, `GET`; **sin** `DELETE`.
- **Acciones:** `POST presupuestos/{id}/cerrar` (rechazo/cierre negativo); conversión presupuesto→pedido vía **Grabar pedido** unificado o `POST presupuestos/{id}/convertir` (alias documentado — **preferencia:** un solo contrato `POST /api/v1/comprobantes/grabar` con `accion` + `tipo` para matriz §10.1 **y** rutas REST explícitas por recurso para OpenAPI legible).
- **Copia:** `POST /api/v1/comprobantes/copiar`.
- **Edición -1:** `POST pedidos/{id}/edicion/iniciar`, `POST .../actividad`, `POST .../edicion/cancelar` (o equivalente en PUT pedido — documentar en implementación).
- Controllers **sin** lógica de negocio; mapeo DTO ↔ service TR-101-04.
- Respuestas envelope: [`envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).
- OpenAPI L5-Swagger + filas matriz permisos.
- Feature tests: 200, 401, 403, 422/409 según caso.

**Out of scope:**

- Implementación reglas en controller (101-04).
- Consultas listados / export (SPEC-101-07).
- Pantalla carga UI (SPEC-101-10).
- Mail SMTP (SPEC-101-13).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Todos los endpoints delegan 100 % en `PedidoService` / services TR-101-04.
- **AC-02**: Respuestas éxito y error cumplen envelope (`error` entero, `resultado` objeto nunca `null`).
- **AC-03**: `DELETE /api/v1/pedidos/{cod}` solo con permiso baja y pedido estado 0; **no** existe ruta DELETE presupuestos.
- **AC-04**: Usuario sin atributo menú `pw_cargapedidos` recibe **403** en operaciones de carga.
- **AC-05**: OpenAPI documenta `security`, `X-Paq-Cliente`, **401**, **403** por operación.
- **AC-06**: Matriz [`matriz-permisos-mvp.md`](../001-Generaliddes/matriz-permisos-mvp.md) actualizada con filas de este slice.
- **AC-07**: Feature test por endpoint protegido (mínimo éxito + 401 sin token).
- **AC-08**: `POST presupuestos/{id}/cerrar` persiste cierre 98 vía service (HU-101-027).
- **AC-09**: Grabación unificada soporta matriz §10.1 (pedido/presupuesto/conversiones) según body.
- **AC-10**: `POST comprobantes/copiar` retorna DTO precargado o id nuevo según contrato acordado en D1.

### Escenarios Gherkin

```gherkin
Feature: API REST pedidos y presupuestos

  Scenario: Grabar pedido sin token
  When POST /api/v1/pedidos sin Authorization
  Then HTTP 401
  And envelope error distinto de 0

  Scenario: Grabar pedido sin permiso carga
  Given usuario autenticado sin pw_cargapedidos
  When POST /api/v1/pedidos con body válido
  Then HTTP 403
  And resultado es objeto vacío o detalle error

  Scenario: DELETE presupuesto inexistente como ruta
  When DELETE /api/v1/presupuestos/{cod}
  Then HTTP 404 route not defined

  Scenario: DELETE pedido estado 1
  Given pedido en estado 1
  When DELETE /api/v1/pedidos/{cod} con permiso
  Then HTTP 422 o 409
  And error de negocio en envelope
```

---

## 3) Reglas de Negocio

> Las reglas de dominio están en TR-101-04. Los controllers solo **validan HTTP**, **autorizan** y **mapean**.

1. **RN-01**: Misma matriz grabación §10.1 vía body `accionGrabacion` en POST/PUT unificado o endpoints dedicados.
2. **RN-02**: `DELETE` pedido mapea a `eliminarPedido` — rechazo si estado ≠ 0.
3. **RN-03**: No exponer `DELETE` presupuesto (AMB-C03).
4. **RN-04**: Conversión presupuesto→pedido puede invocarse con `POST presupuestos/{id}/convertir` **o** `POST pedidos` con contexto origen presupuesto 99 — **misma** lógica service.
5. **RN-05**: Cierre rechazo: `POST presupuestos/{id}/cerrar` con `id_motivo` negativo obligatorio.
6. **RN-06**: Visibilidad cliente/vendedor aplicada en service/policy antes de mutar (TR-GEN-02-visibilidad).
7. **RN-07**: Idempotencia no requerida MVP; usar transacciones service.

---

## 4) Impacto en Datos

### Tablas afectadas

Indirectas vía TR-101-04 (mismas tablas operativas). Sin cambio de esquema en este slice.

### Seed mínimo para tests

- Usuario `vendedor.acotado.mvp` con menú `pw_cargapedidos`.
- Usuario `usuario.sinPermiso.mvp` sin atributo.
- Pedido 0, presupuesto 99, pedido 1 (delete fallido).
- Token Sanctum + header `X-Paq-Cliente: desarrollo`.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Envelope: [`envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| POST | `/api/v1/pedidos` | Bearer + `X-Paq-Cliente` | Procedimiento menú **`pw_cargapedidos`** (`Permiso_Alta` equivalente) | No |
| PUT | `/api/v1/pedidos/{cod_pedido}` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Modi`) | No |
| GET | `/api/v1/pedidos/{cod_pedido}` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Repo`) | No |
| DELETE | `/api/v1/pedidos/{cod_pedido}` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Baja`) — solo pedido estado **0** | No |
| POST | `/api/v1/presupuestos` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Alta`) | No |
| PUT | `/api/v1/presupuestos/{cod_pedido}` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Modi`) | No |
| GET | `/api/v1/presupuestos/{cod_pedido}` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Repo`) | No |
| POST | `/api/v1/presupuestos/{cod_pedido}/cerrar` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Modi`) | No |
| POST | `/api/v1/presupuestos/{cod_pedido}/convertir` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Alta`) | No |
| POST | `/api/v1/comprobantes/grabar` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (Alta/Modi según `cod_pedido` ausente/presente) | No |
| POST | `/api/v1/comprobantes/copiar` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Alta`) | No |
| POST | `/api/v1/pedidos/{cod_pedido}/edicion/iniciar` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Modi`) | No |
| POST | `/api/v1/pedidos/{cod_pedido}/edicion/actividad` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Modi`) | No |
| POST | `/api/v1/pedidos/{cod_pedido}/edicion/cancelar` | Bearer + `X-Paq-Cliente` | **`pw_cargapedidos`** (`Permiso_Modi`) | No |

**Nota permisos:** El atributo funcional MVP es el **procedimiento ERP de menú** `pw_cargapedidos` (matriz seed TR-GEN-02). `AccesoTotal` (supervisor) satisface el atributo. Mapeo a `Permiso_Alta|Modi|Baja|Repo` sigue convención PaqSuite por verbo HTTP.

**Rutas explícitamente ausentes:**

- `DELETE /api/v1/presupuestos/{cod_pedido}` — **no registrar** en `routes/api.php` ni OpenAPI.

### 5.2 Detalle por operación

#### POST `/api/v1/pedidos`

**Autorización:** `pw_cargapedidos` + visibilidad comercial

**Request:**

```json
{
  "cabecera": { "cod_cliente": "...", "lista_precios": 1, "incluye_iva": true },
  "renglones": [{ "cod_articulo": "...", "cantidad": 1, "precio": 100 }],
  "contexto": { "cod_presupuesto_origen": null, "accion": "grabar_pedido" }
}
```

**Response 200:** `resultado: { "cod_pedido", "nro_visible", "estado": 0, "total", "total_iva" }`

**Response 401:** no autenticado — `error` 3000, `respuesta`: `auth.unauthenticated`

**Response 403:** sin `pw_cargapedidos` — `error` 3000, `respuesta`: `auth.noPermission`

**Response 422:** validación — `error` 1000, `resultado: { "fields": {} }`

**OpenAPI (L5-Swagger):**

- [ ] Anotaciones en `PedidoController@store`
- [ ] `security`: bearerAuth
- [ ] Header `X-Paq-Cliente` documentado
- [ ] Respuestas 401 y 403
- [ ] Descripción: requiere procedimiento `pw_cargapedidos`

---

#### PUT `/api/v1/pedidos/{cod_pedido}`

**Autorización:** `pw_cargapedidos` (Modi)

**Request:** mismo schema que POST; incluye renglones completos (reemplazo vía service).

**Response 200:** cabecera actualizada estado **0** (o **-1** si endpoint de edición intermedio — preferir sub-rutas edición).

**Response 403/401:** igual que POST.

**Response 409:** conflicto edición -1 bloqueada — `error` 4000.

---

#### GET `/api/v1/pedidos/{cod_pedido}`

**Autorización:** `pw_cargapedidos` (Repo)

**Response 200:** `resultado: { "cabecera": {}, "renglones": [] }`

**Response 404:** comprobante inexistente o fuera de visibilidad — `error` 4000.

---

#### DELETE `/api/v1/pedidos/{cod_pedido}`

**Autorización:** `pw_cargapedidos` (Baja)

**Request:** sin body.

**Response 200:** `resultado: {}`, `respuesta`: `"ok"`

**Response 422:** estado ≠ 0 o `NOeliminaPedido` — `error` 2000.

**OpenAPI:** documentar explícitamente “solo pedido ingresado web estado 0”.

---

#### POST `/api/v1/presupuestos`

**Autorización:** `pw_cargapedidos` (Alta)

**Request:** análogo a pedidos; `contexto.accion`: `grabar_presupuesto`.

**Response 200:** `estado`: **99**

---

#### PUT `/api/v1/presupuestos/{cod_pedido}`

**Autorización:** `pw_cargapedidos` (Modi)

**Regla:** solo presupuesto **estado 99**.

---

#### GET `/api/v1/presupuestos/{cod_pedido}`

**Autorización:** `pw_cargapedidos` (Repo)

---

#### POST `/api/v1/presupuestos/{cod_pedido}/cerrar`

**Autorización:** `pw_cargapedidos` (Modi)

**Request:**

```json
{
  "id_motivo": 2,
  "observacion": "Cliente desistió"
}
```

**Response 200:** presupuesto **estado 98**; `resultado: { "id_cierre", "cod_presupuesto" }`

**Response 422:** motivo no negativo / presupuesto no 99 — `error` 2000.

**Regla:** **no** usa `CodMotivoCierreExitoso` (solo conversión — HU-101-013).

---

#### POST `/api/v1/presupuestos/{cod_pedido}/convertir`

**Autorización:** `pw_cargapedidos` (Alta)

**Request:** opcional ajuste renglones (mismo schema grabación).

**Response 200:** `resultado: { "cod_pedido_nuevo", "estado": 0, "cod_presupuesto_origen": "{cod}", "presupuesto_estado": 98 }`

**Alternativa:** omitir ruta si `POST /api/v1/comprobantes/grabar` cubre conversión — mantener **una** en implementación y documentar la elegida en OpenAPI (evitar duplicidad).

---

#### POST `/api/v1/comprobantes/grabar`

**Autorización:** `pw_cargapedidos`

**Request:**

```json
{
  "accionGrabacion": "pedido",
  "cod_pedido_origen": null,
  "cod_presupuesto_origen": "guid-pres-99",
  "cabecera": {},
  "renglones": []
}
```

**Valores `accionGrabacion`:** `pedido` | `presupuesto` — el service resuelve matriz §10.1 TR-101-04.

**Response 200:** `resultado` con comprobante persistido y metadatos de conversión si aplicó.

---

#### POST `/api/v1/comprobantes/copiar`

**Autorización:** `pw_cargapedidos` (Alta)

**Request:**

```json
{
  "cod_pedido_origen": "guid-origen",
  "tipo_destino": "pedido"
}
```

**Response 200:** `resultado: { "borrador": { "cabecera", "renglones" }, "cod_pedido": null }` o comprobante ya persistido según diseño D1.

---

#### POST `/api/v1/pedidos/{cod_pedido}/edicion/iniciar`

**Autorización:** `pw_cargapedidos` (Modi)

**Response 200:** `estado: -1`, timestamps actualizados.

**Response 409:** bloqueo activo otro usuario.

---

#### POST `/api/v1/pedidos/{cod_pedido}/edicion/actividad`

**Autorización:** `pw_cargapedidos` (Modi)

**Efecto:** `touchActividadEdicion` — extiende `MinutosWeb`.

**Response 200:** `resultado: { "fechahora_ultima_actividad": "..." }`

---

#### POST `/api/v1/pedidos/{cod_pedido}/edicion/cancelar`

**Autorización:** `pw_cargapedidos` (Modi)

**Response 200:** vuelve a **estado 0** si aplica.

### 5.3 Actualización matriz permisos

Agregar sección **PedidosWeb — Carga** en [`matriz-permisos-mvp.md`](../001-Generaliddes/matriz-permisos-mvp.md):

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| POST | `/api/v1/pedidos` | `pw_cargapedidos` | TR-SPEC-101-05 |
| PUT | `/api/v1/pedidos/{cod_pedido}` | `pw_cargapedidos` | TR-SPEC-101-05 |
| GET | `/api/v1/pedidos/{cod_pedido}` | `pw_cargapedidos` | TR-SPEC-101-05 |
| DELETE | `/api/v1/pedidos/{cod_pedido}` | `pw_cargapedidos` | TR-SPEC-101-05 |
| POST | `/api/v1/presupuestos` | `pw_cargapedidos` | TR-SPEC-101-05 |
| PUT | `/api/v1/presupuestos/{cod_pedido}` | `pw_cargapedidos` | TR-SPEC-101-05 |
| GET | `/api/v1/presupuestos/{cod_pedido}` | `pw_cargapedidos` | TR-SPEC-101-05 |
| POST | `/api/v1/presupuestos/{cod_pedido}/cerrar` | `pw_cargapedidos` | TR-SPEC-101-05 |
| POST | `/api/v1/presupuestos/{cod_pedido}/convertir` | `pw_cargapedidos` | TR-SPEC-101-05 |
| POST | `/api/v1/comprobantes/grabar` | `pw_cargapedidos` | TR-SPEC-101-05 |
| POST | `/api/v1/comprobantes/copiar` | `pw_cargapedidos` | TR-SPEC-101-05 |
| POST | `/api/v1/pedidos/{cod}/edicion/*` | `pw_cargapedidos` | TR-SPEC-101-05 |

- [ ] Filas agregadas en matriz
- [ ] Middleware/policy `pw_cargapedidos` implementado (reutilizar patrón `Pq_Permiso` / menú ERP)

---

## 6) Cambios Frontend

### Pantallas / componentes

- Cliente API `features/pedidos/api/pedidosApi.ts` (o módulo equivalente) con métodos alineados a rutas §5.1.
- Integración en pantalla carga TR-101-10: llamadas a `comprobantes/grabar` o rutas dedicadas.
- Manejo envelope en cliente existente (`apiRequest`).

### data-testid sugeridos

- Depende TR-101-10; este slice solo asegura contratos estables para tests E2E posteriores.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | `PedidoController`, `PresupuestoController`, `ComprobanteController` | Delgados |
| T2 | Backend | Form Requests / DTO HTTP validation | 422 coherentes |
| T3 | Backend | Policies/middleware `pw_cargapedidos` | 403 tests |
| T4 | Backend | Rutas `routes/api.php` grupo `v1` | Sin DELETE presupuesto |
| T5 | Backend | OpenAPI anotaciones + `OpenApi.php` tags | /api/documentation |
| T6 | Tests | `PedidosWebApiTest` feature por endpoint | AC-07 |
| T7 | Docs | Matriz permisos §5.3 | Filas publicadas |
| T8 | Frontend | Stubs cliente API (opcional mismo PR o TR-101-10) | Tipos TS |

---

## 8) Estrategia de Tests

- **Unit:** no duplicar TR-101-04; solo mappers HTTP si existen.
- **Integration / Feature:**
  - Por endpoint §5.1: **200** camino feliz (con seed).
  - **401** sin Bearer en cada familia pedidos/presupuestos.
  - **403** con `usuario.sinPermiso.mvp`.
  - DELETE pedido 0 OK; DELETE pedido 1 → 422.
  - POST cerrar presupuesto sin motivo → 422.
  - Verificar ausencia ruta DELETE presupuesto (404 route).
- **E2E:** ≥ 2 en TR-101-10 (grabar pedido feliz; 403 sin permiso).

---

## 9) Riesgos y Edge Cases

- **Duplicidad `convertir` vs `comprobantes/grabar`:** decidir en D1 un canal canónico para evitar dos contratos divergentes.
- **Payload grande renglones:** límite request PHP/nginx — documentar máximo recomendado.
- **GET comprobante 98/99:** presupuesto cerrado legible para consulta pero PUT rechazado.
- **Envelope con `resultado: null`:** prohibido — validar en tests JSON schema.
- **Tenant inválido:** 400 `tenant.invalid` (patrón MONO TR-GEN-02).

---

## 10) Checklist final

### Checklist del slice

- [ ] AC cumplidos
- [ ] Controllers delegan 100 % en services
- [ ] Feature tests verdes
- [ ] Swagger actualizado
- [ ] Sin DELETE presupuesto en rutas ni OpenAPI

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401 y 403 documentados por operación protegida
- [ ] Envelope JSON respetado (`error` entero, `resultado` objeto, nunca null)
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

(Post-implementación)

### Backend

- `backend/routes/api.php` (grupo pedidos/presupuestos/comprobantes)
- `backend/app/Http/Controllers/Api/V1/PedidosWeb/PedidoController.php`
- `backend/app/Http/Controllers/Api/V1/PedidosWeb/PresupuestoController.php`
- `backend/app/Http/Controllers/Api/V1/PedidosWeb/ComprobanteController.php`
- `backend/app/Http/Requests/PedidosWeb/*.php`
- `backend/app/Http/Middleware/RequirePwCargaPedidos.php` (nombre final según convención)
- `backend/tests/Feature/PedidosWeb/PedidosWebApiTest.php`

### Frontend

- `frontend/src/features/pedidos/api/*.ts` (stubs)

### OpenAPI

- `backend/OpenApi.php` (tags PedidosWeb)
- Controllers anotados del slice

### Docs

- `docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md` (sección Carga)
