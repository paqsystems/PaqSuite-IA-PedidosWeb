<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\AuthFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Admin\AdminPermisoService;
use App\Services\Admin\PermisoBatchService;
use App\Services\Security\AdminSecurityAccessService;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminPermisoController extends Controller
{
    public function __construct(
        private readonly AdminPermisoService $adminPermisoService,
        private readonly PermisoBatchService $permisoBatchService,
        private readonly AdminSecurityAccessService $adminSecurityAccessService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->runAuthorized($request, 'permisos', 'repo', function () use ($request): JsonResponse {
            $usuarioId = $request->query('usuarioId');
            $rolId = $request->query('rolId');

            return ApiResponse::success($this->adminPermisoService->list(
                $usuarioId !== null ? (int) $usuarioId : null,
                $rolId !== null ? (int) $rolId : null,
            ));
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->runAuthorized($request, 'permisos', 'alta', function () use ($request): JsonResponse {
            return ApiResponse::success(
                $this->adminPermisoService->create($request->all()),
                'ok',
                201
            );
        });
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->runAuthorized($request, 'permisos', 'modi', function () use ($request, $id): JsonResponse {
            return ApiResponse::success($this->adminPermisoService->update($id, $request->all()));
        });
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        return $this->runAuthorized($request, 'permisos', 'baja', function () use ($id): JsonResponse {
            $this->adminPermisoService->delete($id);

            return ApiResponse::success([]);
        });
    }

    public function batch(Request $request): JsonResponse
    {
        return $this->runAuthorized($request, 'permisos', 'alta', function () use ($request): JsonResponse {
            $resultado = $this->permisoBatchService->createBatch($request->all());

            return ApiResponse::success($resultado, 'admin.permisos.bulk.successMessage');
        });
    }

    /**
     * @param  callable(): JsonResponse  $callback
     */
    private function runAuthorized(Request $request, string $procedimientoKey, string $tipoPermiso, callable $callback): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $procedimiento = (string) config('paqsuite_admin_security.procedimientos.'.$procedimientoKey);
            $this->adminSecurityAccessService->ensure($user, $procedimiento, $tipoPermiso);
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        try {
            return $callback();
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }
}
