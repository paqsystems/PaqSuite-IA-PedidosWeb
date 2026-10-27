<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\IntegracionMaestroAbortException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PedidosWeb\IntegracionImportacionLoteRequest;
use App\Http\Requests\PedidosWeb\IntegracionMaestroLoteRequest;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroLogWriter;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroOrchestrator;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroRegistry;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionRegistry;
use App\Support\IntegracionMaestroErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Importation\Dto\ImportationBatch;
use PaqSuite\LaravelCore\Importation\ImportationException;
use PaqSuite\LaravelCore\Importation\ImportationOrchestrator as FrameworkImportationOrchestrator;
final class IntegracionMaestroController extends Controller
{
    public function __construct(
        private readonly IntegracionMaestroOrchestrator $orchestrator,
        private readonly IntegracionMaestroLogWriter $logWriter,
        private readonly IntegracionMaestroRegistry $registry,
        private readonly IntegracionTransaccionRegistry $transactionRegistry,
        private readonly FrameworkImportationOrchestrator $frameworkOrchestrator,
    ) {}

    public function store(string $recurso, IntegracionMaestroLoteRequest $request): JsonResponse
    {
        $validated = $request->validated();
        if (! $this->registry->has($recurso)) {
            return ApiResponse::error(
                IntegracionMaestroErrorCodes::recursoDesconocido,
                'integracion.maestro.recursoDesconocido',
                404,
            );
        }

        try {
            $result = $this->orchestrator->process($recurso, $validated);
        } catch (IntegracionMaestroAbortException $exception) {
            $origen = isset($validated['origen']) ? (string) $validated['origen'] : null;
            $this->logWriter->writeAborted($recurso, $exception->respuestaKey(), $origen);

            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus(),
            );
        } catch (\Throwable) {
            $origen = isset($validated['origen']) ? (string) $validated['origen'] : null;
            $this->logWriter->writeAborted($recurso, 'integracion.maestro.errorFatal', $origen);

            return ApiResponse::error(
                IntegracionMaestroErrorCodes::fatal,
                'integracion.maestro.errorFatal',
                500,
            );
        }

        $this->logWriter->write($result, isset($validated['origen']) ? (string) $validated['origen'] : null);

        $respuesta = $result->erroresTruncados
            ? 'integracion.maestro.erroresTruncados'
            : 'ok';

        return ApiResponse::success($result->toArray(), $respuesta);
    }

    public function receive(
        string $recurso,
        IntegracionImportacionLoteRequest $request,
    ): JsonResponse {
        $validated = $request->validated();
        if (! $this->registry->has($recurso) && ! $this->transactionRegistry->has($recurso)) {
            return ApiResponse::error(
                IntegracionMaestroErrorCodes::recursoDesconocido,
                'integracion.maestro.recursoDesconocido',
                404,
            );
        }

        try {
            $tenant = (string) $request->header(
                (string) config('paqsuite_tenant.headerName', 'X-Paq-Cliente'),
                '',
            );
            $payloadJson = json_encode($validated, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $receipt = $this->frameworkOrchestrator->receive(new ImportationBatch(
                (string) $validated['importacionId'],
                $recurso,
                $tenant,
                (int) $validated['numeroLote'],
                (bool) $validated['ultimoEnvio'],
                (string) $validated['modo'],
                (string) $validated['fechaProceso'],
                $validated,
                hash('sha256', $payloadJson),
            ));
        } catch (ImportationException $exception) {
            $errorCode = $exception->httpStatus === 409
                ? IntegracionMaestroErrorCodes::conflictoImportacion
                : IntegracionMaestroErrorCodes::fatal;

            return ApiResponse::error(
                $errorCode,
                $exception->messageKey,
                $exception->httpStatus,
            );
        } catch (\Throwable) {
            return ApiResponse::error(
                IntegracionMaestroErrorCodes::fatal,
                'integracion.maestro.errorFatal',
                500,
            );
        }

        return ApiResponse::success([
            'importacionId' => (string) $validated['importacionId'],
            'numeroLote' => (int) $validated['numeroLote'],
            'estado' => $receipt['queued'] ? 'queued' : 'received',
            'duplicado' => $receipt['duplicate'],
        ], 'integracion.maestro.recibido', 202);
    }

    public function show(
        string $importacionId,
        Request $request,
    ): JsonResponse {
        $tenant = (string) $request->header(
            (string) config('paqsuite_tenant.headerName', 'X-Paq-Cliente'),
            '',
        );
        $result = $this->frameworkOrchestrator->resultByImportationId($importacionId, $tenant);
        if ($result === null) {
            return ApiResponse::error(
                IntegracionMaestroErrorCodes::importacionNoEncontrada,
                'integracion.maestro.importacionNoEncontrada',
                404,
            );
        }

        return ApiResponse::success([
            'importacionId' => $result->importacionId,
            'recurso' => $result->resource,
            'estado' => $result->status,
            'duracion' => $result->durationSeconds,
            'procesados' => $result->processed,
            'agregados' => $result->added,
            'modificados' => $result->updated,
            'eliminados' => $result->deleted,
            'agregadosErroneos' => $result->addedErrors,
            'modificadosErroneos' => $result->updatedErrors,
            'eliminadosErroneos' => $result->deletedErrors,
            'errores' => $result->errors,
        ]);
    }
}
