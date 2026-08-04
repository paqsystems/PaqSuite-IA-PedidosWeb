<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\AuthFlowException;
use App\Exceptions\PedidosWebBusinessException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\CabeceraInicialService;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CatalogosReferencialesController extends Controller
{
    public function __construct(
        private readonly CabeceraInicialService $cabeceraInicialService,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
        private readonly PedidosWebVisibilityGuard $pedidosWebVisibilityGuard,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/perfiles",
     *     summary="Perfiles",
     *     tags={"Maestros y Tablas"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(response=200, description="Listado de perfiles", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeCatalogoPerfiles")),
     *     @OA\Response(response=400, description="Tenant invalido"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function perfiles(Request $request): JsonResponse
    {
        return $this->okCatalogo($request, fn (): array => $this->cabeceraInicialService->listPerfiles());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/condiciones-venta",
     *     summary="Condiciones de venta",
     *     tags={"Maestros y Tablas"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(response=200, description="Listado de condiciones de venta", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeCatalogoCondicionesVenta")),
     *     @OA\Response(response=400, description="Tenant invalido"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function condicionesVenta(Request $request): JsonResponse
    {
        return $this->okCatalogo($request, fn (): array => $this->cabeceraInicialService->listCondicionesVenta());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/transportes",
     *     summary="Transportes",
     *     tags={"Maestros y Tablas"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(response=200, description="Listado de transportes", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeCatalogoTransportes")),
     *     @OA\Response(response=400, description="Tenant invalido"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function transportes(Request $request): JsonResponse
    {
        return $this->okCatalogo($request, fn (): array => $this->cabeceraInicialService->listTransportes());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/listas-precios",
     *     summary="Listas de precios",
     *     tags={"Maestros y Tablas"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(response=200, description="Listado de listas de precios", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeCatalogoListasPrecios")),
     *     @OA\Response(response=400, description="Tenant invalido"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function listasPrecios(Request $request): JsonResponse
    {
        return $this->okCatalogo($request, fn (): array => $this->cabeceraInicialService->listListasPrecios());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clientes/{codCliente}/direcciones-entrega",
     *     summary="Direcciones de entrega",
     *     tags={"Maestros y Tablas"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="codCliente", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Direcciones de entrega del cliente", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeCatalogoDireccionesEntrega")),
     *     @OA\Response(response=400, description="Tenant invalido"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso o cliente fuera de alcance"),
     *     @OA\Response(response=404, description="Cliente no visible")
     * )
     */
    public function direccionesEntrega(Request $request, string $codCliente): JsonResponse
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
            $this->pedidosWebVisibilityGuard->ensureCodClienteVisible($user, $codCliente);

            return ApiResponse::success($this->cabeceraInicialService->listDireccionesEntrega($codCliente));
        } catch (AuthFlowException|PedidosWebBusinessException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }

    /**
     * @param  callable(): list<array<string, mixed>>  $loader
     */
    private function okCatalogo(Request $request, callable $loader): JsonResponse
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

            return ApiResponse::success($loader());
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }
    }
}
