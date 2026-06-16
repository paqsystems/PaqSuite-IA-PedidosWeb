<?php

namespace App\Http\Controllers\Api\V1\ExcelImport;

use App\Exceptions\AuthFlowException;
use App\Exceptions\ExcelImportFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ExcelImport\ExcelImportAccessService;
use App\Services\ExcelImport\ExcelTemplateService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(name="ExcelImport", description="Importación Excel por proceso")
 */
final class ExcelImportProcessController extends Controller
{
    public function __construct(
        private readonly ExcelTemplateService $excelTemplateService,
        private readonly ExcelImportAccessService $accessService,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/excel-import/procesos/{codigoProceso}",
     *     tags={"ExcelImport"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="codigoProceso", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Metadata del proceso"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso"),
     *     @OA\Response(response=404, description="Proceso no encontrado")
     * )
     */
    public function show(Request $request, string $codigoProceso): JsonResponse
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
            $proceso = $this->excelTemplateService->findActiveProceso($codigoProceso);
            $this->visibilityPermissionGuard->ensureAltaPermission($user, (string) $proceso->procedimiento_host);

            return ApiResponse::success($this->excelTemplateService->buildProcesoMetadata($proceso));
        } catch (AuthFlowException|ExcelImportFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/excel-import/procesos/{codigoProceso}/plantilla",
     *     tags={"ExcelImport"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="codigoProceso", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Archivo xlsx"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso"),
     *     @OA\Response(response=404, description="Plantilla no disponible")
     * )
     */
    public function plantilla(Request $request, string $codigoProceso): JsonResponse|Response
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
            $proceso = $this->excelTemplateService->findActiveProceso($codigoProceso);
            $this->visibilityPermissionGuard->ensureAltaPermission($user, (string) $proceso->procedimiento_host);
            $spreadsheet = $this->excelTemplateService->generateSpreadsheet($proceso);
            $binary = $this->excelTemplateService->writeSpreadsheetToString($spreadsheet);
            $fileName = $this->excelTemplateService->buildSuggestedFileName($codigoProceso);

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
