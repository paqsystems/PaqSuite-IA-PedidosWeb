<?php

namespace App\OpenApi;

/**
 * Paths OpenAPI — integración transacciones (ejemplos snapshot + delta en Request body).
 *
 * @OA\Post(
 *     path="/api/v1/integracion/transacciones/stock",
 *     operationId="integracionTransaccionesStock",
 *     summary="Importar stock",
 *     description="**snapshot:** el lote válido reemplaza atómicamente el universo de stock (422 si falla un ítem). **delta:** upsert parcial; baja puntual con `eliminarSiAusente` en el ítem. Elegí el ejemplo en Request body.",
 *     tags={"Integración transacciones"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionTransaccionStockLoteRequest"),
 *             @OA\Examples(
 *                 example="stockSnapshot",
 *                 summary="Snapshot — universo vigente (habitual ERP)",
 *                 value={
 *                     "importacionId": "ERP-IMP-20261024-0001",
 *                     "numeroLote": 1,
 *                     "ultimoEnvio": true,
 *                     "modo": "snapshot",
 *                     "fechaProceso": "2026-10-24T12:00:00-03:00",
 *                     "origen": "erp-job",
 *                     "items": {{"codArticulo": "ART001", "stock": 100.5, "comprometido": 10}, {"codArticulo": "ART002", "stock": 50, "comprometido": 0}}
 *                 }
 *             ),
 *             @OA\Examples(
 *                 example="stockDelta",
 *                 summary="Delta — novedades (upsert)",
 *                 value={
 *                     "importacionId": "ERP-IMP-20261024-0002",
 *                     "numeroLote": 1,
 *                     "ultimoEnvio": true,
 *                     "modo": "delta",
 *                     "fechaProceso": "2026-10-24T12:00:00-03:00",
 *                     "origen": "erp-job",
 *                     "items": {{"codArticulo": "ART001", "stock": 120, "comprometido": 5}}
 *                 }
 *             ),
 *             @OA\Examples(
 *                 example="stockDeltaBaja",
 *                 summary="Delta — baja explícita (eliminarSiAusente)",
 *                 value={
 *                     "importacionId": "ERP-IMP-20261024-0003",
 *                     "numeroLote": 1,
 *                     "ultimoEnvio": true,
 *                     "modo": "delta",
 *                     "fechaProceso": "2026-10-24T12:00:00-03:00",
 *                     "origen": "erp-job",
 *                     "items": {{"codArticulo": "ART099", "eliminarSiAusente": true}}
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido (snapshot atómico o validación)")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/integracion/transacciones/deuda",
 *     operationId="integracionTransaccionesDeuda",
 *     summary="Importar deuda",
 *     description="**snapshot** o **delta** — ver ejemplos en Request body.",
 *     tags={"Integración transacciones"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionTransaccionDeudaLoteRequest"),
 *             @OA\Examples(
 *                 example="deudaSnapshot",
 *                 summary="Snapshot — comprobantes pendientes = lote",
 *                 value={
 *                     "importacionId": "ERP-IMP-20261024-0101",
 *                     "numeroLote": 1,
 *                     "ultimoEnvio": true,
 *                     "modo": "snapshot",
 *                     "fechaProceso": "2026-10-24T12:00:00-03:00",
 *                     "origen": "erp-job",
 *                     "items": {{"codCliente": "CL-001", "tComp": "FC", "nComp": "0001-00001234", "fechaEmis": "2026-10-01", "fechaVto": "2026-11-01", "saldo": 1500.25}}
 *                 }
 *             ),
 *             @OA\Examples(
 *                 example="deudaDelta",
 *                 summary="Delta — upsert / baja con eliminarSiAusente",
 *                 value={
 *                     "importacionId": "ERP-IMP-20261024-0102",
 *                     "numeroLote": 1,
 *                     "ultimoEnvio": true,
 *                     "modo": "delta",
 *                     "fechaProceso": "2026-10-24T12:00:00-03:00",
 *                     "origen": "erp-job",
 *                     "items": {{"codCliente": "CL-001", "tComp": "FC", "nComp": "0001-00001234", "fechaEmis": "2026-10-01", "fechaVto": "2026-11-01", "saldo": 1200, "eliminarSiAusente": false}}
 *                 }
 *             )
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
 *     path="/api/v1/integracion/transacciones/cheques",
 *     operationId="integracionTransaccionesCheques",
 *     summary="Importar cheques",
 *     description="**snapshot** o **delta** — ver ejemplos en Request body.",
 *     tags={"Integración transacciones"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/IntegracionTransaccionChequesLoteRequest"),
 *             @OA\Examples(
 *                 example="chequesSnapshot",
 *                 summary="Snapshot — cartera vigente = lote",
 *                 value={
 *                     "importacionId": "ERP-IMP-20261024-0201",
 *                     "numeroLote": 1,
 *                     "ultimoEnvio": true,
 *                     "modo": "snapshot",
 *                     "fechaProceso": "2026-10-24T12:00:00-03:00",
 *                     "origen": "erp-job",
 *                     "items": {{"interno": "1001", "numero": "456789", "codCliente": "CL-001", "banco": "Galicia", "fecha": "2026-10-20", "importe": 25000.5, "origen": "Cartera", "estado": "En cartera"}}
 *                 }
 *             ),
 *             @OA\Examples(
 *                 example="chequesDelta",
 *                 summary="Delta — upsert parcial",
 *                 value={
 *                     "importacionId": "ERP-IMP-20261024-0202",
 *                     "numeroLote": 1,
 *                     "ultimoEnvio": true,
 *                     "modo": "delta",
 *                     "fechaProceso": "2026-10-24T12:00:00-03:00",
 *                     "origen": "erp-job",
 *                     "items": {{"interno": "1001", "numero": "456789", "codCliente": "CL-001", "banco": "Galicia", "fecha": "2026-10-20", "importe": 25000.5, "origen": "Cartera", "estado": "En cartera"}}
 *                 }
 *             )
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
 *     path="/api/v1/integracion/transacciones/historial-ventas",
 *     operationId="integracionTransaccionesHistorialVentas",
 *     summary="Importar historial de ventas",
 *     description="Solo **delta** (upsert por idGva53). Snapshot → 422.",
 *     tags={"Integración transacciones"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/IntegracionTransaccionHistorialVentasLoteRequest")),
 *     @OA\Response(response=202, description="Lote recibido (procesamiento asíncrono)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionImportacionLoteReceipt")),
 *     @OA\Response(response=409, description="Importación en conflicto"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Lote inválido o modo snapshot")
 * )
 */
final class OpenApiIntegracionTransaccionesPaths
{
}
