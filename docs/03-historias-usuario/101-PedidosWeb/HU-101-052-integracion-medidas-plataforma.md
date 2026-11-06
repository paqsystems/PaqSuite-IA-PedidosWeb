# HU-101-052 — Plataforma de importación asíncrona de medidas

| Campo | Valor |
|---|---|
| **ID** | HU-101-052-integracion-medidas-plataforma |
| **SPEC origen** | [SPEC-101-24-importacion-medidas-erp.md](../../05-open-spec/101-PedidosWeb/SPEC-101-24-importacion-medidas-erp.md) |
| **Prioridad** | Must |
| **Estado** | B1 cerrado — lista para TR |
| **B1** | Enriquecida desde SPEC-101-24 |
| **Dependencias** | GEN-19, GEN-20, GEN-36 |

## Narrativa

Como operador de integración del ERP, quiero enviar lotes autenticados de
medidas y consultar su estado, para actualizar PedidosWeb sin esperar el
procesamiento completo.

## Trazabilidad SPEC

| Entregable / criterio del SPEC | Cobertura |
|---|---|
| Rutas POST y GET | Alcance, CA-03…CA-05 |
| Auth, tenant y ability | RN-03, CA-01…CA-02 |
| Respuesta 202 y procesamiento asíncrono | RN-02, CA-03 |
| GET sin resultados provisorios | RN-03, CA-04 |
| Idempotencia y concurrencia | RN-04…RN-06, CA-06…CA-07 |
| Aislamiento por tenant y auditoría | RN-07, CA-08 |

## Alcance

- Recepción `POST /api/v1/integracion/importaciones/medidas`.
- Consulta `GET /api/v1/integracion/importaciones/{importacionId}`.
- Bearer técnico, `X-Paq-Cliente` y ability de integración.
- Persistencia del lote antes de responder 202.
- Procesamiento asíncrono, estados, idempotencia y lock por tenant/recurso.

## Reglas de negocio

1. `importacionId` lo genera el ERP.
2. Un lote válido responde HTTP 202 y `error: 0`.
3. Mientras no exista resultado final, los contadores y errores del GET son
   `null`.
4. Un lote repetido con igual hash es idempotente.
5. Otro ciclo concurrente para el mismo tenant y recurso recibe HTTP 409.
6. El ERP debe detener los lotes posteriores después de un rechazo de ciclo.
7. Un estado o resultado de otro tenant nunca puede ser visible.

## Criterios de aceptación

- [ ] Token sin ability recibe 403 y Bearer ausente o inválido recibe 401.
- [ ] Tenant ausente o inválido no persiste el lote.
- [ ] La recepción válida devuelve 202 con el identificador y estado.
- [ ] El GET durante procesamiento devuelve contadores y errores `null`.
- [ ] El GET final devuelve el resultado completo.
- [ ] El reenvío idéntico no duplica ni reaplica el lote.
- [ ] La concurrencia incompatible devuelve 409.
- [ ] Se registra la recepción, procesamiento y resultado en logs.

## Escenario Gherkin

```gherkin
Feature: Recepción asíncrona de medidas

  Scenario: Recepción válida de un lote final
    Given un token técnico con "pw_integracion_importacion"
    And un tenant válido en "X-Paq-Cliente"
    When el ERP envía un lote de medidas con "ultimoEnvio" igual a true
    Then la respuesta es HTTP 202
    And el envelope contiene "error" igual a 0
    And el procesamiento continúa fuera de la solicitud HTTP

  Scenario: Consulta durante procesamiento
    Given un lote de medidas recibido y aún no finalizado
    When el ERP consulta su "importacionId"
    Then los contadores y "errores" son null

  Scenario: Ciclo concurrente rechazado
    Given un ciclo de medidas en procesamiento para el tenant
    When el ERP inicia otro ciclo para el mismo tenant y recurso
    Then la respuesta es HTTP 409
    And el ERP detiene el envío de lotes siguientes
```

## Fuera de alcance

- Mapeo de campos y operaciones sobre `pq_pedidosweb_medidas`.
- Otros recursos de maestros.
- UI de administración.
- Modificación de la collection de Postman, cubierta por HU-101-054.

## Preguntas para TR

- Backend de cola y configuración de retry/backoff.
- Límite de bytes y retención de payloads.
- Nombres finales y parámetros de los stored procedures técnicos.

## Supuestos explícitos

- El recurso `medidas` se resuelve mediante el registro existente de
  importaciones asíncronas.
- El ERP conserva el mismo `importacionId` durante todos los lotes del ciclo.
- Los mensajes de respuesta y error son claves i18n, no prosa técnica.

## Riesgos de ambigüedad

- La implementación debe distinguir reintento idempotente del mismo ciclo y
  un ciclo concurrente diferente.
- La retención y el tamaño de errores deben quedar definidos en TR antes de
  implementar.

## Veredicto B1

**Lista para TR**, con observaciones técnicas sobre cola, límites, retención y
stored procedures.
