<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\AuthFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\PqPedidoswebMotivoCierre;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MotivoCierreController extends Controller
{
    public function __construct(
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
                (string) config('paqsuite_visibility.procedimientos.cargaComprobantes'),
                'repo'
            );
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        $query = PqPedidoswebMotivoCierre::query()->orderBy('descripcion');

        if (filled($request->query('tipo_cierre'))) {
            $query->where('tipo_cierre', (string) $request->query('tipo_cierre'));
        }

        if (($request->query('activo') ?? '1') !== '') {
            $query->where('activo', filter_var($request->query('activo', '1'), FILTER_VALIDATE_BOOLEAN));
        }

        $items = $query->get()->map(static fn (PqPedidoswebMotivoCierre $motivo): array => [
            'id_motivo' => (int) $motivo->id_motivo,
            'tipo_cierre' => (string) $motivo->tipo_cierre,
            'descripcion' => (string) $motivo->descripcion,
            'activo' => (bool) $motivo->activo,
        ])->values()->all();

        return ApiResponse::success(['items' => $items]);
    }
}
