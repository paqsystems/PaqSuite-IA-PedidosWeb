<?php

namespace App\Http\Controllers\Api\V1\ExcelImport;

use App\Exceptions\AuthFlowException;
use App\Exceptions\ExcelImportFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ExcelImport\ExcelImportErrorsExportService;
use App\Services\ExcelImport\ExcelImportAccessService;
use App\Services\ExcelImport\ExcelImportProcessService;
use App\Services\ExcelImport\ExcelStagingQueryService;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="ExcelImport", description="Importación Excel — staging y procesamiento")
 */
final class ExcelImportStagingController extends Controller
{
    public function __construct(
        private readonly ExcelImportAccessService $accessService,
        private readonly ExcelStagingQueryService $stagingQueryService,
        private readonly ExcelImportProcessService $processService,
        private readonly ExcelImportErrorsExportService $errorsExportService,
    ) {}

    public function filas(Request $request, string $guidImportacion): JsonResponse
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
        $soloConError = $request->has('soloConError')
            ? filter_var($request->query('soloConError'), FILTER_VALIDATE_BOOLEAN)
            : null;

        try {
            $importacion = $this->accessService->findLoteByGuid($guidImportacion);
            $this->accessService->ensureLotAccess($user, $importacion);

            return ApiResponse::success(
                $this->stagingQueryService->listFilas($importacion, $page, $pageSize, $soloConError)
            );
        } catch (AuthFlowException|ExcelImportFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }

    public function columnas(Request $request, string $guidImportacion): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $epicResponse = $this->accessService->ensureEpicEnabled();
        if ($epicResponse !== null) {
            return $epicResponse;
        }

        try {
            $importacion = $this->accessService->findLoteByGuid($guidImportacion);
            $this->accessService->ensureLotAccess($user, $importacion);

            return ApiResponse::success(
                $this->stagingQueryService->buildColumnasMetadata($importacion, $this->processService)
            );
        } catch (AuthFlowException|ExcelImportFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }

    public function procesar(Request $request, string $guidImportacion): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $epicResponse = $this->accessService->ensureEpicEnabled();
        if ($epicResponse !== null) {
            return $epicResponse;
        }

        try {
            $importacion = $this->accessService->findLoteByGuid($guidImportacion);
            $this->accessService->ensureLotAccess($user, $importacion);

            $resultado = $this->processService->processLot($importacion);

            return ApiResponse::success($resultado, 'excelImport.processSuccess');
        } catch (AuthFlowException|ExcelImportFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }

    public function filasValidas(Request $request, string $guidImportacion): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $epicResponse = $this->accessService->ensureEpicEnabled();
        if ($epicResponse !== null) {
            return $epicResponse;
        }

        try {
            $importacion = $this->accessService->findLoteByGuid($guidImportacion);
            $this->accessService->ensureLotAccess($user, $importacion);

            $items = $this->stagingQueryService->listValidRowPayload($importacion);

            return ApiResponse::success([
                'items' => $items,
                'total' => count($items),
            ]);
        } catch (AuthFlowException|ExcelImportFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }

    public function exportErrores(Request $request, string $guidImportacion): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        $user = $request->user();
        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $epicResponse = $this->accessService->ensureEpicEnabled();
        if ($epicResponse !== null) {
            return $epicResponse;
        }

        try {
            $importacion = $this->accessService->findLoteByGuid($guidImportacion);
            $this->accessService->ensureLotAccess($user, $importacion);

            $spreadsheet = $this->errorsExportService->generateSpreadsheet($importacion);
            $binary = $this->errorsExportService->writeToString($spreadsheet);
            $fileName = $this->errorsExportService->buildSuggestedFileName($importacion);

            return response($binary, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            ]);
        } catch (AuthFlowException|ExcelImportFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }
}
