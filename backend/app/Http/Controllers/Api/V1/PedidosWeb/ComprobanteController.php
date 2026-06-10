<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\AuthFlowException;
use App\Exceptions\PedidosWebBusinessException;
use App\Http\Controllers\Api\V1\PedidosWeb\Support\ComprobanteGrabacionPayload;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\PedidoService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ComprobanteController extends Controller
{
    public function __construct(
        private readonly PedidoService $pedidoService,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    public function grabar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $validated = $request->validate([
            'accionGrabacion' => ['required', 'string', 'in:pedido,presupuesto'],
        ]);

        try {
            $permiso = ($request->input('cod_pedido') ?? null) === null ? 'alta' : 'modi';
            $this->visibilityPermissionGuard->ensurePermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.cargaComprobantes'),
                $permiso
            );

            $payload = ComprobanteGrabacionPayload::fromRequest(
                $request,
                (string) $validated['accionGrabacion'],
            );
            $resultado = $this->pedidoService->grabarComprobante($payload, $user);
        } catch (AuthFlowException|PedidosWebBusinessException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($resultado);
    }

    public function copiar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $validated = $request->validate([
            'codComprobanteOrigen' => ['required', 'string'],
            'tipoDestino' => ['required', 'string', 'in:pedido,presupuesto'],
        ]);

        try {
            $this->visibilityPermissionGuard->ensurePermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.cargaComprobantes'),
                'alta'
            );
            $resultado = $this->pedidoService->copiarComprobante($validated, $user);
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
