<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\AuthFlowException;
use App\Exceptions\PedidosWebBusinessException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\TratativaService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TratativaController extends Controller
{
    public function __construct(
        private readonly TratativaService $tratativaService,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    public function index(Request $request, string $cod): JsonResponse
    {
        return $this->resolver($request, 'repo', fn () => $this->tratativaService->listar($cod));
    }

    public function store(Request $request, string $cod): JsonResponse
    {
        $validated = $request->validate([
            'comentario' => ['required', 'string'],
            'id_resultado' => ['nullable', 'integer'],
            'proxima_fecha' => ['nullable', 'date'],
            'proxima_accion' => ['nullable', 'string'],
        ]);

        return $this->resolver($request, 'modi', fn () => $this->tratativaService->crear(
            $cod,
            $validated,
            $request->user()
        ));
    }

    private function resolver(Request $request, string $tipoPermiso, callable $resolver): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $this->visibilityPermissionGuard->ensurePermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.tratativasPresupuestos'),
                $tipoPermiso
            );
            $resultado = $resolver();
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
