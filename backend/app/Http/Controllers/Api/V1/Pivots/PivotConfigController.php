<?php

namespace App\Http\Controllers\Api\V1\Pivots;

use App\Exceptions\AuthFlowException;
use App\Exceptions\PivotFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\PqPivotConfig;
use App\Services\Pivots\PivotConfigService;
use App\Services\Pivots\PivotMetadataResolver;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class PivotConfigController extends Controller
{
    public function __construct(
        private readonly PivotConfigService $pivotConfigService,
        private readonly PivotMetadataResolver $pivotMetadataResolver,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consultaId' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $consulta = $this->pivotMetadataResolver->findActiveConsulta($validated['consultaId']);
            $this->visibilityPermissionGuard->ensureRepoPermission($user, (string) $consulta->procedimiento_host);
            $items = $this->pivotConfigService->listConfigs($validated['consultaId'], $user);
        } catch (AuthFlowException|PivotFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success(['items' => $items]);
    }

    public function active(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consultaId' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $consulta = $this->pivotMetadataResolver->findActiveConsulta($validated['consultaId']);
            $this->visibilityPermissionGuard->ensureRepoPermission($user, (string) $consulta->procedimiento_host);
            $payload = $this->pivotConfigService->getActiveConfig($validated['consultaId'], $user);
        } catch (AuthFlowException|PivotFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($payload);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consultaId' => ['required', 'string', 'max:100'],
            'nombre' => ['required', 'string', 'max:200'],
            'configuracionJson' => ['required', 'array'],
        ]);

        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $consulta = $this->pivotMetadataResolver->findActiveConsulta($validated['consultaId']);
            $this->visibilityPermissionGuard->ensureRepoPermission($user, (string) $consulta->procedimiento_host);

            $created = $this->pivotConfigService->createConfig(
                $validated['consultaId'],
                $validated['nombre'],
                $validated['configuracionJson'],
                (int) $consulta->version_definicion,
                $user
            );
        } catch (AuthFlowException|PivotFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        } catch (ValidationException $exception) {
            return $this->mapValidationException($exception);
        }

        return ApiResponse::success($created, 'pivotLayout.created', 201);
    }

    public function update(Request $request, int $configId): JsonResponse
    {
        $validated = $request->validate([
            'configuracionJson' => ['required', 'array'],
        ]);

        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $config = PqPivotConfig::query()
            ->where('pivot_id', $configId)
            ->where('eliminado', false)
            ->firstOrFail();

        if ((int) $config->created_by_user_id !== (int) $user->id) {
            return ApiResponse::error(3001, 'pivotLayout.forbidden', 403);
        }

        try {
            $consulta = $this->pivotMetadataResolver->findActiveConsulta($config->consulta_id);
            $this->visibilityPermissionGuard->ensureRepoPermission($user, (string) $consulta->procedimiento_host);

            $updated = $this->pivotConfigService->updateConfig(
                $config,
                $validated['configuracionJson'],
                $user
            );
        } catch (AuthFlowException|PivotFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        } catch (ValidationException $exception) {
            return $this->mapValidationException($exception);
        }

        return ApiResponse::success($updated, 'pivotLayout.updated');
    }

    public function destroy(Request $request, int $configId): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $config = PqPivotConfig::query()
            ->where('pivot_id', $configId)
            ->where('eliminado', false)
            ->firstOrFail();

        if ((int) $config->created_by_user_id !== (int) $user->id) {
            return ApiResponse::error(3001, 'pivotLayout.forbidden', 403);
        }

        try {
            $consulta = $this->pivotMetadataResolver->findActiveConsulta($config->consulta_id);
            $this->visibilityPermissionGuard->ensureRepoPermission($user, (string) $consulta->procedimiento_host);
            $this->pivotConfigService->deleteConfig($config, $user);
        } catch (AuthFlowException|PivotFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success([], 'pivotLayout.deleted');
    }

    public function setActive(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consultaId' => ['required', 'string', 'max:100'],
            'configId' => ['nullable', 'integer'],
        ]);

        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $consulta = $this->pivotMetadataResolver->findActiveConsulta($validated['consultaId']);
            $this->visibilityPermissionGuard->ensureRepoPermission($user, (string) $consulta->procedimiento_host);

            $this->pivotConfigService->setActiveConfig(
                $validated['consultaId'],
                $validated['configId'] ?? null,
                $user
            );
        } catch (AuthFlowException|PivotFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        } catch (ValidationException $exception) {
            return $this->mapValidationException($exception);
        }

        return ApiResponse::success([], 'pivotLayout.activeUpdated');
    }

    private function mapValidationException(ValidationException $exception): JsonResponse
    {
        $nombreErrors = $exception->errors()['nombre'] ?? [];

        if (in_array('pivotLayout.duplicateName', $nombreErrors, true)) {
            return ApiResponse::error(2001, 'pivotLayout.duplicateName', 409);
        }

        $jsonErrors = $exception->errors()['configuracionJson'] ?? [];

        if (in_array('pivotLayout.payloadTooLarge', $jsonErrors, true)) {
            return ApiResponse::error(1000, 'pivotLayout.payloadTooLarge', 422);
        }

        throw $exception;
    }
}
