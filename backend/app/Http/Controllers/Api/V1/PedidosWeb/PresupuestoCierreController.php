<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\AuthFlowException;
use App\Exceptions\PedidosWebBusinessException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\PresupuestoCierreService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PresupuestoCierreController extends Controller
{
    public function __construct(
        private readonly PresupuestoCierreService $presupuestoCierreService,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    public function cerrar(Request $request, string $cod): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $validated = $request->validate([
            'id_motivo' => ['required', 'integer'],
            'observacion' => ['nullable', 'string'],
        ]);

        try {
            $this->visibilityPermissionGuard->ensurePermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.cargaComprobantes'),
                'modi'
            );

            $resultado = $this->presupuestoCierreService->cerrarRechazo(
                $cod,
                (int) $validated['id_motivo'],
                $validated['observacion'] ?? null,
                $user
            );
        } catch (AuthFlowException|PedidosWebBusinessException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($resultado);
    }
}
