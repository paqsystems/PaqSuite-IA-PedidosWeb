<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\AuthFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\LogIntegracionService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class IntegracionLogController extends Controller
{
    public function __construct(
        private readonly LogIntegracionService $logIntegracionService,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $this->visibilityPermissionGuard->ensurePermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.logsIntegracion'),
                'repo'
            );
            $resultado = $this->logIntegracionService->listar($request->query());
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
