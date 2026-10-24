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
final class IntegracionMaestroController extends Controller
{
    public function __construct(
        private readonly IntegracionMaestroOrchestrator $orchestrator,
        private readonly IntegracionMaestroLogWriter $logWriter,
    ) {}

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
