<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\AuthFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\ConsultaListadoService;
use App\Services\PedidosWeb\DetallePedidosConsultaService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ConsultaController extends Controller
{
    public function __construct(
        private readonly ConsultaListadoService $consultaListadoService,
        private readonly DetallePedidosConsultaService $detallePedidosConsultaService,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    public function pedidosIngresados(Request $request): JsonResponse
    {
        return $this->resolverConsulta($request, 'consultasPedidosIngresados', fn () => $this->consultaListadoService->pedidosIngresados(
            $request->user(),
            $request->query()
        ));
    }

    public function pedidosPendientes(Request $request): JsonResponse
    {
        return $this->resolverConsulta($request, 'consultasPedidosPendientes', fn () => $this->consultaListadoService->pedidosPendientes(
            $request->user(),
            $request->query()
        ));
    }

    public function presupuestos(Request $request): JsonResponse
    {
        return $this->resolverConsulta($request, 'consultasPresupuestos', fn () => $this->consultaListadoService->presupuestos(
            $request->user(),
            $request->query()
        ));
    }

    public function stock(Request $request): JsonResponse
    {
        return $this->resolverConsulta($request, 'consultasStock', fn () => $this->consultaListadoService->stock(
            $request->query()
        ));
    }

    public function deuda(Request $request): JsonResponse
    {
        return $this->resolverConsulta($request, 'consultasDeuda', fn () => $this->consultaListadoService->deuda(
            $request->user(),
            $request->query()
        ));
    }

    public function cheques(Request $request): JsonResponse
    {
        return $this->resolverConsulta($request, 'consultasCheques', fn () => $this->consultaListadoService->cheques(
            $request->user(),
            $request->query()
        ));
    }

    public function historialVentas(Request $request): JsonResponse
    {
        return $this->resolverConsulta($request, 'consultasHistorialVentas', fn () => $this->consultaListadoService->historialVentas(
            $request->user(),
            $request->query()
        ));
    }

    public function detallePedidos(Request $request): JsonResponse
    {
        return $this->resolverConsulta($request, 'consultasDetallePedidos', fn () => $this->detallePedidosConsultaService->listar(
            $request->user(),
            $request->query()
        ));
    }

    private function resolverConsulta(Request $request, string $configKey, callable $resolver): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $this->visibilityPermissionGuard->ensurePermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.'.$configKey),
                'repo'
            );
            $resultado = $resolver();
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
