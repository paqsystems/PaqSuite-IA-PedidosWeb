# HU-101-046 — Plataforma API importación maestros (ERP)

Jira: PIAPEDWEB-36

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-046-integracion-maestros-plataforma |
| **SPEC origen** | [SPEC-101-22-importacion-maestros-erp](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Épica** | APIs importación maestros ERP → PedidosWeb |
| **Prioridad** | **Must** |
| **Estado** | **Finalizado** (Parte I SPEC-101-22, 2026-10-29) |
| **B1** | Cerrado (2026-10-21) |
| **TR** | [TR-SPEC-101-22-integracion-maestros-plataforma](../../04-tareas/101-PedidosWeb/TR-SPEC-101-22-integracion-maestros-plataforma.md) |
| **Dependencias** | GEN-19 tenancy · GEN-20 envelope · SPEC-101-08 (logs) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Auth `pw_integracion_importacion` + Sanctum | RN-01, CA-01, CA-02 |
| Header `X-Paq-Cliente` obligatorio | RN-02, CA-03 |
| Envelope GEN-20 + cuerpo lote mínimo | RN-03…RN-07, CA-04…CA-08 |
| Tope 1500 ítems; `items:[]` delta no-op / snapshot rechazado | RN-08, CA-09, CA-10 |
| Log `maestro_import` + severidad | RN-09, CA-11 |
| 401/403/404 recurso; rate limit | RN-10, CA-12, CA-13 |
| OpenAPI tag transversal | CA-14 |
| CA producto §7 (1) vendedor no importa | CA-02 |
| Sin rutas transacciones | Fuera de alcance |

## Narrativa

Como **operador de integración / job ERP**,  
quiero **autenticarme con un token técnico por tenant y enviar lotes JSON con contrato estable**,  
para **actualizar maestros sin usar credenciales de vendedor del portal**.

## Contexto funcional

Capa común de `POST /api/v1/integracion/maestros/{recurso}`: middleware de integración, validación del sobre del lote, despacho al handler por recurso, envelope de respuesta con contadores y errores por ítem, y registro en `pq_pedidosweb_logs_integracion`. La persistencia por recurso la implementan HU-101-047 y HU-101-048.

## Alcance incluido

- Ability/permiso `pw_integracion_importacion` (matriz seguridad; **sin** ítem de menú MVP).
- Usuario técnico Sanctum: documentación/comando de provisión ops (no auto-alta silenciosa en cada deploy).
- Grupo de rutas `integracion/maestros` con middleware: tenant GEN-19, auth Sanctum, gate integración.
- Validación request: `modo` (`delta`|`snapshot`), `fechaProceso`, `origen` opcional, `items` array; tope **1500** ítems planos (escalas: regla A1 en handler).
- Respuesta envelope `resultado`: `recurso`, `modo`, `recibidos`, `aplicados`, `omitidos`, `rechazados`, `errores[]` (máx. 50 + i18n truncado).
- HTTP **200** + `error: 0` con éxito parcial; **4xx/5xx** + `error ≠ 0` si lote no procesado.
- `{recurso}` desconocido → **404**.
- Integración con `LogIntegracionService` (tipo `maestro_import`).
- Rate limit propuesto A1: 60 req/min por token+tenant.
- OpenAPI: esquemas compartidos de lote y respuesta; tag **Integración maestros**.
- Tests feature: auth, tenant, envelope, delta vacío, snapshot vacío rechazado, recurso inválido.

## Fuera de alcance

- Lógica SP y mapeo campo a campo por tabla → HU-047 / HU-048.
- `/api/v1/integracion/transacciones/*` (SPEC-101-23).
- UI portal, mobile, Excel pedidos.
- `Idempotency-Key` (fuera MVP; header ignorado).

## Actores

| Actor | Uso |
|-------|-----|
| **Usuario técnico integración** | Bearer + `X-Paq-Cliente`; único autorizado a POST maestros. |
| **Vendedor / cliente portal** | **Prohibido** en estos endpoints (403). |

## Reglas de negocio

1. **RN-01:** Solo tokens con `pw_integracion_importacion` procesan POST maestros.
2. **RN-02:** Sin `X-Paq-Cliente` válido (EMPRESAS_CONEXION) → error envelope, **sin** escritura en tablas de negocio.
3. **RN-03:** Content-Type `application/json`; cuerpo inválido → lote abortado.
4. **RN-04:** `modo` desconocido → lote abortado (422).
5. **RN-05:** Mensajes `respuesta` y `codigo` en errores = claves i18n (sin prosa SQL/stack).
6. **RN-06:** Rechazar payloads con SQL, nombres de tabla o DDL en propiedades del JSON.
7. **RN-07:** `Idempotency-Key` ignorado en MVP.
8. **RN-08:** `items.length` > 1500 → lote abortado (422).
9. **RN-09:** Log: `info` si `rechazados=0`; `warning` si parcial; `error` si abortado.
10. **RN-10:** Sin Bearer → 401; Bearer sin permiso → 403.

## Criterios de aceptación

- [ ] **CA-01:** Usuario técnico con permiso puede invocar al menos un endpoint maestros de prueba (stub o recurso real).
- [ ] **CA-02:** Usuario vendedor autenticado del portal recibe **403** en POST maestros.
- [ ] **CA-03:** Tenant inválido o ausente no escribe datos y devuelve envelope de error acordado.
- [ ] **CA-04:** Respuesta exitosa usa `error: 0` y estructura `resultado` del SPEC.
- [ ] **CA-05:** Ítem rechazado en delta no impide contabilizar `aplicados` en otros ítems (contrato común; verificado con handler stub o recurso mínimo).
- [ ] **CA-06:** `modo=delta` + `items:[]` → 200 no-op, sin error de lote.
- [ ] **CA-07:** `modo=snapshot` + `items:[]` → lote rechazado (422).
- [ ] **CA-08:** Recurso path inexistente → 404 envelope.
- [ ] **CA-09:** Más de 1500 ítems planos → 422.
- [ ] **CA-10:** `errores` truncado a 50 con indicación i18n en `respuesta` cuando corresponda.
- [ ] **CA-11:** Cada request exitoso o parcial genera fila log `maestro_import` consultable vía API logs existente.
- [ ] **CA-12:** Rate limit configurable documentado en TR.
- [ ] **CA-13:** Feature tests cubren 401, 403, tenant, snapshot vacío, delta vacío.
- [ ] **CA-14:** OpenAPI publica esquemas de lote/respuesta bajo tag Integración maestros.

## Escenarios Gherkin

```gherkin
Feature: Plataforma POST integración maestros

  Scenario: Token integración válido con tenant
    Given un usuario técnico con permiso "pw_integracion_importacion"
    And header "X-Paq-Cliente" con tenant activo
    When POST "/api/v1/integracion/maestros/articulos" con lote delta válido
    Then HTTP 200
    And envelope "error" es 0
    And "resultado.recurso" es "articulos"

  Scenario: Vendedor del portal rechazado
    Given un vendedor autenticado sin permiso de integración
    When POST "/api/v1/integracion/maestros/clientes"
    Then HTTP 403

  Scenario: Snapshot con lote vacío rechazado
    Given token de integración válido
    When POST con "modo" "snapshot" e "items" vacío
    Then HTTP 422
    And envelope "error" distinto de 0
```

## Supuestos explícitos

- El handler por recurso se registra en un catálogo interno enlazado al path `{recurso}`.
- Mismo token servirá después para transacciones (101-23) con otro prefijo URL.

## Preguntas abiertas (TR)

- Nombre exacto del comando artisan de provisión del usuario técnico.
- Implementación concreta del rate limit (middleware Laravel vs gateway).

## Riesgos de ambigüedad

- Bajo: contrato transversal cerrado en A1.

## Veredicto B1

**Lista para TR** — plataforma debe entregarse antes o en paralelo con handlers de HU-047/048.
