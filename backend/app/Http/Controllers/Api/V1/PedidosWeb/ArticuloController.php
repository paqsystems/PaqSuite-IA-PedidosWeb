<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\AuthFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Contracts\PedidosWeb\ArticuloRepositoryInterface;
use App\Models\PqPedidoswebArticulo;
use App\Services\PedidosWeb\StockConsultaService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ArticuloController extends Controller
{
    public function __construct(
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
        private readonly ArticuloRepositoryInterface $articuloRepository,
        private readonly StockConsultaService $stockConsultaService,
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

        $codigos = $this->parseCodigosQuery($request);
        $solicitudPorCodigos = $codigos !== [];

        $query = PqPedidoswebArticulo::query()->orderBy('descripcion');

        if ($solicitudPorCodigos) {
            $query->whereIn('codigo', $codigos);
        } else {
            // Lookup de carga: excluir artículos BASE (usa_esc = 'B'). No aplica al refresh por codigos.
            $query->excluirArticulosBaseCarga();
        }

        if (filled($request->query('q'))) {
            $search = '%'.trim((string) $request->query('q')).'%';
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('codigo', 'like', $search)
                    ->orWhere('descripcion', 'like', $search);
            });
        }

        $pageSize = $solicitudPorCodigos
            ? min(1000, max(count($codigos), 1))
            : min(1000, max(1, (int) ($request->query('page_size') ?? 500)));
        $codLista = (int) ($request->query('lista_precios') ?? 0);
        $articulos = $query
            ->limit($pageSize)
            ->get();

        $stockPorCodigo = $this->stockConsultaService->lookupDisponibilidadPorCodigos(
            $articulos->map(static fn (PqPedidoswebArticulo $articulo): string => (string) $articulo->codigo)->all()
        );

        $items = $articulos
            ->map(function (PqPedidoswebArticulo $articulo) use ($codLista, $stockPorCodigo): array {
                $precio = 0.0;
                if ($codLista > 0) {
                    $precioLista = $this->articuloRepository->findPrecioLista($codLista, (string) $articulo->codigo);
                    $precio = (float) ($precioLista?->precio ?? 0);
                }

                $codArticulo = (string) $articulo->codigo;
                $stock = $stockPorCodigo[$codArticulo] ?? null;

                return [
                    'codArticulo' => $codArticulo,
                    'descripcion' => (string) $articulo->descripcion,
                    'porcIva' => (float) ($articulo->porc_iva ?? 0),
                    'bonificacion' => (float) ($articulo->bonificacion ?? 0),
                    'precio' => $precio,
                    'disponibleNeto' => (float) ($stock['disponibleNeto'] ?? 0),
                    'disponibleNetoBase' => $stock['disponibleNetoBase'] ?? null,
                ];
            })
            ->values()
            ->all();

        return ApiResponse::success(['items' => $items]);
    }

    /**
     * @return list<string>
     */
    private function parseCodigosQuery(Request $request): array
    {
        if (! filled($request->query('codigos'))) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $codigo): string => trim($codigo),
            explode(',', (string) $request->query('codigos'))
        ), static fn (string $codigo): bool => $codigo !== ''));
    }
}
