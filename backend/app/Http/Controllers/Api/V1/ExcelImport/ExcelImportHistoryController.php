<?php

namespace App\Http\Controllers\Api\V1\ExcelImport;

use App\Exceptions\AuthFlowException;
use App\Exceptions\ExcelImportFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ExcelImport\ExcelImportAccessService;
use App\Services\ExcelImport\ExcelImportHistoryService;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="ExcelImport", description="Importación Excel — historial")
 */
final class ExcelImportHistoryController extends Controller
{
    public function __construct(
        private readonly ExcelImportAccessService $accessService,
        private readonly ExcelImportHistoryService $historyService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/excel-import/historial",
     *     summary="Historial de importaciones Excel",
     *     tags={"ExcelImport"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso o epica deshabilitada")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $epicResponse = $this->accessService->ensureEpicEnabled();
        if ($epicResponse !== null) {
            return $epicResponse;
        }

        $page = max(1, (int) $request->query('page', 1));
        $pageSize = min(
            (int) config('excel_import.maxPageSize'),
            max(1, (int) $request->query('pageSize', config('excel_import.defaultPageSize')))
        );

        $filters = array_filter([
            'codigoProceso' => $request->query('codigoProceso'),
            'estadoImportacion' => $request->query('estadoImportacion'),
            'usuarioEjecucion' => $request->query('usuarioEjecucion'),
            'fechaDesde' => $request->query('fechaDesde'),
            'fechaHasta' => $request->query('fechaHasta'),
        ], static fn ($value) => $value !== null && $value !== '');

        try {
            $this->accessService->ensureHistorialAccess($user);

            return ApiResponse::success(
                $this->historyService->listHistorial($user, $filters, $page, $pageSize)
            );
        } catch (AuthFlowException|ExcelImportFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }
}
