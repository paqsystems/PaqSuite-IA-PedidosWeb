<?php

namespace App\OpenApi;

/**
 * @OA\Get(
 *     path="/api/v1/integracion/comprobantes",
 *     operationId="integracionExportComprobantes",
 *     summary="Exportar comprobantes ERP (lectura paginada)",
 *     description="Listado síncrono de cabeceras con renglones y cierre. Requiere `estadoRangos` (repetible o CSV).",
 *     tags={"Integración exportación"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="estadoRangos", in="query", required=true, description="Par desde:hasta (repetible o CSV)", @OA\Schema(type="array", @OA\Items(type="string", pattern="^-?\\d+:-?\\d+$", example="0:0"))),
 *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1, default=1)),
 *     @OA\Parameter(name="pageSize", in="query", @OA\Schema(type="integer", minimum=1, maximum=1000, default=20)),
 *     @OA\Parameter(name="page_size", in="query", description="Alias de pageSize", @OA\Schema(type="integer", minimum=1, maximum=1000)),
 *     @OA\Parameter(name="codCliente", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="codPedido", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="fechaDesde", in="query", @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="fechaHasta", in="query", @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="Página exportada", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionExportComprobantes")),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso integración"),
 *     @OA\Response(response=422, description="Query inválida")
 * )
 */
final class OpenApiIntegracionExportPaths {}