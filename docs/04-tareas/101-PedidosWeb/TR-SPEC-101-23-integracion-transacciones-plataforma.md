# TR-SPEC-101-23a — Plataforma API importación transacciones ERP

Jira: [PIAPEDWEB-43](https://paqsystems.atlassian.net/browse/PIAPEDWEB-43)

| Campo | Valor |
|-------|-------|
| **HU relacionada** | [HU-101-049](../../03-historias-usuario/101-PedidosWeb/HU-101-049-integracion-transacciones-plataforma.md) |
| **SPEC relacionada** | [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) |
| **Épica** | APIs importación transacciones ERP ([PIAPEDWEB-35](https://paqsystems.atlassian.net/browse/PIAPEDWEB-35)) |
| **Prioridad** | **Must** |
| **Dependencias** | GEN-19 tenancy · GEN-20 envelope · contrato común de SPEC-101-22 · SPEC-101-08 logs |
| **Estado** | **C1 apto con observaciones** — autoriza D1 |
| **Última actualización** | 2026-10-22 |

## 1) Objetivo y alcance

Implementar la capa transversal de las cuatro APIs:

```text
POST /api/v1/integracion/transacciones/{recurso}
```

### Incluido

- Middleware de tenant, `auth:sanctum` y ability `pw_integracion_importacion`.
- Registro de las rutas `stock`, `deuda`, `cheques` e `historial-ventas`.
- Validación común del lote y envelope GEN-20.
- Resolución del handler por recurso.
- Rate limit de 60 requests/minuto por token+tenant.
- Escritura de log `transaccion_import`.
- Esquemas y paths OpenAPI.
- Tests transversales de auth, tenant, contrato, límites y errores.

### Fuera de alcance

- Persistencia de tablas de stock, deuda, cheques e historial → TR-23b / TR-23c.
- Rutas de maestros → SPEC-101-22.
- UI, mobile, Excel, cron e `Idempotency-Key`.

## 2) Criterios de aceptación y verificación

| HU | Verificación TR |
|----|-----------------|
| CA-01 | Feature con usuario técnico y handler stub registrado → 200 y envelope válido |
| CA-02 | Feature con vendedor autenticado → 403 en las cuatro rutas |
| CA-03 | Tenant inválido/ausente → error sin invocar handler |
| CA-04 | Assert `error: 0` y contadores del resultado |
| CA-05 | Delta vacío → 200 no-op; snapshot vacío → 422 |
| CA-06 | Cuatro slugs registrados; slug desconocido → 404 |
| CA-07 | 1501 ítems → 422; `errores[]` máximo 50 |
| CA-08 | Snapshot fatal → 422; delta parcial → 200 |
| CA-09 | Rate limiter identificado en configuración y test aislado |
| CA-10 | Log con tipo, severidad, recurso, modo y contadores |
| CA-11 | OpenAPI publica cuatro paths y schemas compartidos |

## 3) Contrato HTTP

### 3.1 Headers

| Header | Regla |
|--------|-------|
| `Authorization` | Bearer Sanctum obligatorio |
| `X-Paq-Cliente` | Tenant obligatorio y válido |
| `Content-Type` | `application/json` |

Permiso:

```text
POST /api/v1/integracion/transacciones/{recurso}
ability: pw_integracion_importacion
```

### 3.2 Request común

```json
{
  "modo": "delta",
  "fechaProceso": "2026-10-22T12:00:00-03:00",
  "origen": "tango",
  "items": []
}
```

| Campo | Tipo | Regla |
|-------|------|-------|
| `modo` | string | `delta` o `snapshot` |
| `fechaProceso` | datetime | obligatorio, ISO-8601 |
| `origen` | string/null | opcional, longitud definida por contrato común |
| `items` | array | obligatorio, máximo 1500 elementos |

El historial acepta únicamente `delta`; `stock`, `deuda` y `cheques` aceptan `snapshot` y `delta`.

### 3.3 Respuesta común

```json
{
  "error": 0,
  "respuesta": "integracion.transaccion.ok",
  "resultado": {
    "recurso": "stock",
    "modo": "delta",
    "recibidos": 2,
    "aplicados": 1,
    "omitidos": 0,
    "rechazados": 1,
    "errores": [
      {
        "indice": 1,
        "clave": "A-2",
        "codigo": "integracion.transaccion.itemInvalido"
      }
    ]
  }
}
```

`errores` se limita a 50. Las claves `respuesta` y `codigo` son i18n; nunca contienen SQL, stack trace o DDL.

### 3.4 Matriz de errores

| Caso | HTTP | Código |
|------|------|--------|
| Sin Bearer | 401 | `auth.unauthenticated` |
| Sin ability | 403 | `integracion.transaccion.forbidden` |
| JSON/validación inválida | 422 | `integracion.transaccion.jsonInvalido` |
| Modo inválido | 422 | `integracion.transaccion.modoInvalido` |
| Snapshot vacío | 422 | `integracion.transaccion.snapshotVacio` |
| Snapshot no soportado | 422 | `integracion.transaccion.modoNoSoportado` |
| Más de 1500 ítems | 422 | `integracion.transaccion.loteDemasiadoGrande` |
| Recurso desconocido | 404 | `integracion.transaccion.recursoDesconocido` |
| Snapshot no aplicado atómicamente | 422 | clave i18n de aborto |

## 4) Diseño backend

| Componente | Responsabilidad |
|------------|-----------------|
| `IntegracionTransaccionLoteRequest` o request común equivalente | Validar sobre, modo, fecha y tope |
| `IntegracionTransaccionController` | Recibir slug y delegar |
| `IntegracionTransaccionRegistry` | Resolver solo los cuatro handlers |
| `IntegracionTransaccionOrchestrator` | Contadores, errores, atomicidad declarada por handler |
| `IntegracionTransaccionLogWriter` | Registrar `transaccion_import` |
| Handler de recurso | Validar/mutar el dominio; invocar SP |

El controller no ejecuta SQL ni accede directamente a tablas de negocio.

## 5) Rate limit y logging

- Limitar por identidad del token y tenant resuelto.
- Configurar 60 requests/minuto.
- `info`: lote aplicado sin rechazos.
- `warning`: delta parcial con rechazos.
- `error`: snapshot abortado o fallo de lote.
- Payload lógico mínimo: `recurso`, `modo`, `fechaProceso` y contadores.

## 6) OpenAPI

Agregar tag **Integración transacciones** y cuatro paths literales:

- `/api/v1/integracion/transacciones/stock`
- `/api/v1/integracion/transacciones/deuda`
- `/api/v1/integracion/transacciones/cheques`
- `/api/v1/integracion/transacciones/historial-ventas`

Schemas compartidos:

- `IntegracionTransaccionLoteRequest`
- `IntegracionTransaccionItemError`
- `IntegracionTransaccionLoteResultado`
- `ApiEnvelopeIntegracionTransaccionLote`

Cada path documenta Bearer, `X-Paq-Cliente`, 200, 401, 403, 404 y 422.

## 7) Tests

Crear o extender:

```text
tests/Feature/Api/PedidosWeb/IntegracionTransaccionPlatformTest.php
tests/Unit/PedidosWeb/Integracion/IntegracionTransaccionOrchestratorTest.php
```

Cubrir 401, 403, tenant inválido, delta vacío, snapshot vacío, slug desconocido, tope 1500, truncado de errores, envelope y logging. Usar handler fake/stub para no acoplar esta TR a los SP de TR-23b/23c.

## 8) Plan de tareas

1. Request común, códigos i18n y envelope.
2. Registry, controller y rutas.
3. Middleware/ability y rate limit.
4. Log writer.
5. OpenAPI.
6. Tests transversales.

## 9) Riesgos y decisiones para C1

- Confirmar reutilización exacta de clases comunes de 101-22 frente a un adapter de transacciones.
- Confirmar nombres finales de clases antes de D1; no cambia el contrato.
- Mantener la atomicidad de snapshot en la TR de cada recurso.

### Checklist normas transversales

- [ ] Endpoints nuevos con policy/middleware en código.
- [ ] Matriz endpoint ↔ permiso actualizada.
- [ ] OpenAPI en `/api/documentation` coherente con código y matriz.
- [x] 401 y 403 documentados por operación protegida.
- [x] Envelope JSON con `error` entero y `resultado` objeto.
- [x] `X-Paq-Cliente` documentado.
- [ ] Tests API incluyen 401 y 403.
- [x] Sin ampliación de alcance fuera de SPEC/HU/TR.

## 10) Veredicto C1

**Apto con observaciones para D1.** Las observaciones son de cableado técnico y no cambian el contrato funcional; D1 debe cerrar los nombres concretos y verificar la matriz/OpenAPI.
