<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthFlowException;
use App\Http\Responses\ApiResponse;
use App\Services\Visibility\VisibilityDataService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VisibilityDataController extends Controller
{
    public function __construct(
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
        private readonly VisibilityDataService $visibilityDataService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/clientes",
     *     summary="Clientes visibles según perfil funcional",
     *     tags={"Visibilidad"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(response=200, description="Listado de clientes visibles", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeVisibleClients")),
     *     @OA\Response(response=400, description="Tenant invalido"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso base de consulta")
     * )
     */
    public function clients(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(
                AuthErrorCodes::unauthenticated,
                'auth.unauthenticated',
                401
            );
        }

        try {
            $this->visibilityPermissionGuard->ensureRepoPermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.clientes')
            );

            $resultado = $this->visibilityDataService->listVisibleClients($user);
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($resultado);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comprobantes/{id}",
     *     summary="Comprobante visible según perfil funcional",
     *     tags={"Visibilidad"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Comprobante visible", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeVisibleComprobante")),
     *     @OA\Response(response=400, description="Tenant invalido"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso base de consulta"),
     *     @OA\Response(response=404, description="Comprobante inexistente o fuera de alcance")
     * )
     */
    public function showComprobante(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(
                AuthErrorCodes::unauthenticated,
                'auth.unauthenticated',
                401
            );
        }

        try {
            $this->visibilityPermissionGuard->ensureRepoPermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.comprobantes')
            );

            $resultado = $this->visibilityDataService->findVisibleComprobante($user, $id);
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($resultado);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/resumen",
     *     summary="Resumen visible del dashboard",
     *     tags={"Visibilidad"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(response=200, description="Resumen dashboard visible", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeDashboardResumen")),
     *     @OA\Response(response=400, description="Tenant invalido"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso base de consulta")
     * )
     */
    public function dashboardResumen(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(
                AuthErrorCodes::unauthenticated,
                'auth.unauthenticated',
                401
            );
        }

        try {
            $this->visibilityPermissionGuard->ensureRepoPermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.dashboard')
            );

            $resultado = $this->visibilityDataService->buildDashboardResumen($user);
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($resultado);
    }
}
