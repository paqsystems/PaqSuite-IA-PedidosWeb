<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Http\Controllers\Controller;
use App\Http\Requests\PedidosWeb\IntegracionExportComprobantesRequest;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\Integracion\Export\ExportComprobantesService;
use App\Services\PedidosWeb\Integracion\Export\IntegracionExportLogWriter;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final class IntegracionExportComprobantesController extends Controller
{
    public function __construct(
        private readonly ExportComprobantesService $exportComprobantesService,
        private readonly IntegracionExportLogWriter $logWriter,
    ) {}

    public function index(IntegracionExportComprobantesRequest $request): JsonResponse
    {
        try {
            $resultado = $this->exportComprobantesService->list($request->toQuery());
            $this->logWriter->logSuccess($request, $resultado);

            return ApiResponse::success($resultado, 'integracion.export.ok');
        } catch (\Throwable $throwable) {
            Log::error('integracion.export.error', [
                'message' => $throwable->getMessage(),
            ]);

            return ApiResponse::error(
                AuthErrorCodes::validationFailed,
                'integracion.export.errorFatal',
                500,
            );
        }
    }
}