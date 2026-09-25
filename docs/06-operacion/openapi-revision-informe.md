# Informe de revision OpenAPI — PedidosWeb

Fecha: 2026-07-31
Fuente: rutas Laravel `api/v1` vs `storage/api-docs/api-docs.json` (L5-Swagger).

## Como usar este informe

1. Completa la columna **tagPropuesto** en el CSV (o en las tablas) con el nombre de grupo deseado en Swagger.
2. Devuelve el archivo corregido (o la tabla de renombres de tags) para aplicar nomenclatura + schemas.
3. Archivos companion: [openapi-revision-inventario.csv](./openapi-revision-inventario.csv) · [openapi-revision-inventario.json](./openapi-revision-inventario.json).
4. Regenerar: `php backend/scripts/generate-openapi-revision-report.php`.

## Resumen

| Severidad | Cantidad | Significado |
|-----------|----------|-------------|
| ok | 21 | Path documentado con estructura tipada de `resultado` (o envelope vacio valido) |
| alto | 10 | Documentado pero incompleto |
| critico | 34 | `ApiEnvelope` generico, body `{}`, o resultado sin propiedades |
| faltante | 32 | Ruta Laravel sin path OpenAPI |
| **Total** | **97** | Operaciones HTTP unicas `api/v1` |

## Tags actuales (nomenclatura a corregir)

Completa **Tag propuesto**. Ejemplo: `Visibilidad` → `Maestros`.

| Tag actual (OpenAPI) | Ops | Tag propuesto (completar) | Notas |
|----------------------|-----|---------------------------|-------|
| `(sin documentar)` | 32 |  | Asignar tag al documentar |
| `Auth` | 6 |  |  |
| `ChatAssistant` | 10 |  |  |
| `ExcelImport` | 7 |  |  |
| `Menu` | 1 |  |  |
| `PedidosWeb` | 30 |  | Puede subdividirse (Carga, Consultas, Config, ...) |
| `Pivots` | 3 |  |  |
| `Preferences` | 4 |  |  |
| `System` | 1 |  |  |
| `Visibilidad` | 3 |  | Candidato a Maestros / Catalogos |

## Inventario por tag

Columna **tagPropuesto**: vacia = usar el tag del grupo; o override por operacion.

### Tag: `Auth`

| Metodo | Path | Summary | tagPropuesto | Severidad | Resultado | Body | Accion |
|--------|------|---------|--------------|-----------|-----------|------|--------|
| POST | `/api/v1/auth/login` | Inicio de sesion |   | ok | estructura_tipada | body_tipado | OK |
| POST | `/api/v1/auth/logout` | Cierre de sesion |   | ok | envelope_vacio_ok | n/a | OK |
| GET | `/api/v1/auth/me` | Contexto de sesion del usuario autenticado |   | ok | estructura_tipada | n/a | OK |
| POST | `/api/v1/auth/password/change` | Cambio de contraseña autenticado |   | ok | estructura_tipada | body_tipado | OK |
| POST | `/api/v1/auth/password/forgot` | Solicitud de recuperacion de contraseña |   | ok | envelope_vacio_ok | body_tipado | OK |
| POST | `/api/v1/auth/password/reset` | Confirmacion de recuperacion de contraseña |   | ok | envelope_vacio_ok | body_tipado | OK |

### Tag: `ChatAssistant`

| Metodo | Path | Summary | tagPropuesto | Severidad | Resultado | Body | Accion |
|--------|------|---------|--------------|-----------|-----------|------|--------|
| GET | `/api/v1/chat-assistant/me/configuration` | Configuración personal activa del Chat Asistente IA |   | ok | estructura_tipada | n/a | OK |
| PUT | `/api/v1/chat-assistant/me/configuration` | Guardar configuración personal del Chat Asistente IA |   | ok | estructura_tipada | body_tipado | OK |
| PATCH | `/api/v1/chat-assistant/me/configuration/status` | Habilitar o deshabilitar la configuración del Chat Asiste... |   | ok | estructura_tipada | body_tipado | OK |
| GET | `/api/v1/chat-assistant/me/configurations` | Listar configuraciones personales del Chat Asistente IA |   | alto | sin_schema_json | n/a | Completar estructura de resultado o examples |
| POST | `/api/v1/chat-assistant/me/configurations` | Crear configuración personal del Chat Asistente IA |   | alto | sin_schema_json | body_tipado | Completar estructura de resultado o examples |
| DELETE | `/api/v1/chat-assistant/me/configurations/{credentialId}` | Eliminar configuración personal del Chat Asistente IA |   | alto | sin_schema_json | n/a | Completar estructura de resultado o examples |
| PUT | `/api/v1/chat-assistant/me/configurations/{credentialId}` | Actualizar configuración personal del Chat Asistente IA |   | alto | sin_schema_json | body_tipado | Completar estructura de resultado o examples |
| PATCH | `/api/v1/chat-assistant/me/configurations/{credentialId}/status` | Habilitar o deshabilitar una configuración específica |   | alto | sin_schema_json | body_tipado | Completar estructura de resultado o examples |
| POST | `/api/v1/chat-assistant/messages` | Enviar consulta al Chat Asistente IA |   | ok | estructura_tipada | body_tipado | OK |
| GET | `/api/v1/chat-assistant/providers` | Catálogo de proveedores IA activos |   | ok | estructura_tipada | n/a | OK |

### Tag: `ExcelImport`

| Metodo | Path | Summary | tagPropuesto | Severidad | Resultado | Body | Accion |
|--------|------|---------|--------------|-----------|-----------|------|--------|
| GET | `/api/v1/excel-import/historial` | Historial de importaciones Excel |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/excel-import/lotes/{guidImportacion}` | Detalle de lote de importacion |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| POST | `/api/v1/excel-import/lotes/{guidImportacion}/cancelar` | Cancelar lote de importacion |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/excel-import/procesos/{codigoProceso}` |  |   | alto | sin_schema_json | n/a | Completar estructura de resultado o examples |
| POST | `/api/v1/excel-import/procesos/{codigoProceso}/archivo/hojas` | Listar hojas de un archivo Excel |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| POST | `/api/v1/excel-import/procesos/{codigoProceso}/lotes` | Crear lote de importacion Excel |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/excel-import/procesos/{codigoProceso}/plantilla` |  |   | alto | sin_schema_json | n/a | Completar estructura de resultado o examples |

### Tag: `Menu`

| Metodo | Path | Summary | tagPropuesto | Severidad | Resultado | Body | Accion |
|--------|------|---------|--------------|-----------|-----------|------|--------|
| GET | `/api/v1/user/menu` | Menu autorizado del usuario |   | ok | estructura_tipada | n/a | OK |

### Tag: `PedidosWeb`

| Metodo | Path | Summary | tagPropuesto | Severidad | Resultado | Body | Accion |
|--------|------|---------|--------------|-----------|-----------|------|--------|
| GET | `/api/v1/articulos` | Autocompletar articulos para carga de comprobantes |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/clientes/{codCliente}/cabecera-inicial` | Inicializar cabecera de comprobante desde cliente (HU-101... |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| POST | `/api/v1/comprobantes/copiar` | Copiar comprobante existente a pedido o presupuesto |   | critico | ApiEnvelope_generico_sin_estructura | body_tipado | Crear ApiEnvelopeXxx con estructura de resultado |
| POST | `/api/v1/comprobantes/grabar` | Grabar pedido o presupuesto (contrato unificado) |   | critico | ApiEnvelope_generico_sin_estructura | body_object_vacio | Tipar request body + resultado |
| GET | `/api/v1/config/parametros` | Consulta de parametros generales (solo lectura) |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/config/parametros-carga` | Parametros Modifica* y flags UI para pantalla de carga |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/consultas/cheques` | Consulta cheques del cliente |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/consultas/detalle-pedidos` | Consulta detalle de pedidos (cabecera + renglon) |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/consultas/deuda` | Consulta deuda del cliente |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/consultas/historial-ventas` | Historial de ventas del cliente |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/consultas/pedidos-ingresados` | Consulta pedidos ingresados |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/consultas/pedidos-pendientes` | Consulta pedidos pendientes |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/consultas/presupuestos` | Consulta presupuestos |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/consultas/stock` | Consulta stock de articulos |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/dashboard/operativo` | Dashboard operativo PedidosWeb |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/integracion/logs` | Logs de integracion (mail, etc.) |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/motivos-cierre` | Catalogo de motivos de cierre de presupuesto |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| POST | `/api/v1/pedidos` | Alta de pedido |   | critico | ApiEnvelope_generico_sin_estructura | body_object_vacio | Tipar request body + resultado |
| POST | `/api/v1/pedidos/carga/asistente/turn` | Turno del Asistente IA en carga de pedidos/presupuestos |   | critico | estructura_tipada | body_object_vacio | Tipar request body + resultado |
| DELETE | `/api/v1/pedidos/{cod_pedido}` | Eliminar pedido en estado 0 |   | ok | envelope_vacio_ok | n/a | OK |
| GET | `/api/v1/pedidos/{cod_pedido}` | Obtener pedido con detalle |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| PUT | `/api/v1/pedidos/{cod_pedido}` | Modificar pedido existente |   | critico | ApiEnvelope_generico_sin_estructura | body_object_vacio | Tipar request body + resultado |
| POST | `/api/v1/pedidos/{cod_pedido}/edicion/actividad` | Renovar timestamp de actividad en edicion |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| POST | `/api/v1/pedidos/{cod_pedido}/edicion/cancelar` | Cancelar edicion y volver a estado 0 |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| POST | `/api/v1/pedidos/{cod_pedido}/edicion/iniciar` | Iniciar edicion concurrente del pedido |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| POST | `/api/v1/presupuestos` | Alta de presupuesto |   | critico | ApiEnvelope_generico_sin_estructura | body_object_vacio | Tipar request body + resultado |
| PUT | `/api/v1/presupuestos/{cod_pedido}` | Modificar presupuesto existente |   | critico | ApiEnvelope_generico_sin_estructura | body_object_vacio | Tipar request body + resultado |
| POST | `/api/v1/presupuestos/{cod}/cerrar` | Cerrar presupuesto por rechazo |   | critico | ApiEnvelope_generico_sin_estructura | body_tipado | Crear ApiEnvelopeXxx con estructura de resultado |
| GET | `/api/v1/presupuestos/{cod}/tratativas` | Listar tratativas de un presupuesto |   | critico | ApiEnvelope_generico_sin_estructura | n/a | Crear ApiEnvelopeXxx con estructura de resultado |
| POST | `/api/v1/presupuestos/{cod}/tratativas` | Registrar tratativa en presupuesto |   | critico | ApiEnvelope_generico_sin_estructura | body_tipado | Crear ApiEnvelopeXxx con estructura de resultado |

### Tag: `Pivots`

| Metodo | Path | Summary | tagPropuesto | Severidad | Resultado | Body | Accion |
|--------|------|---------|--------------|-----------|-----------|------|--------|
| POST | `/api/v1/pivots/consultas/{consultaId}/data` |  |   | alto | sin_schema_json | body_sin_properties | Completar estructura de resultado o examples |
| GET | `/api/v1/pivots/consultas/{consultaId}/metadata` |  |   | alto | sin_schema_json | n/a | Completar estructura de resultado o examples |
| POST | `/api/v1/pivots/consultas/{consultaId}/validate-structure` |  |   | alto | sin_schema_json | body_sin_properties | Completar estructura de resultado o examples |

### Tag: `Preferences`

| Metodo | Path | Summary | tagPropuesto | Severidad | Resultado | Body | Accion |
|--------|------|---------|--------------|-----------|-----------|------|--------|
| GET | `/api/v1/users/me/preferences` | Preferencias del usuario autenticado |   | ok | estructura_tipada | n/a | OK |
| PATCH | `/api/v1/users/me/preferences` | Actualizar preferencia abrir en nueva pestaña |   | ok | estructura_tipada | body_tipado | OK |
| PATCH | `/api/v1/users/me/preferences/locale` | Actualizar idioma del usuario |   | ok | estructura_tipada | body_tipado | OK |
| PATCH | `/api/v1/users/me/preferences/theme` | Actualizar tema del usuario |   | ok | estructura_tipada | body_tipado | OK |

### Tag: `System`

| Metodo | Path | Summary | tagPropuesto | Severidad | Resultado | Body | Accion |
|--------|------|---------|--------------|-----------|-----------|------|--------|
| GET | `/api/v1/health` | Health check del servicio |   | ok | estructura_tipada | n/a | OK |

### Tag: `Visibilidad`

| Metodo | Path | Summary | tagPropuesto | Severidad | Resultado | Body | Accion |
|--------|------|---------|--------------|-----------|-----------|------|--------|
| GET | `/api/v1/clientes` | Clientes visibles según perfil funcional |   | ok | estructura_tipada | n/a | OK |
| GET | `/api/v1/comprobantes/{id}` | Comprobante visible según perfil funcional |   | ok | estructura_tipada | n/a | OK |
| GET | `/api/v1/dashboard/resumen` | Resumen visible del dashboard |   | ok | estructura_tipada | n/a | OK |

### Tag: `(sin documentar)`

| Metodo | Path | Summary | tagPropuesto | Severidad | Resultado | Body | Accion |
|--------|------|---------|--------------|-----------|-----------|------|--------|
| GET | `/api/v1/admin/permisos` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| POST | `/api/v1/admin/permisos` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| POST | `/api/v1/admin/permisos/batch` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| DELETE | `/api/v1/admin/permisos/{id}` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| PUT | `/api/v1/admin/permisos/{id}` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/admin/roles` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| POST | `/api/v1/admin/roles` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| DELETE | `/api/v1/admin/roles/{id}` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| PUT | `/api/v1/admin/roles/{id}` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/admin/roles/{id}/atributos` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| PUT | `/api/v1/admin/roles/{id}/atributos` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/admin/usuarios` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/config/public` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/dashboard/resumen-mensual` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/excel-import/lotes/{guidImportacion}/columnas` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/excel-import/lotes/{guidImportacion}/export-errores` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/excel-import/lotes/{guidImportacion}/filas` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/excel-import/lotes/{guidImportacion}/filas/validas` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| POST | `/api/v1/excel-import/lotes/{guidImportacion}/procesar` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/grid-layouts` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| POST | `/api/v1/grid-layouts` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/grid-layouts/active` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| PUT | `/api/v1/grid-layouts/active` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| DELETE | `/api/v1/grid-layouts/{id}` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| PUT | `/api/v1/grid-layouts/{id}` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/pivot-configs` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| POST | `/api/v1/pivot-configs` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/pivot-configs/active` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| PUT | `/api/v1/pivot-configs/active` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| DELETE | `/api/v1/pivot-configs/{configId}` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| PUT | `/api/v1/pivot-configs/{configId}` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |
| GET | `/api/v1/presupuestos/{cod_pedido}` |  |   | faltante | path_ausente | n/a | Agregar path OpenAPI + schema resultado tipado |

## Hallazgos destacados (confirmados)

1. `POST /api/v1/comprobantes/grabar`: request con `cabecera`/`renglones` como object vacio; response `ApiEnvelope` generico.
2. `GET /api/v1/articulos`: path existe (tag PedidosWeb) pero sin estructura de `resultado`.
3. `GET /api/v1/clientes`: documentado bajo tag **Visibilidad** (no PedidosWeb).
4. `GET /api/v1/config/parametros`: documentado con `ApiEnvelope` generico (sin shape de parametros).
5. Decenas de operaciones sin documentar (admin, grid-layouts, pivot-configs, excel staging, etc.).

## Regla de calidad

Si `resultado` transporta datos de negocio, OpenAPI MUST declarar schema tipado (no `ApiEnvelope` generico).
Regla Cursor: `.cursor/rules/openapi-resultado-estructura.mdc`.
