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
final class IntegracionTransaccionController extends Controller
{
    public function __construct(
        private readonly IntegracionTransaccionOrchestrator $orchestrator,
        private readonly IntegracionMaestroLogWriter $logWriter,
    ) {}

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
