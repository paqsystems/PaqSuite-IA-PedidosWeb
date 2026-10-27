# TR-SPEC-101-24a — Plataforma de importación asíncrona de medidas

| Campo | Valor |
|---|---|
| **HU relacionada** | [HU-101-052](../../03-historias-usuario/101-PedidosWeb/HU-101-052-integracion-medidas-plataforma.md) |
| **SPEC relacionada** | [SPEC-101-24](../../05-open-spec/101-PedidosWeb/SPEC-101-24-importacion-medidas-erp.md) |
| **Prioridad** | Must |
| **Estado** | C1 apto con observaciones — listo para D1 |
| **Dependencias** | GEN-19, GEN-20, GEN-36, `LogIntegracionService` |

## 1. Objetivo

Implementar la recepción y consulta asíncrona del recurso `medidas` sobre el
protocolo GEN-36 existente, sin procesar el maestro dentro del request HTTP.

## 2. Alcance

- Ruta `POST /api/v1/integracion/importaciones/medidas`.
- Ruta `GET /api/v1/integracion/importaciones/{importacionId}`.
- Validación de auth, tenant, carátula y lotes.
- Persistencia de cabecera, lote, hash y payload original.
- Despacho del job al recibir `ultimoEnvio=true`.
- Estados, idempotencia, lock y respuesta 202.
- OpenAPI y tests de plataforma.

Fuera de alcance: mapeo de columnas y SP específicos de medidas, cubiertos por
TR-101-24b, y modificación de la collection, cubierta por TR-101-24c.

## 3. Contrato

Headers obligatorios:

```text
Authorization: Bearer {integrationToken}
X-Paq-Cliente: {tenant}
Content-Type: application/json
```

La request debe validar:

- `importacionId` string no vacío.
- `modo` en `delta|snapshot`.
- `ultimoEnvio` boolean.
- `numeroLote` entero positivo.
- `fechaProceso` ISO-8601.
- `items` array.

La respuesta válida de recepción es HTTP 202 con `error: 0` y al menos:
`importacionId`, `estado`, `recurso`, `modo`, `numeroLote` y `ultimoEnvio`.

El GET intermedio devuelve `null` en `recibidos`, `procesados`, los seis
contadores y `errores`. Solo el resultado final completa esos datos.

## 4. Reglas técnicas

1. El lock se identifica por tenant + recurso.
2. Un ciclo distinto mientras existe otro ciclo activo devuelve HTTP 409.
3. El mismo lote, con el mismo hash, se responde idempotentemente.
4. El mismo número de lote con payload diferente devuelve error de conflicto.
5. Un lote posterior a `ultimoEnvio=true` se rechaza.
6. El estado consultado se filtra por tenant antes de devolver información.
7. El job debe ejecutarse con una cola real; `QUEUE_CONNECTION=sync` no es
   válido para este flujo.

## 5. Persistencia y clases

Reutilizar la infraestructura de importaciones asíncronas existente:

- `IntegracionMaestroController`.
- `IntegracionImportacionLoteRequest` o FormRequest equivalente.
- dispatcher/orchestrator de importaciones.
- tablas técnicas de cabecera y lotes.
- job de procesamiento.
- servicio de logs de integración.

Si faltan contratos técnicos, agregar repositorios por stored procedure para:

```text
pq_sp_integracion_importaciones_recibir_lote
pq_sp_integracion_importaciones_consultar
```

Los nombres finales deben quedar alineados con el contrato GEN-36 y no deben
introducir SQL de negocio en controllers o servicios.

## 6. OpenAPI

Documentar:

- POST fijo `/api/v1/integracion/importaciones/medidas`.
- GET `/api/v1/integracion/importaciones/{importacionId}`.
- Bearer y `X-Paq-Cliente`.
- HTTP 202, 401, 403, 409, 422 y 500.
- Schemas de request, recepción, estado intermedio y resultado final.

### 6.1 Autorización y envelope

Ambas operaciones son protegidas y deben documentar:

| Operación | Seguridad | Header | Permiso | Respuestas |
|---|---|---|---|---|
| POST recepción | Bearer/Sanctum | `X-Paq-Cliente` | `pw_integracion_importacion` | 202, 401, 403, 409, 422, 500 |
| GET resultado | Bearer/Sanctum | `X-Paq-Cliente` | `pw_integracion_importacion` | 200, 401, 403, 404, 500 |

Toda respuesta debe conservar el envelope GEN-20 con `error` entero,
`respuesta` string y `resultado` objeto; nunca `resultado: null`.

## 7. Tests

- 401 sin Bearer.
- 403 sin `pw_integracion_importacion`.
- Tenant inválido sin persistencia.
- Request válida devuelve 202.
- `ultimoEnvio=true` despacha el procesamiento.
- GET intermedio devuelve contadores y errores `null`.
- Reenvío idéntico es idempotente.
- Payload diferente para el mismo lote es rechazado.
- Ciclo concurrente devuelve 409.
- GET de otro tenant no revela información.

## 8. Plan de trabajo

| ID | Trabajo | Evidencia |
|---|---|---|
| 24a-01 | Validar ruta y middleware | Feature test auth/tenant |
| 24a-02 | Validar y persistir carátula/lote/hash | Test de recepción |
| 24a-03 | Integrar job y estados | Test de despacho |
| 24a-04 | Implementar lock e idempotencia | Tests 409/reintento |
| 24a-05 | Completar GET y OpenAPI | Test de envelope/documentación |
| 24a-06 | Integrar logs | Registro auditable |

## 9. Criterios de terminado

- [ ] Recepción 202 verificada.
- [ ] Consulta intermedia y final verificadas.
- [ ] Idempotencia y concurrencia verificadas.
- [ ] OpenAPI actualizado.
- [ ] Tests automatizados ejecutados.
- [ ] Sin acceso SQL de negocio fuera de SP.

### Checklist normas transversales

- [ ] Policy/middleware y matriz endpoint ↔ permiso actualizados.
- [ ] OpenAPI publicado con security, 401, 403 y `X-Paq-Cliente`.
- [ ] Envelope con `error` entero y `resultado` objeto.
- [ ] Tests API de 401 y 403.
- [ ] Sin ampliación de alcance fuera del SPEC/HU.
