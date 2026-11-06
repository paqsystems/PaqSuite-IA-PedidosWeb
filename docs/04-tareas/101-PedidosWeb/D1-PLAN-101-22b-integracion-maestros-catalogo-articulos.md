# Plan de implementación D1 — TR-22b Catálogo, artículos y precios

| Campo | Valor |
|-------|-------|
| **Fecha** | 2026-10-21 |
| **Parte** | D1 — `ai-planning-mode` |
| **C1** | [F-101-22-cierre-c1](F-101-22-cierre-c1-importacion-maestros-erp.md) — **Apto con observaciones** |
| **TR** | [TR-SPEC-101-22-integracion-maestros-catalogo-articulos](TR-SPEC-101-22-integracion-maestros-catalogo-articulos.md) |
| **HU / SPEC** | [HU-101-047](../../03-historias-usuario/101-PedidosWeb/HU-101-047-integracion-maestros-catalogo-articulos.md) · [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Dependencia** | TR-22a plataforma implementada |
| **Estado plan** | **Parte E verificada** — pendiente F1 |

## Alcance entendido

Reemplazar los stubs de plataforma por handlers y stored procedures para los diez recursos:

`provincias`, `condventa`, `transportes`, `perfiles`, `vendedores`, `listaprecios`, `escalas`, `articulos`, `listaprecios-articulos` y `descuentocantidad`.

El alcance incluye:

- Upsert delta por clave natural, preservando columnas omitidas.
- Snapshot de catálogos chicos mediante `*_finalize_snapshot` separado: eliminar ausentes sin referencias; devolver `omitidos` cuando una referencia impida borrar.
- Snapshot de `escalas` como universo de `valores[]` por cabecera.
- Snapshot de `vendedores` y `articulos` sin borrar ausentes; `eliminarSiAusente` explícito por ítem.
- Recursos solo delta (`listaprecios-articulos`, `descuentocantidad`) rechazados a nivel lote con `modoNoSoportado`.
- Persistencia exclusivamente mediante SP SQL Server; ningún Eloquent/Query Builder contra tablas de negocio desde handlers.
- Diez paths POST literales en OpenAPI, reutilizando envelope y schemas de 22a.

Fuera de alcance: clientes, direcciones, contactos, parámetros (22c), transacciones 101-23, UI, Excel y cambios de esquema destructivos.

## Fuentes leídas

- SPEC-101-22, §1, §3, §4, §6 y §7.
- HU-101-047, reglas RN-01…RN-05 y CA-01…CA-10.
- TR-22b, especialmente §3.1, §3.2, §4, §5 y §7.
- C1: `C1-M-101-22-02` (finalize snapshot) y `C1-M-101-22-03` (mapeo completo).
- Modelos existentes `PqPedidoswebProvincia`, `CondicionVenta`, `Transporte`, `Perfil`, `Vendedor`, `ListaPrecios`, `Escalas*`, `Articulo`, `ListaPreciosArticulo` y `DescuentoCantidad`.
- `PedidosWebDevSchemaBootstrap.php` y DDL canónico de artículos.
- Plataforma 22a: registry, handler interface, orchestrator, request, controller y OpenAPI.
- Reglas de acceso a datos SP, `WHERE IN`, `NOLOCK` y estructura OpenAPI.

## Impacto esperado

### Base de datos

Crear `backend/scripts/sql/integracion-maestros/` con scripts idempotentes `CREATE OR ALTER PROCEDURE`.

Familias:

- `provincias_apply` + `provincias_finalize_snapshot`
- `condventa_apply` + `condventa_finalize_snapshot`
- `transportes_apply` + `transportes_finalize_snapshot`
- `perfiles_apply` + `perfiles_finalize_snapshot`
- `listaprecios_apply` + `listaprecios_finalize_snapshot`
- `vendedores_apply`
- `escalas_apply` + `escalas_detalle_apply` + `escalas_finalize_snapshot`
- `articulos_apply`
- `listaprecios_articulos_apply`
- `descuentocantidad_apply`

`00-deploy-all.sql` debe crear primero la tabla técnica de staging y sus índices de forma idempotente, y luego los procedimientos con `CREATE OR ALTER`. No debe incluir `DROP`, `TRUNCATE` ni recreación de tablas de negocio.

Firmas:

- `@modo`, `@fecha_proceso`, `@clave natural`, columnas de negocio explícitas y `@eliminar_si_ausente` cuando aplique.
- `escalas_detalle_apply` recibe `@cod_escala`, `@cod_valor`, `@desc_valor`.
- Los finalize reciben `@batch_id`; no se expanden miles de parámetros en `IN` ni se depende de la persistencia de una conexión para tablas temporales.
- La tabla técnica de staging contiene `batch_id`, tenant, recurso, clave natural, `fecha_proceso`, origen y estado. No contiene el payload comercial final ni se expone por API.
- Lecturas de tablas físicas en SQL Server llevan `WITH (NOLOCK)` según regla 75.
- Referencias para delete se validan con `EXISTS`/joins, no con `WHERE IN` de listas runtime.

Los SP deben mapear errores de FK/referencia a códigos que el adapter convierta en `integracion.maestro.fkFaltante` o `integracion.maestro.deleteReferenciado`.

### Backend

Nuevos componentes:

- `Repositories/*MaestroSpRepository.php`, uno por recurso o un adapter tipado por familia.
- `Handlers/*MaestroHandler.php` para los diez recursos.
- Normalizadores comunes para camelCase, booleanos `true/false` y `S/N`, `codLogin` ignorado y claves naturales.
- Excepción/adaptador de errores SQL → `IntegracionMaestroItemResult::rejected(...)` u `omitted(...)`.

Cambios:

- `config/integracion_maestros.php`: reemplazar stubs por handlers reales y declarar `supports_snapshot`.
- Extender `IntegracionMaestroHandlerInterface` con `finalizeSnapshot(...)` y una operación de contexto de lote; el orchestrator debe invocarla después del loop solo para catálogos chicos y escalas.
- Mantener `listaprecios-articulos` y `descuentocantidad` con `supports_snapshot=false`.
- Corregir el conteo de errores parciales para que solo cuente errores rechazados y preserve `omitidos`.
- El handler de `vendedores` no escribe `cod_login`.
- El handler de `perfiles` no modifica parámetros de pedidos.
- `articulos`: normalizar `especial` a bit, `stockeable` y `equivalenciaVentas`; aplicar `eliminarSiAusente` solo para el ítem indicado.
- `escalas`: procesar cabecera y valores, y finalizar el universo de detalle de cada cabecera enviada.

### Frontend

Sin cambios.

### Tests

Tests feature agrupados `@group integracion`, con skip explícito si faltan SQL Server/SP:

- provincias delta upsert e idempotencia;
- condventa snapshot elimina ausente no referenciado y omite referido;
- vendedores ignora `cod_login`;
- escalas cabecera + dos detalles y snapshot elimina detalle ausente;
- artículos delta parcial y `eliminarSiAusente` con/sin referencias;
- listaprecios-articulos snapshot → 422;
- descuentocantidad delta por `(cod_articu, cantidad)`;
- OpenAPI contiene diez paths literales;
- cantidad de `pq_pedidosweb_pedidoscabecera` no cambia.

Unit tests:

- mapeo y normalización por recurso;
- firma/argumentos de repositories;
- clasificación de error SQL como rechazado u omitido;
- finalize snapshot con claves del lote;
- no escritura de `cod_login`.

Fixture E ejecutado en `paqsystems_pedidosweb_demo` mediante un usuario técnico
mínimo (`integracion.erp.mvp`) provisionado directamente con rol, permiso y
ability de integración. La suite dejó de depender del seed MVP completo.

### Documentación

- Completar TR-22b con anexo de mapeo camelCase → columnas y firma de cada SP.
- Actualizar runbook de integración con recursos disponibles y requisito de deploy de SP.
- Documentar orden de instalación de `00-deploy-all.sql`.
- Actualizar OpenSpec/README al cerrar Parte D y registrar el comentario SDD correspondiente.

### DevOps

- Entregar scripts SP en el PR.
- En servidor dedicado, ejecutar el script idempotente en orden de instalación/update.
- No ejecutar scripts en `Ankas_del_sur` ni en una base ERP compartida sin autorización explícita.
- No ejecutar bootstrap destructivo.

## Decisiones D1

| ID | Decisión |
|----|----------|
| D1-22b-01 | Snapshot catálogo chico usa SP `*_finalize_snapshot` separado; evita mezclar delete de ausentes con el upsert por ítem. |
| D1-22b-02 | Los SP reciben columnas explícitas; payload JSON no se pasa como SQL libre. |
| D1-22b-03 | Cada request obtiene un `batch_id` único; `apply` registra las claves en staging y `finalize` opera exclusivamente sobre ese batch. `fechaProceso` y `origen` quedan como metadatos, no como identificador de concurrencia. |
| D1-22b-04 | El orchestrator conserva el loop por ítem y agrega finalize por handler; no se realiza `finalize` para recursos solo delta. |
| D1-22b-05 | Las referencias para delete se resuelven dentro del SP con `EXISTS`; no se cargan colecciones en PHP ni se usa `whereIn`. |
| D1-22b-06 | Un error de un ítem no aborta delta; un fallo de infraestructura/SP aborta el lote y se registra como error. |

## Orden de trabajo

1. Definir anexo de mapeo y contratos SP a partir de modelos/DDL.
2. Extender interfaz/orchestrator para finalize snapshot y clasificación de errores SQL.
3. Crear scripts SQL de provincias, condventa, transportes, perfiles y listas.
4. Implementar repositories y handlers de esos cinco catálogos; smoke delta/snapshot.
5. Implementar vendedores y artículos con replace por ítem y `eliminarSiAusente`.
6. Implementar escalas cabecera/detalle y finalize de valores.
7. Implementar listas-artículos y descuento-cantidad solo delta.
8. Sustituir registry/configuración de stubs.
9. Agregar diez paths OpenAPI literales y ejemplos de request/response.
10. Ejecutar unit/feature/integración y actualizar documentación/cierre D.

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Tablas reales difieren del bootstrap dev | Validar cada firma contra modelo/DDL canónico y marcar incompatibilidades antes del deploy. |
| Delete snapshot elimina datos referenciados por lógica no declarada | SP exige `EXISTS` sobre referencias conocidas; si no puede probarse ausencia, devuelve omitido. |
| SP finalize no distingue ejecuciones concurrentes | `batch_id` único por request, índice por tenant/recurso/batch y finalize acotado al batch; no se reutiliza `fechaProceso + origen` como identidad. |
| Escalas detalle se elimina fuera de su cabecera | Finalize acotado a cada `cod_escala` enviado, nunca al catálogo global. |
| Error SQL filtrado expone stack | Adapter devuelve solo claves i18n y registra detalle técnico fuera del envelope. |
| Los stubs actuales tienen semántica distinta | Registry se actualiza atómicamente junto con handlers/tests; no quedan stubs activos en recursos reales. |

## Tests a ejecutar

```powershell
cd backend
php artisan test --group=integracion --filter=IntegracionMaestro
php artisan test tests/Unit/PedidosWeb/Integracion
php artisan test tests/Feature/OpenApiDocumentationTest.php
```

Los tests que requieran SP deben marcarse `@group integracion` y omitirse cuando no haya SQL Server/SP desplegados; los tests unitarios no deben depender de BD.

## Dudas / bloqueos

No hay bloqueos para iniciar D. La decisión de staging queda cerrada. Quedan verificaciones técnicas obligatorias durante D1:

- confirmar nombres/columnas de referencias de pedidos y artículos antes de habilitar deletes;
- confirmar columnas y retención de la tabla staging, incluyendo limpieza idempotente de batches finalizados o expirados;
- confirmar si el SQL Server objetivo soporta `CREATE OR ALTER PROCEDURE` en todos los ambientes soportados.

Si alguna verificación contradice el modelo o cambia la semántica de snapshot, detener D y emitir TR-update; no resolverla silenciosamente.

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR-22b:** **Sí**.
- No se implementan clientes, contactos, direcciones ni parámetros.
- No se crean tablas ni se agregan columnas.
- No se modifica frontend, menú, mobile, Excel ni transacciones ERP.
- Todo acceso de negocio nuevo queda restringido a stored procedures.
