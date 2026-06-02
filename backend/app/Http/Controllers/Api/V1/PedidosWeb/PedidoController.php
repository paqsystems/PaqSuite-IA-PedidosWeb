<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\AuthFlowException;
use App\Exceptions\PedidosWebBusinessException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\PedidoService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function __construct(
        private readonly PedidoService $pedidoService,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    public function store(Request $request): JsonResponse
    {
        return $this->grabarAlias($request, 'pedido', null);
    }

    public function update(Request $request, string $codPedido): JsonResponse
    {
        return $this->grabarAlias($request, 'pedido', $codPedido);
    }

    public function show(Request $request, string $codPedido): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $this->visibilityPermissionGuard->ensurePermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.cargaComprobantes'),
                'repo'
            );
            $resultado = $this->pedidoService->getComprobante($codPedido, $user);
        } catch (AuthFlowException|PedidosWebBusinessException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($resultado);
    }

    public function destroy(Request $request, string $codPedido): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $this->visibilityPermissionGuard->ensurePermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.cargaComprobantes'),
                'baja'
            );
            $this->pedidoService->eliminarPedido($codPedido, $user);
        } catch (AuthFlowException|PedidosWebBusinessException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success([]);
    }

    public function iniciarEdicion(Request $request, string $codPedido): JsonResponse
    {
        return $this->edicionAction($request, $codPedido, 'iniciar');
    }

    public function touchActividad(Request $request, string $codPedido): JsonResponse
    {
        return $this->edicionAction($request, $codPedido, 'touch');
    }

    public function cancelarEdicion(Request $request, string $codPedido): JsonResponse
    {
        return $this->edicionAction($request, $codPedido, 'cancelar');
    }

    protected function grabarAlias(Request $request, string $accionGrabacion, ?string $codPedido): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $validated = $request->validate([
            'cabecera' => ['required', 'array'],
            'cabecera.cod_cliente' => ['required', 'string'],
            'renglones' => ['required', 'array', 'min:1'],
            'cod_pedido_origen' => ['nullable', 'string'],
            'cod_presupuesto_origen' => ['nullable', 'string'],
        ]);

        $payload = [
            ...$validated,
            'accionGrabacion' => $accionGrabacion,
            'cod_pedido' => $codPedido,
        ];

        try {
            $this->visibilityPermissionGuard->ensurePermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.cargaComprobantes'),
                $codPedido === null ? 'alta' : 'modi'
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

    private function edicionAction(Request $request, string $codPedido, string $action): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $this->visibilityPermissionGuard->ensurePermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.cargaComprobantes'),
                'modi'
            );

            $resultado = match ($action) {
                'iniciar' => $this->pedidoService->iniciarEdicion($codPedido, $user),
                'touch' => $this->pedidoService->touchActividad($codPedido, $user),
                default => $this->pedidoService->cancelarEdicion($codPedido, $user),
            };
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
