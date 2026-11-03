<?php

namespace App\OpenApi;

/**
 * Paths OpenAPI — integración maestros (ejemplos snapshot + delta en Request body).
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/provincias",
 *     operationId="integracionMaestrosProvincias",
 *     summary="Importar provincias",
 *     description="Catálogo chico: **snapshot** = universo del lote; **delta** = upsert. Elegí el ejemplo en Request body.",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroProvinciasLoteRequest"),
 *             @OA\Examples(example="provinciasSnapshot", summary="Snapshot — universo del recurso", value={"importacionId": "ERP-MAE-PROV-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "snapshot", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codProvin": "B", "descripcion": "Buenos Aires"}, {"codProvin": "C", "descripcion": "Capital Federal"}}}),
 *             @OA\Examples(example="provinciasDelta", summary="Delta — novedades (upsert)", value={"importacionId": "ERP-MAE-PROV-002", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codProvin": "B", "descripcion": "Buenos Aires (actualizado)"}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/condventa",
 *     operationId="integracionMaestrosCondventa",
 *     summary="Importar condiciones de venta",
 *     description="**snapshot** o **delta** — ver ejemplos en Request body.",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroCondventaLoteRequest"),
 *             @OA\Examples(example="condventaSnapshot", summary="Snapshot — universo del recurso", value={"importacionId": "ERP-MAE-CV-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "snapshot", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codigo": 1, "descripcion": "Contado", "dias": 0}}}),
 *             @OA\Examples(example="condventaDelta", summary="Delta — upsert", value={"importacionId": "ERP-MAE-CV-002", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codigo": 2, "descripcion": "30 días", "dias": 30}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/transportes",
 *     operationId="integracionMaestrosTransportes",
 *     summary="Importar transportes",
 *     description="**snapshot** o **delta** — ver ejemplos en Request body.",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroTransportesLoteRequest"),
 *             @OA\Examples(example="transportesSnapshot", summary="Snapshot — universo del recurso", value={"importacionId": "ERP-MAE-TR-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "snapshot", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codigo": "TR01", "descripcion": "Retiro en planta"}}}),
 *             @OA\Examples(example="transportesDelta", summary="Delta — upsert", value={"importacionId": "ERP-MAE-TR-002", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codigo": "TR02", "descripcion": "Transporte express"}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/perfiles",
 *     operationId="integracionMaestrosPerfiles",
 *     summary="Importar perfiles",
 *     description="**snapshot** o **delta** — ver ejemplos en Request body.",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroPerfilesLoteRequest"),
 *             @OA\Examples(example="perfilesSnapshot", summary="Snapshot — universo del recurso", value={"importacionId": "ERP-MAE-PF-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "snapshot", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codPerfil": "P01", "descripcion": "Perfil estándar"}}}),
 *             @OA\Examples(example="perfilesDelta", summary="Delta — upsert", value={"importacionId": "ERP-MAE-PF-002", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codPerfil": "P02", "descripcion": "Perfil mayorista"}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/vendedores",
 *     operationId="integracionMaestrosVendedores",
 *     summary="Importar vendedores",
 *     description="**snapshot** (replace por ítem) o **delta** (upsert + `eliminarSiAusente`). Ver ejemplos.",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroVendedoresLoteRequest"),
 *             @OA\Examples(example="vendedoresSnapshot", summary="Snapshot — replace por ítem", value={"importacionId": "ERP-MAE-VEN-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "snapshot", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codVended": "VEN01", "nombre": "Vendedor Uno"}}}),
 *             @OA\Examples(example="vendedoresDelta", summary="Delta — upsert", value={"importacionId": "ERP-MAE-VEN-002", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codVended": "VEN01", "nombre": "Vendedor Uno (actualizado)"}}}),
 *             @OA\Examples(example="vendedoresDeltaBaja", summary="Delta — baja explícita (eliminarSiAusente)", value={"importacionId": "ERP-MAE-VEN-003", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codVended": "VEN99", "eliminarSiAusente": true}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/listaprecios",
 *     operationId="integracionMaestrosListaprecios",
 *     summary="Importar listas de precios",
 *     description="**snapshot** o **delta**. Precios hijos en `articulos[]` (alias ERP `items[]`).",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroListapreciosLoteRequest"),
 *             @OA\Examples(example="listapreciosSnapshot", summary="Snapshot — listas presentes = universo", value={"importacionId": "ERP-MAE-LP-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "snapshot", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codLista": 1, "descripcion": "Lista general", "articulos": {{"codArticulo": "ART001", "precio": 100.5}}}}}),
 *             @OA\Examples(example="listapreciosDelta", summary="Delta — upsert lista y precios", value={"importacionId": "ERP-MAE-LP-002", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codLista": 1, "articulos": {{"codArticulo": "ART001", "precio": 105}}}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/escalas",
 *     operationId="integracionMaestrosEscalas",
 *     summary="Importar escalas",
 *     description="**snapshot** o **delta** — ver ejemplos en Request body.",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroEscalasLoteRequest"),
 *             @OA\Examples(example="escalasSnapshot", summary="Snapshot — universo del recurso", value={"importacionId": "ERP-MAE-ESC-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "snapshot", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codEscala": "01", "descripEs": "Unidades", "valores": {{"codValor": "U", "descValor": "Unidad"}}}}}),
 *             @OA\Examples(example="escalasDelta", summary="Delta — upsert", value={"importacionId": "ERP-MAE-ESC-002", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codEscala": "01", "descripEs": "Unidades (rev)"}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/medidas",
 *     operationId="integracionMaestrosMedidas",
 *     summary="Importar medidas",
 *     description="Unidades de medida ERP. **snapshot** = universo del lote (finalize elimina ausentes); **delta** = upsert por `codMedida`. Ver ejemplos en Request body.",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroMedidasLoteRequest"),
 *             @OA\Examples(example="medidasSnapshot", summary="Snapshot — universo del recurso", value={"importacionId": "ERP-MAE-MED-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "snapshot", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"idMedida": 1001, "codMedida": "UN", "descMedida": "Unidad"}, {"idMedida": 1002, "codMedida": "KG", "descMedida": "Kilogramo"}}}),
 *             @OA\Examples(example="medidasDelta", summary="Delta — upsert por codMedida", value={"importacionId": "ERP-MAE-MED-002", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"idMedida": 1001, "codMedida": "UN", "descMedida": "Unidad (actualizada)"}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/articulos",
 *     operationId="integracionMaestrosArticulos",
 *     summary="Importar artículos",
 *     description="**snapshot** (replace por ítem) o **delta** (upsert + `eliminarSiAusente`). Ver ejemplos.",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroArticulosLoteRequest"),
 *             @OA\Examples(example="articulosSnapshot", summary="Snapshot — replace campos enviados por ítem", value={"importacionId": "ERP-MAE-ART-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "snapshot", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codigo": "ART001", "descripcion": "Artículo demo", "stockeable": true}}}),
 *             @OA\Examples(example="articulosDelta", summary="Delta — upsert", value={"importacionId": "ERP-MAE-ART-002", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codigo": "ART001", "descripcion": "Artículo demo (rev)"}}}),
 *             @OA\Examples(example="articulosDeltaBaja", summary="Delta — baja explícita (eliminarSiAusente)", value={"importacionId": "ERP-MAE-ART-003", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codigo": "ART099", "eliminarSiAusente": true}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/descuentocantidad",
 *     operationId="integracionMaestrosDescuentocantidad",
 *     summary="Importar descuentos por cantidad",
 *     description="**Solo delta** en MVP (`snapshot` → 422). Ejemplo único en Request body.",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroDescuentocantidadLoteRequest"),
 *             @OA\Examples(example="descuentocantidadDelta", summary="Delta — único modo soportado", value={"importacionId": "ERP-MAE-DC-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codArticu": "ART001", "cantidad": 10, "porcentaje": 5}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido o modo snapshot")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/clientes",
 *     operationId="integracionMaestrosClientes",
 *     summary="Importar clientes",
 *     description="**snapshot** o **delta**; contactos/direcciones anidados; `eliminarSiAusente` en delta. Ver ejemplos.",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroClientesLoteRequest"),
 *             @OA\Examples(example="clientesSnapshot", summary="Snapshot — replace por ítem", value={"importacionId": "ERP-MAE-CLI-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "snapshot", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codClient": "CL-001", "nombre": "Cliente demo", "codVended": "VEN01"}}}),
 *             @OA\Examples(example="clientesDelta", summary="Delta — upsert", value={"importacionId": "ERP-MAE-CLI-002", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codClient": "CL-001", "nombre": "Cliente demo (rev)"}}}),
 *             @OA\Examples(example="clientesDeltaBaja", summary="Delta — baja explícita (eliminarSiAusente)", value={"importacionId": "ERP-MAE-CLI-003", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"codClient": "CL-099", "eliminarSiAusente": true}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/maestros/parametros",
 *     operationId="integracionMaestrosParametros",
 *     summary="Importar parámetros PedidosWeb",
 *     description="**Solo delta** (`snapshot` → 422). Whitelist de claves — ver schema del ítem.",
 *     tags={"Integración maestros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionMaestroParametrosLoteRequest"),
 *             @OA\Examples(example="parametrosDelta", summary="Delta — único modo soportado", value={"importacionId": "ERP-MAE-PAR-001", "numeroLote": 1, "ultimoEnvio": true, "modo": "delta", "fechaProceso": "2026-10-24T12:00:00-03:00", "origen": "erp-job", "items": {{"clave": "MinutosWeb", "tipoValor": "I", "valorInt": 30}}})
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido o modo snapshot")
 * )
 */
final class OpenApiIntegracionMaestrosPaths
{
}
