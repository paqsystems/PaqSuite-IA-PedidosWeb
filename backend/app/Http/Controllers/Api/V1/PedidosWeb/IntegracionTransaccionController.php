<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\IntegracionMaestroAbortException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PedidosWeb\IntegracionTransaccionLoteRequest;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroLogWriter;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionOrchestrator;
use App\Support\IntegracionMaestroErrorCodes;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

final class IntegracionTransaccionController extends Controller
{
    public function __construct(
        private readonly IntegracionTransaccionOrchestrator $orchestrator,
        private readonly IntegracionMaestroLogWriter $logWriter,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/integracion/transacciones/{recurso}",
     *     operationId="integracionTransaccionesStore",
     *     tags={"Integración transacciones"},
     *     summary="Importar lote de transacciones ERP",
     *     description="Requiere permiso pw_integracion_importacion, usuario técnico Sanctum y X-Paq-Cliente.",
     *     security={{"sanctum": {}}, {"tenant": {}}},
     *     @OA\Parameter(name="recurso", in="path", required=true, @OA\Schema(type="string", enum={"stock","deuda","cheques","historial-ventas"})),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/IntegracionTransaccionLoteRequest")),
     *     @OA\Response(response=200, description="Lote procesado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionTransaccionLote")),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=403, description="Sin permiso integración", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Recurso desconocido", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Lote rechazado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function store(string $recurso, IntegracionTransaccionLoteRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $origen = isset($validated['origen']) ? (string) $validated['origen'] : null;

        try {
            $result = $this->orchestrator->process($recurso, $validated);
        } catch (IntegracionMaestroAbortException $exception) {
            $this->logWriter->writeAbortedTransaction($recurso, $exception->respuestaKey(), $origen);

            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus(),
            );
        } catch (\Throwable) {
            $this->logWriter->writeAbortedTransaction($recurso, 'integracion.transaccion.errorFatal', $origen);

            return ApiResponse::error(
                IntegracionMaestroErrorCodes::fatal,
                'integracion.transaccion.errorFatal',
                500,
            );
        }

        $this->logWriter->writeTransaction($result, $origen);

        return ApiResponse::success(
            $result->toArray(),
            $result->erroresTruncados ? 'integracion.transaccion.erroresTruncados' : 'ok',
        );
    }
}
