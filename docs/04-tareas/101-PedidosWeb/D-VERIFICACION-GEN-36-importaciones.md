# Verificación del agente — GEN-36 Importaciones API asíncronas

| Campo | Valor |
|---|---|
| Alcance revisado | SPEC-101-22, SPEC-101-23 y SPEC-101-24; TR y código asociado a GEN-36 |
| Fecha | 2026-10-25 (rev. nota 2026-10-29) |
| Parte | F1 (alcance **conjunto** GEN-36) |
| Estado | **No aprobado** (conjunto) |

> **Nota 2026-10-29:** SPEC-101-22 cerró por su propio F1 acotado — [D-VERIFICACION-101-22-importacion-maestros-erp.md](D-VERIFICACION-101-22-importacion-maestros-erp.md) (**Aprobado con observaciones**). Este informe sigue vigente para **101-23** y cierre global del motor GEN-36.

## Resultado

**No aprobado para ejecutar el cierre F ni la unificación I del GEN completo.**

La rama contiene una implementación parcial del motor de recepción asíncrona y
del recurso `medidas`, pero la evidencia disponible no demuestra el alcance
completo de GEN-36 ni permite afirmar que todas las TR de importaciones estén
finalizadas.

## Evidencia revisada

- Rutas registradas:
  - `POST /api/v1/integracion/importaciones/{recurso}`
  - `GET /api/v1/integracion/importaciones/{importacionId}`
- Registry de maestros con `medidas`, clave natural `codMedida` y SP de
  aplicación/finalización.
- SP idempotentes de `medidas` en
  `backend/scripts/sql/integracion-maestros/00-deploy-all.sql`.
- Job `ProcessImportationMaestroJob` y adapters GEN-36.
- Request Postman local `medidas - recibir lote 1/1`.
- JSON de collection y environment válidos.
- Sintaxis PHP válida en controller, adapter, handler, configuración y script
  SQL revisados.
- Unitarios ejecutados:

```text
php artisan test tests/Unit/PedidosWeb/Integracion/ImportationMaestroDispatcherTest.php tests/Unit/PedidosWeb/Integracion/IntegracionMaestroOrchestratorTest.php tests/Unit/PedidosWeb/Integracion/IntegracionMaestroPayloadGuardTest.php

PASS — 4 tests, 7 assertions
```

## Hallazgos críticos

1. **GEN-36 no está completo:** SPEC-101-23 tiene **Parte D** (2026-10-29) pero falta **F1/QA ERP**; sus
   TR siguen en estado previo a D.
2. **Falta evidencia de ciclo asíncrono completo para `medidas`:** el test
   existente verifica solamente el `202` de recepción. No verifica GET
   intermedio, GET final, snapshot, snapshot vacío, baja física, reintento
   idempotente ni concurrencia `409`.
3. **Postman incompleto:** existe el POST de medidas, pero no la request de
   consulta de resultado ni las secuencias de idempotencia y concurrencia
   exigidas por TR-24c.
4. **OpenAPI incompleto para el alcance específico:** existe un path genérico
   `{recurso}`, pero no una anotación/schema explícita de
   `/api/v1/integracion/importaciones/medidas` ni evidencia de que el documento
   generado exponga el contrato específico de medidas.
5. La ejecución integrada quedó bloqueada por el entorno SQL Server: el proceso
   no produjo resultado después de más de 45 segundos y fue detenido. También
   se observó el warning de una clase de test inexistente:
   `IntegracionMaestrosClientesFeatureTest`.

## Advertencias

- La colección y los scripts de despliegue no fueron publicados remotamente,
  respetando la autorización disponible.
- No se ejecutó ningún bootstrap destructivo ni SQL contra una base compartida.
- La validación de JSON demuestra sintaxis, no ejecución contra un backend con
  worker y SQL Server activos.

## Tests

| Comando | Resultado |
|---|---|
| Unitarios dispatcher/orchestrator/payload guard | **4 passed, 7 assertions** |
| Validación JSON collection/environment | **OK** |
| `php -l` sobre archivos PHP/SQL revisados | **OK** |
| Suite feature de integración | **Sin resultado; bloqueada por conexión/entorno SQL** |
| `OpenApiDocumentationTest` | **Sin resultado; sin progreso durante 30 s, detenido** |

## Pendientes para F1 aprobable

- Implementar y probar SPEC-101-23 completo, o excluirlo explícitamente de un
  cierre acotado de SPEC-101-22/24.
- Agregar tests de GET intermedio/final, snapshot, snapshot vacío, baja,
  idempotencia y concurrencia.
- Completar collection Postman con GET, polling acotado, reintento y `409`.
- Completar OpenAPI y `api-docs.json` con el contrato tipado de `medidas`.
- Ejecutar feature tests y smoke Postman contra SQL Server dedicado, SP
  desplegados y worker con cola real.
- Resolver o eliminar el archivo de test que referencia una clase inexistente.

## Recomendación final

Mantener el GEN en **Verificando / Pendiente de Revisión**, sin crear un cierre
F como terminado y sin pasar a I. Repetir F1 después de completar los
pendientes y conservar la evidencia de ejecución.
