<?php

namespace App\Http\Controllers\Api\V1\Config;

use App\Exceptions\AuthFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Config\ParametrosConsultaService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ParametrosController extends Controller
{
    public function __construct(
        private readonly ParametrosConsultaService $parametrosConsultaService,
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
                (string) config('paqsuite_visibility.procedimientos.consultaParametros'),
                'repo'
            );
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        $programa = (string) $request->query('programa', 'PedidosWeb');

        return ApiResponse::success($this->parametrosConsultaService->listarPorPrograma($programa));
    }
}
