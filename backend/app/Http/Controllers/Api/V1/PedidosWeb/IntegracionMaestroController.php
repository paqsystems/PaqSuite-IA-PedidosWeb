<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\IntegracionMaestroAbortException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PedidosWeb\IntegracionMaestroLoteRequest;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroLogWriter;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroOrchestrator;
use App\Support\IntegracionMaestroErrorCodes;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

final class IntegracionMaestroController extends Controller
{
    public function __construct(
        private readonly IntegracionMaestroOrchestrator $orchestrator,
        private readonly IntegracionMaestroLogWriter $logWriter,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/integracion/maestros/{recurso}",
     *     operationId="integracionMaestrosStore",
     *     tags={"Integración maestros"},
     *     summary="Importar lote de maestros ERP",
     *     description="Requiere permiso pw_integracion_importacion (alta). Usuario técnico Sanctum + header X-Paq-Cliente.",
     *     security={{"sanctum": {}}, {"tenant": {}}},
     *     @OA\Parameter(name="recurso", in="path", required=true, @OA\Schema(type="string", enum={
     *         "vendedores","provincias","condventa","transportes","perfiles","listaprecios","escalas",
     *         "articulos","listaprecios-articulos","descuentocantidad","clientes","clientes-direcciones",
     *         "clientes-contactos","parametros"
     *     })),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/IntegracionMaestroLoteRequest")),
     *     @OA\Response(response=200, description="Lote procesado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionMaestroLote")),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=403, description="Sin permiso integración", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Recurso desconocido", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Lote rechazado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function store(string $recurso, IntegracionMaestroLoteRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $origen = isset($validated['origen']) ? (string) $validated['origen'] : null;

        try {
            $result = $this->orchestrator->process($recurso, $validated);
        } catch (IntegracionMaestroAbortException $exception) {
            $this->logWriter->writeAborted($recurso, $exception->respuestaKey(), $origen);

            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus(),
            );
        } catch (\Throwable) {
            $this->logWriter->writeAborted($recurso, 'integracion.maestro.errorFatal', $origen);

            return ApiResponse::error(
                IntegracionMaestroErrorCodes::fatal,
                'integracion.maestro.errorFatal',
                500,
            );
        }

        $this->logWriter->write($result, $origen);

        $respuesta = $result->erroresTruncados
            ? 'integracion.maestro.erroresTruncados'
            : 'ok';

        return ApiResponse::success($result->toArray(), $respuesta);
    }
}
