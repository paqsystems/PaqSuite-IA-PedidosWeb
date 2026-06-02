<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\AuthFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\PqPedidoswebArticulo;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ArticuloController extends Controller
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

        $query = PqPedidoswebArticulo::query()->orderBy('codigo');

        if (filled($request->query('q'))) {
            $search = '%'.trim((string) $request->query('q')).'%';
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('codigo', 'like', $search)
                    ->orWhere('descripcion', 'like', $search);
            });
        }

        $pageSize = min(50, max(1, (int) ($request->query('page_size') ?? 20)));
        $items = $query
            ->limit($pageSize)
            ->get()
            ->map(static fn (PqPedidoswebArticulo $articulo): array => [
                'codArticulo' => (string) $articulo->codigo,
                'descripcion' => (string) $articulo->descripcion,
                'porcIva' => (float) ($articulo->porc_iva ?? 0),
                'bonificacion' => (float) ($articulo->bonificacion ?? 0),
            ])
            ->values()
            ->all();

        return ApiResponse::success(['items' => $items]);
    }
}
