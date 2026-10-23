# HU-101-049 — Plataforma API importación transacciones ERP

Jira: [PIAPEDWEB-40](https://paqsystems.atlassian.net/browse/PIAPEDWEB-40)

| Campo | Valor |
|-------|-------|
| **ID** | HU-101-049-integracion-transacciones-plataforma |
| **SPEC origen** | [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) |
| **Épica** | APIs importación transacciones ERP → PedidosWeb |
| **Prioridad** | **Must** |
| **Estado** | Especificado |
| **B1** | Cerrado (2026-10-22) |
| **Dependencias** | GEN-19 tenancy · GEN-20 envelope · contrato común de SPEC-101-22 · SPEC-101-08 logs |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Auth Sanctum + `pw_integracion_importacion` | RN-01, CA-01, CA-02 |
| Header `X-Paq-Cliente` y contrato común de lote | RN-02…RN-05, CA-03…CA-05 |
| Cuatro rutas de transacciones | RN-06, CA-06 |
| Tope 1500 y errores máximos 50 | RN-07, CA-07 |
| Códigos HTTP 200/401/403/404/422 | RN-08, CA-01, CA-02, CA-08 |
| Rate limit 60 req/min | RN-09, CA-09 |
| Log `transaccion_import` | RN-10, CA-10 |
| OpenAPI tag Integración transacciones | CA-11 |
| Sin rutas de maestros | Fuera de alcance |

## Narrativa

Como **operador de integración / job ERP**,  
quiero **autenticarme con el token técnico del tenant y enviar lotes de transacciones con el contrato común**,  
para **actualizar los datos que consume el portal sin utilizar credenciales de vendedor**.

## Contexto funcional

Capa común de `POST /api/v1/integracion/transacciones/{recurso}`: resolución de tenant, autenticación, autorización, validación del sobre del lote, despacho por recurso, envelope GEN-20, rate limit y log. La escritura específica de stock/deuda/cheques e historial se cubre en HU-101-050 y HU-101-051.

## Alcance incluido

- Middleware de tenant, Bearer Sanctum y permiso `pw_integracion_importacion`.
- Rutas para `stock`, `deuda`, `cheques` e `historial-ventas`.
- Validación de `modo`, `fechaProceso`, `origen` opcional e `items`.
- Tope de **1500** ítems planos por request.
- Envelope con `recurso`, `modo`, `recibidos`, `aplicados`, `omitidos`, `rechazados` y `errores[]` con tope **50**.
- Éxito parcial HTTP **200** únicamente en `delta`; aborto de snapshot con HTTP **422**.
- Recurso desconocido → HTTP **404**.
- Registro `transaccion_import` con severidad `info`, `warning` o `error` según resultado.
- Publicación OpenAPI de las cuatro rutas.

## Fuera de alcance

- Mapeo y persistencia específica de stock, deuda, cheques e historial → HU-101-050 / HU-101-051.
- Rutas `/integracion/maestros/*` → SPEC-101-22.
- UI nueva, mobile, Excel e idempotencia.
- Cron dentro de PedidosWeb.

## Actores

| Actor | Uso |
|-------|-----|
| **Usuario técnico ERP** | Invoca POST con Bearer y `X-Paq-Cliente`. |
| **Vendedor del portal** | No puede invocar estas rutas; recibe **403**. |

## Reglas de negocio

1. **RN-01:** Solo un token con `pw_integracion_importacion` puede procesar POST de transacciones.
2. **RN-02:** Tenant ausente o inválido no escribe datos y devuelve envelope de error.
3. **RN-03:** El cuerpo debe ser JSON y respetar el contrato común del lote.
4. **RN-04:** `items.length` mayor a 1500 aborta el lote con 422.
5. **RN-05:** Los errores no exponen SQL, stack trace ni DDL; `respuesta` y `codigo` son claves i18n.
6. **RN-06:** Los slugs válidos son únicamente `stock`, `deuda`, `cheques` e `historial-ventas`.
7. **RN-07:** Un delta puede responder 200 con rechazos por ítem; un snapshot no puede responder éxito parcial.
8. **RN-08:** Sin Bearer → 401; Bearer sin permiso → 403; recurso desconocido → 404.
9. **RN-09:** El rate limit es 60 requests por minuto por token y tenant.
10. **RN-10:** El log usa `tipo=transaccion_import` y registra recurso, modo, `fechaProceso` y contadores.

## Criterios de aceptación

- [ ] **CA-01:** Usuario técnico con permiso invoca una ruta de transacciones y recibe envelope válido.
- [ ] **CA-02:** Vendedor autenticado del portal recibe **403** en las cuatro rutas.
- [ ] **CA-03:** Tenant ausente o inválido no modifica tablas de negocio.
- [ ] **CA-04:** Respuesta exitosa usa `error: 0` y todos los contadores definidos.
- [ ] **CA-05:** `items: []` en delta devuelve 200 no-op; snapshot vacío devuelve 422.
- [ ] **CA-06:** Se publican exactamente las cuatro rutas del SPEC y un slug desconocido devuelve 404.
- [ ] **CA-07:** Más de 1500 ítems devuelve 422 y `errores[]` no supera 50 elementos.
- [ ] **CA-08:** Snapshot abortado devuelve `error != 0`; delta parcial devuelve HTTP 200.
- [ ] **CA-09:** El límite de 60 requests/minuto por token+tenant está aplicado o parametrizado.
- [ ] **CA-10:** Cada request procesado genera log `transaccion_import` con la severidad correspondiente.
- [ ] **CA-11:** OpenAPI documenta las cuatro rutas, el tag y los esquemas compartidos.

## Escenarios Gherkin

```gherkin
Feature: Plataforma POST integración transacciones

  Scenario: Usuario técnico autorizado
    Given un usuario técnico con permiso "pw_integracion_importacion"
    And un header "X-Paq-Cliente" válido
    When realiza POST "/api/v1/integracion/transacciones/stock" con lote delta válido
    Then recibe HTTP 200
    And el envelope contiene "error" igual a 0

  Scenario: Vendedor no autorizado
    Given un vendedor autenticado del portal
    When realiza POST a una ruta de transacciones
    Then recibe HTTP 403

  Scenario: Recurso desconocido
    Given un token técnico válido
    When realiza POST "/api/v1/integracion/transacciones/inexistente"
    Then recibe HTTP 404
```

## Supuestos explícitos

- El mismo usuario técnico y permiso definidos para SPEC-101-22 se reutilizan en transacciones.
- La persistencia de cada recurso se realiza mediante los SP definidos en el SPEC.

## Preguntas abiertas (TR)

- Nombre exacto del comando de provisión del usuario técnico, si se reutiliza o se documenta desde 101-22.
- Implementación concreta del rate limit.

## Observaciones B1

- La HU cubre el contrato transversal del SPEC y deja la persistencia por recurso en HU-101-050/051.
- Los nombres finales de SP, el comando de provisión y la implementación concreta del rate limit quedan correctamente diferidos a TR.
- No se detectan ambigüedades bloqueantes para generar la TR.

## Veredicto B1

**Lista para TR** — plataforma transversal independiente de los handlers de dominio; puede implementarse antes o en paralelo.
