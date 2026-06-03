<?php

namespace App\Services\PedidosWeb;

use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\PqPedidoswebPedidoDetalle;
use App\Models\User;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use App\Services\Visibility\VisibleClientsResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class DetallePedidosConsultaService
{
    public function __construct(
        private readonly VisibleClientsResolver $visibleClientsResolver,
        private readonly PedidosWebVisibilityGuard $pedidosWebVisibilityGuard,
        private readonly ConsultaListadoService $consultaListadoService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listar(User $user, array $filters): array
    {
        $cabTable = (new PqPedidoswebPedidoCabecera)->getTable();
        $detTable = (new PqPedidoswebPedidoDetalle)->getTable();

        $query = PqPedidoswebPedidoDetalle::query()
            ->select("{$detTable}.*")
            ->join($cabTable, "{$cabTable}.cod_pedido", '=', "{$detTable}.cod_pedido")
            ->whereIn(
                "{$cabTable}.cod_cliente",
                $this->visibleClientsResolver->visibleClientsForUser($user)->select('cod_client')
            )
            ->with([
                'cabecera.cliente',
                'cabecera.vendedor',
                'cabecera.condicionVenta',
                'cabecera.transporte',
                'cabecera.listaPrecios',
                'cabecera.perfil',
                'articulo',
            ]);

        $this->applyFilters($user, $query, $filters, $cabTable, $detTable);

        $query
            ->orderByDesc("{$cabTable}.fecha")
            ->orderBy("{$detTable}.cod_pedido")
            ->orderBy("{$detTable}.renglon");

        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = min(100, max(1, (int) ($filters['page_size'] ?? 20)));

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($pageSize, ["{$detTable}.*"], 'page', $page);

        return [
            'items' => collect($paginator->items())
                ->map(fn (PqPedidoswebPedidoDetalle $detalle): array => $this->mapDetalleItem($detalle))
                ->values()
                ->all(),
            'page' => (int) $paginator->currentPage(),
            'page_size' => (int) $paginator->perPage(),
            'total' => (int) $paginator->total(),
            'total_pages' => (int) $paginator->lastPage(),
            'metadata' => [
                'fecha_proceso' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(
        User $user,
        Builder $query,
        array $filters,
        string $cabTable,
        string $detTable,
    ): void {
        if (filled($filters['cod_cliente'] ?? null)) {
            $codCliente = (string) $filters['cod_cliente'];
            $this->pedidosWebVisibilityGuard->ensureCodClienteVisible($user, $codCliente);
            $query->where("{$cabTable}.cod_cliente", $codCliente);
        }

        if (filled($filters['cod_pedido'] ?? null)) {
            $query->where("{$detTable}.cod_pedido", (string) $filters['cod_pedido']);
        }

        if (filled($filters['estado'] ?? null)) {
            $query->where("{$cabTable}.estado", (int) $filters['estado']);
        }

        if (filled($filters['q'] ?? null)) {
            $term = '%'.addcslashes(trim((string) $filters['q']), '%_\\').'%';
            $query->where(function (Builder $builder) use ($term, $detTable): void {
                $builder
                    ->where("{$detTable}.cod_articulo", 'like', $term)
                    ->orWhere("{$detTable}.descripcion_articulo", 'like', $term)
                    ->orWhereHas('articulo', function (Builder $articuloQuery) use ($term): void {
                        $articuloQuery->where('descripcion', 'like', $term);
                    });
            });
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDetalleItem(PqPedidoswebPedidoDetalle $detalle): array
    {
        $cabecera = $detalle->cabecera;

        if ($cabecera === null) {
            return [
                'codPedido' => (string) $detalle->cod_pedido,
                'renglon' => (int) $detalle->renglon,
                'codArticulo' => (string) $detalle->cod_articulo,
                'descripcionArticulo' => $this->resolveDescripcionArticulo($detalle),
                'cantidad' => (float) $detalle->cantidad,
                'porcBonif' => (float) ($detalle->porc_bonif ?? 0),
                'precioLista' => $this->resolvePrecioLista($detalle),
                'precioNeto' => (float) ($detalle->precio_neto ?? 0),
                'importeBruto' => (float) ($detalle->precio_bruto ?? 0),
                'importeNeto' => (float) ($detalle->importe_neto ?? 0),
                'ivaNeto' => (float) ($detalle->iva ?? 0),
                'importeNetoConIva' => (float) ($detalle->importe_total ?? 0),
            ];
        }

        return [
            ...$this->consultaListadoService->mapComprobanteForConsulta($cabecera, 'codPedido'),
            'renglon' => (int) $detalle->renglon,
            'codArticulo' => (string) $detalle->cod_articulo,
            'descripcionArticulo' => $this->resolveDescripcionArticulo($detalle),
            'cantidad' => (float) $detalle->cantidad,
            'porcBonif' => (float) ($detalle->porc_bonif ?? 0),
            'precioLista' => $this->resolvePrecioLista($detalle),
            'precioNeto' => (float) ($detalle->precio_neto ?? 0),
            'importeBruto' => (float) ($detalle->precio_bruto ?? 0),
            'importeNeto' => (float) ($detalle->importe_neto ?? 0),
            'ivaNeto' => (float) ($detalle->iva ?? 0),
            'importeNetoConIva' => (float) ($detalle->importe_total ?? 0),
        ];
    }

    private function resolveDescripcionArticulo(PqPedidoswebPedidoDetalle $detalle): string
    {
        if (filled($detalle->descripcion_articulo)) {
            return (string) $detalle->descripcion_articulo;
        }

        return (string) ($detalle->articulo?->descripcion ?? '');
    }

    private function resolvePrecioLista(PqPedidoswebPedidoDetalle $detalle): float
    {
        if ($detalle->precio !== null) {
            return (float) $detalle->precio;
        }

        return (float) ($detalle->importe_lista ?? 0);
    }
}
