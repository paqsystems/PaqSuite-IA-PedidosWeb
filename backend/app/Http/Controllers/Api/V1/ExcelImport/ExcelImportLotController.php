<?php

namespace App\Http\Controllers\Api\V1\ExcelImport;

use App\Exceptions\AuthFlowException;
use App\Exceptions\ExcelImportFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ExcelImport\ExcelImportAccessService;
use App\Services\ExcelImport\ExcelImportLotService;
use App\Services\ExcelImport\ExcelTemplateService;
use App\Services\ExcelImport\ExcelWorkbookService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="ExcelImport", description="Importación Excel — lotes y carga")
 */
final class ExcelImportLotController extends Controller
{
    public function __construct(
        private readonly ExcelTemplateService $excelTemplateService,
        private readonly ExcelWorkbookService $workbookService,
        private readonly ExcelImportLotService $lotService,
        private readonly ExcelImportAccessService $accessService,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/excel-import/procesos/{codigoProceso}/archivo/hojas",
     *     summary="Listar hojas de un archivo Excel",
     *     tags={"ExcelImport"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="codigoProceso", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Hojas detectadas", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Archivo invalido")
     * )
     */
    public function listarHojas(Request $request, string $codigoProceso): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $epicResponse = $this->accessService->ensureEpicEnabled();
        if ($epicResponse !== null) {
            return $epicResponse;
        }

        $request->validate(['archivo' => 'required|file']);

        try {
            $proceso = $this->excelTemplateService->findActiveProceso($codigoProceso);
            $this->visibilityPermissionGuard->ensureAltaPermission($user, (string) $proceso->procedimiento_host);

            $archivo = $request->file('archivo');
            $path = $archivo?->getRealPath();
            if ($path === false || $path === null) {
                throw new ExcelImportFlowException(
                    \App\Support\ExcelImportErrorCodes::archivoCorrupto,
                    'excelImport.archivoCorrupto',
                    422
                );
            }

            $hojas = $this->workbookService->listSheetNames($path);

            return ApiResponse::success(['hojas' => $hojas]);
        } catch (AuthFlowException|ExcelImportFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/excel-import/procesos/{codigoProceso}/lotes",
     *     summary="Crear lote de importacion Excel",
     *     tags={"ExcelImport"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="codigoProceso", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lote creado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Validacion o regla de negocio")
     * )
     */
    public function crearLote(Request $request, string $codigoProceso): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $epicResponse = $this->accessService->ensureEpicEnabled();
        if ($epicResponse !== null) {
            return $epicResponse;
        }

        $request->validate([
            'archivo' => 'required|file',
            'hojaSeleccionada' => 'required|string|max:150',
        ]);

        try {
            $proceso = $this->excelTemplateService->findActiveProceso($codigoProceso);
            $this->visibilityPermissionGuard->ensureAltaPermission($user, (string) $proceso->procedimiento_host);

            $archivo = $request->file('archivo');
            if ($archivo === null) {
                throw new ExcelImportFlowException(
                    \App\Support\ExcelImportErrorCodes::formatoInvalido,
                    'excelImport.formatoInvalido',
                    422
                );
            }

            $resultado = $this->lotService->createLotFromUpload(
                $user,
                $codigoProceso,
                $archivo,
                (string) $request->input('hojaSeleccionada'),
                $request->ip()
            );

            return ApiResponse::success($resultado);
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
     *     path="/api/v1/excel-import/lotes/{guidImportacion}",
     *     summary="Detalle de lote de importacion",
     *     tags={"ExcelImport"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="guidImportacion", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Detalle del lote", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Lote inexistente")
     * )
     */
    public function show(Request $request, string $guidImportacion): JsonResponse
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

            return ApiResponse::success($this->lotService->buildLotDetail($importacion));
        } catch (AuthFlowException|ExcelImportFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/excel-import/lotes/{guidImportacion}/cancelar",
     *     summary="Cancelar lote de importacion",
     *     tags={"ExcelImport"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="guidImportacion", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lote cancelado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="No cancelable")
     * )
     */
    public function cancelar(Request $request, string $guidImportacion): JsonResponse
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
            $this->lotService->cancelLot($importacion, $user);

            return ApiResponse::success([
                'estadoImportacion' => 'cancelada',
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
