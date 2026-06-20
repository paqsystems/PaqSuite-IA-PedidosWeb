<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\AuthFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Admin\AdminPermisoService;
use App\Services\Security\AdminSecurityAccessService;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminUsuarioLookupController extends Controller
{
    public function __construct(
        private readonly AdminPermisoService $adminPermisoService,
        private readonly AdminSecurityAccessService $adminSecurityAccessService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $procedimiento = (string) config('paqsuite_admin_security.procedimientos.permisos');
            $this->adminSecurityAccessService->ensure($user, $procedimiento, 'repo');
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        $resultado = $this->adminPermisoService->lookupUsuarios(
            $request->query('search'),
            (int) $request->query('page', 1),
            (int) $request->query('pageSize', 20),
        );

        return ApiResponse::success($resultado);
    }
}
