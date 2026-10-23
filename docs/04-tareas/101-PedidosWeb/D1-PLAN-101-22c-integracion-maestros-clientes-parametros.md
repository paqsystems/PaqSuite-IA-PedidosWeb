# Plan de implementación D1 — TR-22c Clientes, direcciones, contactos y parámetros

| Campo | Valor |
|-------|-------|
| **Fecha** | 2026-10-22 |
| **Parte** | D1 — `ai-planning-mode` |
| **C1** | [F-101-22-cierre-c1](F-101-22-cierre-c1-importacion-maestros-erp.md) — **Apto con observaciones** |
| **TR** | [TR-SPEC-101-22-integracion-maestros-clientes-parametros](TR-SPEC-101-22-integracion-maestros-clientes-parametros.md) |
| **HU / SPEC** | [HU-101-048](../../03-historias-usuario/101-PedidosWeb/HU-101-048-integracion-maestros-clientes-parametros.md) · [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Dependencias** | TR-22a plataforma y TR-22b catálogo implementados; vendedores, listas, condiciones y transportes disponibles |
| **Estado plan** | **Listo para Parte D** |

## Alcance entendido

Implementar los cuatro recursos restantes de TR-22c bajo el contrato transversal existente:

`clientes`, `clientes-direcciones`, `clientes-contactos` y `parametros`.

El alcance incluye:

- Upsert delta por clave natural y semántica PATCH: campos omitidos no pisan valores existentes.
- Clientes con replace por ítem, validación de FKs comerciales, `eliminarSiAusente` explícito y preservación de clientes ausentes del lote.
- Ignorar `codLogin`/`cod_login`; no crear usuarios web ni modificar `users`.
- Recortar `leyenda1`…`leyenda5` a 60 caracteres Unicode antes de persistir.
- Direcciones y contactos únicamente en `delta`; `snapshot` debe abortar el lote con HTTP 422 y `integracion.maestro.modoNoSoportado`.
- Direcciones con `habitual` normalizado a `S`/`N`.
- Contactos delta sin eliminación implícita de contactos omitidos.
- Parámetros restringidos a las claves `programa=PedidosWeb` del seed versionado; claves fuera de whitelist se rechazan por ítem sin abortar el lote.
- Persistencia de negocio exclusivamente mediante SP `pq_sp_integracion_maestros_*`.
- Cuatro paths OpenAPI literales y feature tests de los CA de HU-101-048.
- Sin cambios en frontend, UI de parámetros, mobile, Excel, transacciones ni tablas de pedidos/presupuestos.

## Fuentes leídas

- SPEC-101-22, §§1–8 y decisiones A1 `AMB-C-101-22-01…05`, `AMB-A-101-22-01…04`.
- HU-101-048, RN-01…RN-07, CA-01…CA-12 y escenarios Gherkin.
- TR-22c, §§1–8.
- C1 `F-101-22-cierre-c1-importacion-maestros-erp.md`.
- D1 de plataforma 22a y D1/implementación existente de catálogo 22b.
- `IntegracionMaestroOrchestrator`, `IntegracionMaestroRegistry`, `CatalogoMaestroHandler` y `MaestroSpRepository`.
- Modelos `PqPedidoswebCliente`, `PqPedidoswebClienteDireccionEntrega` y `PqPedidoswebClienteContacto`.
- `ParametrosGralTipoValor`, `PqParametrosGralPedidosWebSeeder` y seed `docs/backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json`.
- OpenAPI transversal existente y reglas de acceso por stored procedure, `NOLOCK` y envelope GEN-20.

## Impacto esperado

### Base de datos

Agregar a `backend/scripts/sql/integracion-maestros/00-deploy-all.sql` cuatro procedimientos idempotentes:

- `pq_sp_integracion_maestros_clientes_apply`
- `pq_sp_integracion_maestros_clientes_direcciones_apply`
- `pq_sp_integracion_maestros_clientes_contactos_apply`
- `pq_sp_integracion_maestros_parametros_apply`

Contrato operativo común: `batch_id`, tenant, modo, fecha de proceso, origen y payload del ítem, alineado con el repository/SP ya usado por 22b. Cada SP debe devolver `status`, `clave` y `error_code`, y registrar la clave natural en staging.

Mapeo previsto:

| Recurso | Tabla | Clave natural | Reglas SP |
|---|---|---|---|
| `clientes` | `pq_pedidosweb_clientes` | `cod_client` | Merge por campos presentes; validar `cod_vended`, `lista_precios`, `cod_condvta`, `cod_transpor`; no tocar `cod_login` |
| `clientes-direcciones` | `pq_pedidosweb_clientesde` | `cod_client \| id_de` | Solo delta; validar cliente; `habitual` → `S`/`N` |
| `clientes-contactos` | `pq_pedidosweb_clientescontactos` | `cod_client \| cod_contacto` | Solo delta; validar cliente; no borrar omitidos |
| `parametros` | `PQ_parametros_gral` | `Programa \| Clave` | Solo `Programa='PedidosWeb'` y clave whitelist; actualizar una única columna de valor; nunca DELETE ni vaciado |

Los SP deben usar `EXISTS` para validar referencias y `WITH (NOLOCK)` en tablas persistentes leídas. No se usarán listas runtime en `IN`/`whereIn`, ni `DROP`, `TRUNCATE` o recreación de tablas.

### Backend

Cambios previstos:

- Registrar cuatro recursos en `config/integracion_maestros.php`, usando handlers específicos:
  - `ClientesMaestroHandler`
  - `ClientesDireccionesMaestroHandler`
  - `ClientesContactosMaestroHandler`
  - `ParametrosMaestroHandler`
- Reutilizar `MaestroSpRepository` y el orchestrator transversal; no duplicar auth, tenant, envelope, rate limit ni logging.
- Extraer un normalizador común para leyendas Unicode y `habitual`, o ubicarlo en handlers si evita ampliar una abstracción fuera de esta TR.
- Asegurar que el handler de clientes elimine únicamente cuando el ítem trae `eliminarSiAusente=true` y el cliente no tiene pedidos referenciados; si está referenciado, devolver rechazo de ítem con código de negocio.
- Cargar la whitelist como configuración PHP versionada, generada una vez desde el seed; el runtime no leerá un archivo de documentación ni permitirá claves dinámicas.
- La whitelist debe incluir clave y tipo de valor esperado, no solo el nombre, para validar la columna de destino.
- Agregar claves i18n existentes o nuevas para parámetro no permitido, FK faltante, delete referenciado y modo no soportado.

### Frontend

Sin cambios. La consulta de parámetros continúa siendo solo lectura y no se agrega ABM.

### Tests

Feature, agrupados `@group integracion` y con skip explícito si no están disponibles SQL Server/SP:

- Clientes delta: upsert, idempotencia, PATCH parcial y `codLogin` ignorado.
- Baja de cliente: elimina contactos/direcciones cuando no hay referencias y rechaza sin borrar cuando existe deuda.
- Leyenda de 80 caracteres persistida con 60 caracteres Unicode.
- `eliminarSiAusente` elimina cliente sin pedidos y rechaza cliente referenciado.
- Direcciones delta y normalización `true/false` y `S/N` a `S/N`.
- Direcciones snapshot → HTTP 422 sin invocar SP.
- Contactos delta agrega/actualiza y preserva un contacto omitido.
- Parámetro whitelist actualiza el valor tipado.
- Parámetro fuera de whitelist produce `rechazados=1` y HTTP 200 en delta.
- Cantidad de `pq_pedidosweb_pedidoscabecera` y presupuestos sin cambios.
- OpenAPI contiene los cuatro paths literales y el envelope tipado.

Unit tests:

- Normalización Unicode de leyendas.
- Normalización de `habitual`.
- Resolución de columna de valor por tipo.
- Whitelist: clave permitida, clave no permitida y tipo incompatible.
- Construcción de claves naturales y mapeo de errores SP.

### Documentación

- Completar TR-22c con el anexo campo → columna y contrato de parámetros.
- Actualizar `runbook-usuario-integracion-maestros.md` con los cuatro recursos, ejemplos y orden recomendado.
- Actualizar OpenSpec/README y el cierre D cuando la implementación y los tests estén verificados.

### DevOps

- Entregar los cuatro SP dentro del script idempotente de instalación/update.
- Incluir la configuración PHP de whitelist y su fuente de generación en el PR.
- El despliegue requiere ejecutar el script de SP en la base objetivo autorizada.
- No ejecutar el script contra `Ankas_del_sur` ni contra una base ERP compartida sin autorización explícita.
- No ejecutar bootstrap destructivo.

## Decisiones D1

| ID | Decisión |
|---|---|
| D1-22c-01 | Direcciones y contactos no implementan finalize snapshot; cualquier `modo=snapshot` se rechaza antes del loop y antes de invocar SP. |
| D1-22c-02 | Clientes conserva semántica PATCH en delta y replace por ítem en snapshot; la ausencia del lote nunca elimina, solo `eliminarSiAusente=true` permite baja explícita. |
| D1-22c-03 | Las referencias de clientes para baja se validan dentro del SP con `EXISTS`; no se cargan pedidos en PHP. |
| D1-22c-03b | La baja explícita se ejecuta con transacción y bloqueo consistente: se verifica que no existan pedidos, deuda, cheques ni ventas detalladas; luego se eliminan contactos, direcciones y cliente, en ese orden. |
| D1-22c-04 | La whitelist se versiona en PHP con clave + tipo y se regenera desde el seed como operación de desarrollo; producción no depende de leer JSON en runtime. |
| D1-22c-05 | El handler de parámetros solo actualiza la columna correspondiente a `tipo_valor` y deja las columnas alternativas en `NULL`; no modifica `CAPTION`/`TOOLTIP`. |
| D1-22c-06 | Se adopta el contrato canónico de `PQ_PARAMETROS_GRAL`: `B`→`Valor_Bool`, `I`→`Valor_Int`, `N`→`Valor_Decimal`, `S`→`Valor_String`, `T`→`Valor_Text` y `D`→`Valor_DateTime`. No existe tipo `F`. |

## Orden de trabajo

1. Verificar columnas reales y restricciones de las cuatro tablas contra modelos, DDL canónico y referencias de pedidos.
2. Definir el anexo de mapeo camelCase → columnas y las firmas/errores de los cuatro SP.
3. Generar la whitelist PHP desde el seed y agregar test de integridad de cantidad, claves y tipos.
4. Implementar `clientes_apply`, incluyendo leyendas, FKs, `codLogin` ignorado y baja explícita.
5. Implementar direcciones y contactos delta, con normalización y validación de cliente.
6. Implementar `parametros_apply` con whitelist y columna tipada.
7. Registrar handlers/configuración y ajustar solo las abstracciones transversales necesarias.
8. Agregar los cuatro paths OpenAPI literales y ejemplos representativos.
9. Ejecutar unit/feature/OpenAPI; verificar que no cambie la cantidad de pedidos/presupuestos.
10. Actualizar runbook, TR, SPEC y cierre de Parte D con evidencia.

## Riesgos

| Riesgo | Mitigación |
|---|---|
| El seed puede cambiar en futuras versiones | Test comparativo y script one-shot reproducible; la whitelist se versiona y se revisa junto con el seed |
| Columnas reales difieren del modelo Laravel o del DDL dev | Verificación previa de metadata/DDL; si cambia contrato, emitir TR-update |
| Baja de cliente afecta pedidos por referencias no declaradas | `EXISTS` dentro del SP; si no puede demostrarse ausencia, rechazar el ítem |
| `cod_login` se actualiza accidentalmente por un merge genérico | Lista explícita de columnas permitidas y test de regresión |
| Leyendas Unicode se recortan por bytes o por caracteres incorrectos | Helper basado en caracteres Unicode y test con multibyte |
| Whitelist desactualizada respecto del seed | Test comparativo y script one-shot reproducible; no whitelist dinámica en producción |
| Cambios parciales de parámetros dejan valores en columnas incorrectas | SP valida tipo y limpia/actualiza exclusivamente la columna canónica |
| SP ejecutado en una base compartida sin instalación previa | Runbook de deploy separado; no ejecutar desde tests ni bootstrap destructivo |

## Tests a ejecutar

```powershell
cd backend
php artisan test tests/Unit/PedidosWeb/Integracion
php artisan test --group=integracion --filter=IntegracionMaestro
php artisan test tests/Feature/OpenApiDocumentationTest.php
```

Los tests que requieren SP real deben omitirse de forma explícita cuando SQL Server o los procedimientos no estén desplegados. No se ejecutará ningún bootstrap destructivo para satisfacer esa dependencia.

## Dudas / bloqueos

1. Confirmar la columna exacta de referencia de pedidos para la baja de clientes (`cod_cliente`) y si existen otras referencias que deban impedir el DELETE.
2. Confirmar si `id_de` y `cod_contacto` llegan como enteros o strings desde el ERP; se persistirán según el tipo real de tabla, sin alterar la clave natural.

Si la verificación de referencias cambia el contrato, detener la Parte D y emitir actualización de TR/SPEC. No resolver esa contradicción silenciosamente.

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR-22c:** **Sí**.
- No se implementan nuevos recursos de catálogo ni se modifican los handlers 22a/22b salvo ajustes transversales estrictamente necesarios.
- No se agrega UI, ABM de parámetros, menú, mobile, Excel ni API de transacciones.
- No se crean usuarios web ni se modifica `cod_login`.
- No se modifica `pq_pedidosweb_pedidoscabecera` ni tablas de presupuestos.
- Todo acceso de negocio nuevo queda restringido a stored procedures.
