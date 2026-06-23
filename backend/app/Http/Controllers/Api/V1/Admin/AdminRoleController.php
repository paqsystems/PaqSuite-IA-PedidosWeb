<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\AuthFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\RoleAttributesService;
use App\Services\Security\AdminSecurityAccessService;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminRoleController extends Controller
{
    public function __construct(
        private readonly AdminRoleService $adminRoleService,
        private readonly RoleAttributesService $roleAttributesService,
        private readonly AdminSecurityAccessService $adminSecurityAccessService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->runAuthorized($request, 'roles', 'repo', function () use ($request): JsonResponse {
            $resultado = $this->adminRoleService->list($request->query('search'));

            return ApiResponse::success($resultado);
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->runAuthorized($request, 'roles', 'alta', function () use ($request): JsonResponse {
            $resultado = $this->adminRoleService->create($request->all());

            return ApiResponse::success($resultado, 'ok', 201);
        });
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->runAuthorized($request, 'roles', 'modi', function () use ($request, $id): JsonResponse {
            $resultado = $this->adminRoleService->update($id, $request->all());

            return ApiResponse::success($resultado);
        });
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        return $this->runAuthorized($request, 'roles', 'baja', function () use ($id): JsonResponse {
            $this->adminRoleService->delete($id);

            return ApiResponse::success([]);
        });
    }

    public function showAttributes(Request $request, int $id): JsonResponse
    {
        return $this->runAuthorized($request, 'roles', 'repo', function () use ($id): JsonResponse {
            return ApiResponse::success($this->roleAttributesService->getForRole($id));
        });
    }

    public function updateAttributes(Request $request, int $id): JsonResponse
    {
        return $this->runAuthorized($request, 'roles', 'modi', function () use ($request, $id): JsonResponse {
            $items = (array) $request->input('items', []);
            $resultado = $this->roleAttributesService->syncForRole($id, $items);

            return ApiResponse::success($resultado);
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
