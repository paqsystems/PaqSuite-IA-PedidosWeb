# Plan de implementación D1 — SPEC-101-23 — Integración transacciones ERP

| Campo | Valor |
|-------|-------|
| **Fecha** | 2026-10-22 |
| **Parte** | D1 — `ai-planning-mode` |
| **C1** | [F-101-23-cierre-c1](F-101-23-cierre-c1-importacion-transacciones-erp.md) — **Apto con observaciones** |
| **TR** | [23a](TR-SPEC-101-23-integracion-transacciones-plataforma.md) · [23b](TR-SPEC-101-23-integracion-transacciones-saldos.md) · [23c](TR-SPEC-101-23-integracion-transacciones-historial-ventas.md) |
| **HU / SPEC** | HU-101-049…051 · [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) |
| **Estado del plan** | **Listo para Parte D**; mapeo opcional pendiente de cierre técnico en D1 |
| **Orden** | 23a → 23b → 23c |

## Alcance entendido

Implementar las cuatro APIs POST de integración ERP:

- Capa común de auth Sanctum, tenant `X-Paq-Cliente`, ability `pw_integracion_importacion`, envelope GEN-20, rate limit, logs y OpenAPI.
- Handlers y stored procedures de `stock`, `deuda` y `cheques`, con snapshot atómico y delta merge.
- Handler y stored procedure de `historial-ventas`, exclusivamente delta y upsert por `idGva53`.
- Tests unitarios, feature/API, OpenAPI y atomicidad.

No se implementan UI, mobile, Excel, cron, pedidos, maestros ni `Idempotency-Key`.

## Fuentes leídas

- SPEC-101-23 y cierre A1.
- HU-101-049, HU-101-050 y HU-101-051 con B1 cerrado.
- TR-23a, TR-23b y TR-23c con C1 apto con observaciones.
- `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md`.
- Plataforma existente 101-22:
  - `EnsureIntegracionImportacion`
  - `IntegracionMaestroLoteRequest`
  - `IntegracionMaestroOrchestrator`
  - `IntegracionMaestroRegistry`
  - `IntegracionMaestroHandlerInterface`
  - `IntegracionMaestroLogWriter`
  - `config/integracion_maestros.php`
  - `routes/api.php`
  - `PedidosWebOpenApiPaths.php`
  - tests de plataforma y handlers.
- Regla SQL Server de aislamiento: escrituras fuertes mediante `SqlServerIsolation::transaction()`; no `DB::transaction()` directo.

## Decisiones D1

| ID | Decisión |
|----|----------|
| D1-23-01 | Reutilizar middleware, ability, tenant, envelope, códigos i18n y helpers de tests de 101-22; no duplicar seguridad ni contrato GEN-20. |
| D1-23-02 | Crear namespace propio `Integracion\Transacciones` para registry, orchestrator, handlers y repositories; no mezclar handlers de maestros con transacciones. |
| D1-23-03 | Mantener un repository por recurso o adapter tipado; todo acceso de negocio pasa por SP. |
| D1-23-04 | Snapshot usa staging aislado por `batch_id + tenant + recurso`; primero valida todo el lote y luego publica el conjunto dentro de una transacción SQL fuerte. No se ejecuta `TRUNCATE`/`DROP` desde PHP. |
| D1-23-05 | Delta procesa ítems válidos, no borra ausentes y aplica solo la primera ocurrencia de una clave duplicada; las siguientes son `rechazados`. |
| D1-23-06 | La lista de claves naturales se mantiene en config/registry para que el orchestrator pueda detectar duplicados sin `whereIn()` ni colecciones de BD. |
| D1-23-07 | Los SP reciben parámetros tipados y columnas explícitas; el payload JSON no se concatena ni se interpreta como SQL. |
| D1-23-08 | `error` usa rangos del contrato transversal: validación 1000, autorización 3000, not found/conflicto 4000 e infraestructura 9000. |
| D1-23-09 | El log conserva `tipo=transaccion_import`; la severidad es `info` en éxito, `warning` en delta parcial y `error` en aborto snapshot/fallo de infraestructura. |
| D1-23-10 | En historial, `fechaEmision` es la fecha del comprobante; `fechaProceso` de carátula prevalece para todas las filas y se persiste en `fecha_proceso`. |

## Decisión funcional cerrada

Para historial de ventas, `fechaEmision` representa la fecha del comprobante. La `fechaProceso` de carátula representa el momento del proceso y prevalece para todas las filas; se persiste en `fecha_proceso`. Una eventual fecha de proceso por fila no reemplaza la fecha de carátula.

## Impacto esperado

### Base de datos

- Agregar scripts idempotentes en `backend/scripts/sql/integracion-transacciones/`.
- Crear, solo si no existe, staging técnico aislado por tenant/recurso/batch; sin eliminar tablas existentes.
- Crear/alterar los SP:
  - `pq_sp_integracion_transacciones_stock`
  - `pq_sp_integracion_transacciones_deuda`
  - `pq_sp_integracion_transacciones_cheques`
  - `pq_sp_integracion_transacciones_historial_ventas`
- Incluir procedimiento de finalización snapshot donde sea necesario.
- Usar `SqlServerIsolation::transaction()` para publicación atómica.
- Lecturas de validación SQL Server con `NOLOCK` solo donde no se requiera consistencia fuerte; referencias mediante `EXISTS`/JOIN, nunca listas runtime en `IN`.

### Backend

#### Reutilizar

- `EnsureIntegracionImportacion`.
- Configuración de ability/tenant.
- Envelope `ApiResponse`.
- `IntegracionMaestroLogWriter` o adapter común, documentando la excepción existente de logs si aplica.
- Helpers y seeds de usuarios técnicos de integración en tests.

#### Crear

```text
app/Http/Controllers/Api/V1/PedidosWeb/IntegracionTransaccionController.php
app/Http/Requests/PedidosWeb/IntegracionTransaccionLoteRequest.php
app/Services/PedidosWeb/Integracion/Transacciones/IntegracionTransaccionRegistry.php
app/Services/PedidosWeb/Integracion/Transacciones/IntegracionTransaccionOrchestrator.php
app/Services/PedidosWeb/Integracion/Transacciones/IntegracionTransaccionHandlerInterface.php
app/Services/PedidosWeb/Integracion/Transacciones/IntegracionTransaccionLoteContext.php
app/Services/PedidosWeb/Integracion/Transacciones/IntegracionTransaccionItemResult.php
app/Services/PedidosWeb/Integracion/Transacciones/IntegracionTransaccionProcessResult.php
app/Services/PedidosWeb/Integracion/Transacciones/IntegracionTransaccionLogWriter.php
app/Services/PedidosWeb/Integracion/Transacciones/Handlers/StockTransaccionHandler.php
app/Services/PedidosWeb/Integracion/Transacciones/Handlers/DeudaTransaccionHandler.php
app/Services/PedidosWeb/Integracion/Transacciones/Handlers/ChequesTransaccionHandler.php
app/Services/PedidosWeb/Integracion/Transacciones/Handlers/HistorialVentasTransaccionHandler.php
app/Services/PedidosWeb/Integracion/Transacciones/Repositories/StockTransaccionSpRepository.php
app/Services/PedidosWeb/Integracion/Transacciones/Repositories/DeudaTransaccionSpRepository.php
app/Services/PedidosWeb/Integracion/Transacciones/Repositories/ChequesTransaccionSpRepository.php
app/Services/PedidosWeb/Integracion/Transacciones/Repositories/HistorialVentasSpRepository.php
```

#### Tocar

- `routes/api.php`: cuatro POST bajo middleware existente.
- `PedidosWebServiceProvider.php`: bindings del registry/orchestrator.
- `PedidosWebOpenApiPaths.php` y schemas OpenAPI.
- `lang/es/integracion.php`: claves `integracion.transaccion.*`.
- Config de rate limit, si se reutiliza el limiter existente de 101-22.

### Frontend

Sin cambios. El SPEC es backend servidor-a-servidor.

### Tests

Crear:

```text
tests/Feature/Api/PedidosWeb/IntegracionTransaccionPlatformTest.php
tests/Feature/Api/PedidosWeb/IntegracionTransaccionSaldosTest.php
tests/Feature/Api/PedidosWeb/IntegracionTransaccionHistorialVentasTest.php
tests/Unit/PedidosWeb/Integracion/Transacciones/IntegracionTransaccionOrchestratorTest.php
tests/Unit/PedidosWeb/Integracion/Transacciones/TransaccionHandlersTest.php
```

Reutilizar `SeedsIntegracionMaestrosFeatureData` o extraer un helper común sin alterar el seed existente.

### Documentación

- Actualizar TR con firmas finales de SP y tabla campo a campo.
- Actualizar runbook de integración con las cuatro rutas y orden maestros → transacciones.
- Actualizar OpenAPI generado solo cuando se implemente D y se solicite regeneración.
- Registrar cierre D/E/F en los documentos correspondientes.

### DevOps

- El PR incluirá scripts SQL idempotentes y la secuencia de despliegue.
- Requiere ejecutar deploy de SP en base dedicada/entorno autorizado.
- No ejecutar bootstrap destructivo ni SQL de despliegue sobre `Ankas_del_sur` sin consentimiento explícito.
- Verificar `.env`/config de rate limit y usuario técnico.

## Orden de trabajo

1. Resolver la decisión de fecha de historial y anotar la resolución en TR-23c.
2. 23a: extraer/reutilizar contrato común, crear request, registry, orchestrator, rutas, logs, rate limit, OpenAPI y tests de auth/tenant.
3. 23b: confirmar esquema físico; escribir staging y SP de stock; implementar handler/repository y tests atómicos.
4. 23b: implementar deuda canónica y cheques; agregar pruebas de FKs, duplicados y campos prohibidos.
5. 23c: cerrar mapeo opcional y precedencia de fecha; implementar SP, handler y repository de historial.
6. Registrar los cuatro handlers y validar que no queden rutas ambiguas.
7. Ejecutar unitarios, feature tests, OpenAPI y pruebas de integración con SQL Server dedicado.
8. Actualizar documentación de cierre D y preparar E.

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Contrato común de 101-22 acoplado a maestros | Adapter de transacciones y tests de envelope compartidos; no duplicar auth |
| Snapshot parcial por error tardío | Staging por batch y publicación transaccional solo después de validación completa |
| Drift de columnas físicas | Inspección de DDL/modelo antes de escribir SP; detener D ante contradicción |
| Concurrencia de snapshots | `batch_id` único, tenant/recurso en staging e aislamiento fuerte al finalizar |
| `whereIn()` por FKs o claves | `EXISTS`/JOIN en SP; no cargar listas en PHP |
| Error SQL expuesto | Mapeo a códigos i18n y detalle solo en logs |
| Falta de SP en CI | Tests unitarios sin BD y feature de integración marcado `@group integracion` |

## Tests a ejecutar durante D/E

```powershell
cd backend
php artisan test tests/Unit/PedidosWeb/Integracion/Transacciones
php artisan test tests/Feature/Api/PedidosWeb/IntegracionTransaccionPlatformTest.php
php artisan test tests/Feature/Api/PedidosWeb/IntegracionTransaccionSaldosTest.php
php artisan test tests/Feature/Api/PedidosWeb/IntegracionTransaccionHistorialVentasTest.php
php artisan test tests/Feature/OpenApiDocumentationTest.php
```

Los tests que requieran SP/SQL Server se ejecutan únicamente con el entorno dedicado correspondiente.

## Dudas / bloqueos

| ID | Tema | Estado |
|----|------|--------|
| D1-23-01 | Precedencia fecha de fila vs carátula en historial | **Cerrado por decisión humana: prevalece carátula** |
| D1-23-02 | Columnas físicas de stock/cheques | Verificar en D1 técnico antes del SQL |
| D1-23-03 | Staging vs TVP | Se adopta staging técnico por batch; validar compatibilidad SQL Server en D1 |
| D1-23-04 | Reuso exacto del orchestrator de 101-22 | Adapter propio de transacciones; compartir solo infraestructura estable |

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR-23:** **Sí**.
- No se modifica frontend/mobile.
- No se importan maestros, pedidos ni presupuestos.
- No se agrega idempotencia ni cron.
- No se ejecutan operaciones destructivas de base de datos.

## Veredicto D1

**Plan listo para Parte D.** 23a y 23b pueden avanzar cuando se confirme el esquema físico; 23c ya tiene cerrada la precedencia de fechas y requiere completar únicamente el mapeo opcional en D1 técnico.
