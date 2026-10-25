# Plan de implementación D1 — SPEC-101-24 — Integración asíncrona de medidas ERP

| Campo | Valor |
|---|---|
| **Fecha** | 2026-10-25 |
| **Parte** | D1 — `ai-planning-mode` |
| **C1** | [F-101-24-cierre-c](F-101-24-cierre-c-importacion-medidas-erp.md) — **Apto con observaciones** |
| **TR** | [24a](TR-SPEC-101-24-integracion-medidas-plataforma.md) · [24b](TR-SPEC-101-24-integracion-medidas-maestro.md) · [24c](TR-SPEC-101-24-integracion-medidas-postman.md) |
| **HU / SPEC** | HU-101-052…054 · [SPEC-101-24](../../05-open-spec/101-PedidosWeb/SPEC-101-24-importacion-medidas-erp.md) |
| **Estado del plan** | **Listo para Parte D con decisiones técnicas explícitas** |
| **Orden** | 24a → 24b → 24c |

## Alcance entendido

Implementar el recurso `medidas` sobre la infraestructura GEN-36 ya presente:

- recepción `POST /api/v1/integracion/importaciones/medidas`;
- consulta `GET /api/v1/integracion/importaciones/{importacionId}`;
- persistencia asíncrona de lotes y payload original;
- job de procesamiento, estados, idempotencia y lock por tenant/recurso;
- upsert por `codMedida`;
- delta, snapshot y snapshot vacío;
- bajas físicas al finalizar snapshot;
- rechazo de ítems sin `codMedida` válido;
- seis contadores de operación;
- stored procedures, OpenAPI, tests y collection Postman.

No se implementan otros maestros, clientes anidados, UI, mobile, Excel,
transacciones ni cron dentro de PedidosWeb.

## Fuentes leídas

- SPEC-101-24 y cierre A1.
- HU-101-052, HU-101-053 y HU-101-054 con B1 cerrado.
- TR-101-24a, TR-101-24b y TR-101-24c con C1 apto con observaciones.
- `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`.
- `backend/routes/api.php`.
- `IntegracionMaestroController`.
- `IntegracionImportacionLoteRequest`.
- `ImportationMaestroDispatcher`.
- `ProcessImportationMaestroJob`.
- `ImportationMaestroDomainAdapter`.
- `IntegracionMaestroRegistry`.
- `config/integracion_maestros.php`.
- `PedidosWebOpenApiPaths.php` y `OpenApiGetsSchemas.php`.
- tests actuales de plataforma, dispatcher y orchestrator.
- `backend/scripts/sql/integracion-maestros/00-deploy-all.sql`.
- collection y environment locales de Postman.

## Estado actual observado

La rama ya contiene parcialmente:

- rutas genéricas de recepción y consulta;
- controller conectado al orchestrator de `paqsuite/laravel-core`;
- dispatcher y job asíncrono;
- adapter de dominio con contadores `added/updated/deleted` y errores;
- configuración de límite de payload de 5 MB y máximo de 50 errores;
- schemas OpenAPI genéricos de importación;
- handlers y SP de otros maestros.

No se observó todavía un handler/configuración/SP específico de `medidas`.
El trabajo debe completar y corregir el cableado existente, no duplicarlo.

## Decisiones D1

| ID | Decisión |
|---|---|
| D1-24-01 | Reutilizar controller, middleware, tenant, ability, envelope, dispatcher, job y orchestrator GEN-36 existentes. |
| D1-24-02 | Registrar `medidas` en `config/integracion_maestros.php` con handler específico y clave `codMedida`. |
| D1-24-03 | Recibir `idMedida` desde el ERP; `id_medida` no es identity. El upsert y la detección alta/modificación se realizan por `codMedida`, pero un cambio de `idMedida` se persiste. |
| D1-24-04 | Los ítems sin `codMedida` válido son `rechazados`, no incrementan `procesados` ni los seis contadores. |
| D1-24-05 | Mantener límite inicial de payload de 5 MB y máximo de 50 errores, ya presentes en configuración; documentar y probar. |
| D1-24-06 | La respuesta GET intermedia debe exponer contadores y `errores` como `null`; el resultado final expone los seis contadores y el detalle. |
| D1-24-07 | El procedimiento de snapshot debe aislarse por tenant, recurso y ciclo; no ejecutar bajas lote por lote. |
| D1-24-08 | La tabla no tiene referencias funcionales en el sistema; las medidas ausentes del snapshot se eliminan físicamente al finalizar. |

## Impacto esperado

### Base de datos

Crear scripts idempotentes en:

```text
backend/scripts/sql/integracion-maestros/
```

Agregar:

- `pq_sp_integracion_importaciones_medidas_apply`;
- `pq_sp_integracion_importaciones_medidas_finalize_snapshot`;
- staging técnico o integración con staging GEN-36 ya existente;
- validación de `cod_medida` y longitudes;
   - upsert sobre `pq_pedidosweb_medidas`;
- comparación de ausentes al finalizar snapshot.

El SP debe:

- recibir parámetros tipados;
- evitar SQL dinámico construido desde payload;
- usar `EXISTS`/JOIN para referencias;
- respetar `SqlServerIsolation::transaction()` en publicación fuerte;
- no usar `WHERE IN` con listas de tamaño incierto;
- no ejecutar `DROP`, `TRUNCATE` ni recreación de esquema.

No ejecutar scripts sobre `Ankas_del_sur` sin consentimiento explícito.

### Backend

#### Reutilizar

- `IntegracionMaestroController`;
- `IntegracionImportacionLoteRequest`;
- `ImportationMaestroDispatcher`;
- `ProcessImportationMaestroJob`;
- `ImportationMaestroDomainAdapter`;
- `IntegracionMaestroRegistry`;
- `ApiResponse`;
- `LogIntegracionService`/writer;
- middleware de integración y rate limiter.

#### Crear o tocar

```text
backend/config/integracion_maestros.php
backend/app/Services/PedidosWeb/Integracion/Maestros/Handlers/MedidasMaestroHandler.php
backend/app/Services/PedidosWeb/Integracion/Maestros/Repositories/MedidasMaestroSpRepository.php
backend/app/Services/PedidosWeb/Integracion/Maestros/IntegracionMaestroItemResult.php
backend/app/Services/PedidosWeb/Integracion/Maestros/ImportationMaestroDomainAdapter.php
backend/app/Http/Controllers/Api/V1/PedidosWeb/IntegracionMaestroController.php
backend/app/OpenApi/PedidosWebOpenApiPaths.php
backend/app/OpenApi/OpenApiGetsSchemas.php
backend/lang/*/integracion.php
```

Cambios funcionales obligatorios:

1. Registrar `medidas` y su handler.
2. Mapear `idMedida`, `codMedida` y `descMedida`.
3. Asegurar que un rechazo sin clave no se convierta en error de alta o
   modificación.
4. Asegurar que `procesados` coincida con la suma de los seis contadores.
5. Completar `recibidos`, estado y timestamps si el contrato GEN-36 los expone.
6. Devolver `null` en contadores/errores mientras el ciclo no sea final.
7. Mapear conflicto concurrente a HTTP 409 y envelope consistente.

### Frontend

Sin cambios. Es una integración servidor-a-servidor y no agrega pantalla.

### Tests

Crear o completar:

```text
backend/tests/Feature/Api/PedidosWeb/IntegracionImportacionMedidasPlatformTest.php
backend/tests/Feature/Api/PedidosWeb/IntegracionImportacionMedidasFeatureTest.php
backend/tests/Unit/PedidosWeb/Integracion/ImportationMedidasDomainAdapterTest.php
backend/tests/Unit/PedidosWeb/Integracion/ImportationMaestroDispatcherTest.php
backend/tests/Feature/OpenApiDocumentationTest.php
```

Reutilizar helpers de usuario técnico, tenant y token existentes.

### Postman

Actualizar únicamente:

```text
backend/storage/app/postman/integracion/PedidosWeb-Integracion.postman_collection.json
```

Agregar POST/GET de medidas, rechazo por `codMedida`, idempotencia, 409,
estado intermedio y resultado final. Validar sintaxis JSON y no publicar en
workspace remoto.

### Documentación

- Actualizar estado D1 en el cierre C.
- Completar firmas finales de SP en TR-24b.
- Documentar límite de 5 MB y 50 errores.
- Registrar en la evidencia D1 las decisiones cerradas sobre referencias e
  `idMedida`.
- Preparar cierre D/E/F sin modificar el alcance del SPEC.

### DevOps

- Verificar que la cola no use `QUEUE_CONNECTION=sync`.
- Confirmar worker, retry, timeout y backoff.
- Incluir scripts SP en install/update.
- No ejecutar deploy SQL ni bootstrap destructivo en bases compartidas.

## Orden de trabajo

1. Confirmar el contrato real de `paqsuite/laravel-core` para resultado
   intermedio, lock, idempotencia y job.
2. Confirmar DDL de `pq_pedidosweb_medidas` y documentar que
   `id_medida` no es identity.
3. Implementar las decisiones cerradas: no hay referencias funcionales y un
   cambio de `idMedida` para el mismo `codMedida` actualiza la fila.
4. Registrar `medidas` en registry/config y crear handler/repository.
5. Crear `apply` y `finalize_snapshot` mediante SP idempotentes.
6. Completar adapter de dominio y contadores.
7. Ajustar controller/GET para estado intermedio, resultado final y envelope.
8. Actualizar OpenAPI con schemas tipados de request y resultado.
9. Crear tests unitarios y feature con el entorno permitido.
10. Actualizar y validar la collection Postman.
11. Ejecutar pruebas D/E y documentar evidencia.

## Riesgos

| Riesgo | Mitigación |
|---|---|
| `id_medida` es PK física y el upsert usa `cod_medida` | `id_medida` no es identity; recibirlo del ERP y actualizarlo si cambia para el mismo código |
| Snapshot elimina una medida referenciada | Verificado que no hay referencias funcionales; eliminar ausentes al finalizar |
| GET actual no distingue estado intermedio | Adaptar mapper del resultado GEN-36 y agregar test explícito |
| Adapter actual cuenta ítems inválidos como errores de modificación | Test de `codMedida` ausente y resultado `rechazado` separado |
| Job síncrono en desarrollo | Verificar `QUEUE_CONNECTION` y ejecutar prueba real de cola |
| OpenAPI genérico o body sin propiedades | Crear schemas tipados de medidas y resultado |
| SP no desplegado en entorno | Separar tests unitarios de integración y documentar prerrequisito |
| Cambios actuales no pertenecen a esta tarea | Revisar diff antes de D y tocar solo archivos del alcance |

## Tests a ejecutar durante D/E

```powershell
cd backend
php artisan test tests/Unit/PedidosWeb/Integracion/ImportationMaestroDispatcherTest.php
php artisan test tests/Unit/PedidosWeb/Integracion/ImportationMaestroOrchestratorTest.php
php artisan test tests/Feature/Api/PedidosWeb/IntegracionImportacionMedidasPlatformTest.php
php artisan test tests/Feature/Api/PedidosWeb/IntegracionImportacionMedidasFeatureTest.php
php artisan test tests/Feature/OpenApiDocumentationTest.php
```

Con SQL Server dedicado y SP desplegados:

```powershell
php artisan test --group=integracion-medidas
```

La collection Postman se ejecutará con el environment local y worker activo.

## Dudas / bloqueos

| ID | Tema | Estado |
|---|---|---|
| D1-24-01 | Política para medida referenciada en snapshot | **Cerrado:** no existen referencias funcionales; eliminar ausentes al finalizar |
| D1-24-02 | `idMedida` diferente para mismo `codMedida` | **Cerrado:** recibir y actualizar `id_medida`; la unicidad continúa siendo `cod_medida` |
| D1-24-03 | Retención de payloads y errores | No bloquea D; fijar en configuración/runbook |
| D1-24-04 | Backend de cola y supervisor | Verificar ambiente antes de prueba E2E API |
| D1-24-05 | Unicidad de `importacionId` | Confirmar contrato GEN-36: mínimo tenant + recurso |

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR-24:** **Sí**.
- No se agregan otros maestros ni clientes anidados.
- No se modifica frontend, mobile, Excel o cron.
- No se publica Postman remotamente.
- No se ejecutan operaciones destructivas de base de datos.

## Veredicto D1

**Plan listo para Parte D.** D1-24-01 y D1-24-02 quedan cerrados con la
verificación del esquema y del uso actual del endpoint. La implementación debe
seguir el orden 24a → 24b → 24c.
