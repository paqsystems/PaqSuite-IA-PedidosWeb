<?php

namespace App\Http\Controllers\Api\V1\Pivots;

use App\Exceptions\AuthFlowException;
use App\Exceptions\PivotFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Pivots\PivotDatasetExecutor;
use App\Services\Pivots\PivotMetadataResolver;
use App\Services\Pivots\PivotStructureValidator;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Pivots", description="Motor metadata y dataset pivotable")
 */
final class PivotController extends Controller
{
    public function __construct(
        private readonly PivotMetadataResolver $pivotMetadataResolver,
        private readonly PivotDatasetExecutor $pivotDatasetExecutor,
        private readonly PivotStructureValidator $pivotStructureValidator,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/pivots/consultas/{consultaId}/metadata",
     *     tags={"Pivots"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="consultaId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Metadata efectiva"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso"),
     *     @OA\Response(response=404, description="Consulta no encontrada")
     * )
     */
    public function metadata(Request $request, string $consultaId): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $consulta = $this->pivotMetadataResolver->findActiveConsulta($consultaId);
            $this->visibilityPermissionGuard->ensureRepoPermission($user, (string) $consulta->procedimiento_host);
            $resultado = $this->pivotMetadataResolver->resolveMetadata($consultaId);
        } catch (AuthFlowException|PivotFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($resultado);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/pivots/consultas/{consultaId}/data",
     *     tags={"Pivots"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="consultaId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object")),
     *     @OA\Response(response=200, description="Dataset plano"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Filtro obligatorio o volumen excedido")
     * )
     */
    public function data(Request $request, string $consultaId): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $validated = $request->validate([
            'filtros' => ['sometimes', 'array'],
            'pagina' => ['sometimes', 'integer', 'min:1'],
            'tamanoPagina' => ['sometimes', 'integer', 'min:1', 'max:5000'],
        ]);

        try {
            $consulta = $this->pivotMetadataResolver->findActiveConsulta($consultaId);
            $this->visibilityPermissionGuard->ensureRepoPermission($user, (string) $consulta->procedimiento_host);

            $resultado = $this->pivotDatasetExecutor->execute(
                $user,
                $consultaId,
                $validated['filtros'] ?? [],
                (int) ($validated['pagina'] ?? 1),
                (int) ($validated['tamanoPagina'] ?? 500)
            );
        } catch (AuthFlowException|PivotFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($resultado);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/pivots/consultas/{consultaId}/validate-structure",
     *     tags={"Pivots"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="consultaId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object")),
     *     @OA\Response(response=200, description="Estructura válida"),
     *     @OA\Response(response=422, description="Estructura inválida")
     * )
     */
    public function validateStructure(Request $request, string $consultaId): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $validated = $request->validate([
            'filas' => ['sometimes', 'array'],
            'columnas' => ['sometimes', 'array'],
            'valores' => ['sometimes', 'array'],
            'filtrosInternos' => ['sometimes', 'array'],
        ]);

        try {
            $consulta = $this->pivotMetadataResolver->findActiveConsulta($consultaId);
            $this->visibilityPermissionGuard->ensureRepoPermission($user, (string) $consulta->procedimiento_host);

            $metadata = $this->pivotMetadataResolver->resolveMetadata($consultaId);
            $restricciones = is_array($metadata['restricciones']) ? $metadata['restricciones'] : [];

            $this->pivotStructureValidator->validate($validated, $restricciones);
        } catch (AuthFlowException|PivotFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success(['valido' => true]);
    }
}
