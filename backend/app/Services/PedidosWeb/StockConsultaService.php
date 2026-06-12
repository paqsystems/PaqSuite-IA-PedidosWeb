<?php

namespace App\Services\PedidosWeb;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Consulta de stock — agregación en SQL (una pasada por página).
 *
 * @see docs/02-producto/PedidosWeb/consulta-stock.md
 */
final class StockConsultaService
{
    private const BASE_NOT_EMPTY_SQL = "NULLIF(LTRIM(RTRIM(a.[base])), '') IS NOT NULL";

    /**
     * Disponibilidad informativa para listbox de carga (misma fórmula que consulta stock).
     *
     * @param  list<string>  $codigos
     * @return array<string, array{disponibleNeto: float, disponibleNetoBase: float|null}>
     *
     * @see docs/02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md §3.1
     */
    public function lookupDisponibilidadCargaPorCodigos(array $codigos): array
    {
        return $this->lookupDisponibilidadPorCodigos($codigos);
    }

    /**
     * Disponibilidad informativa con pedidos web (consulta stock y refresh por codigos).
     *
     * @param  list<string>  $codigos
     * @return array<string, array{disponibleNeto: float, disponibleNetoBase: float|null}>
     */
    public function lookupDisponibilidadPorCodigos(array $codigos): array
    {
        return $this->lookupDisponibilidadPorCodigosInternal($codigos, incluirComprometidoWeb: true);
    }

    /**
     * @param  list<string>  $codigos
     * @return array<string, array{disponibleNeto: float, disponibleNetoBase: float|null}>
     */
    private function lookupDisponibilidadPorCodigosInternal(array $codigos, bool $incluirComprometidoWeb): array
    {
        $codigos = array_values(array_unique(array_filter(array_map(
            static fn (mixed $codigo): string => trim((string) $codigo),
            $codigos
        ), static fn (string $codigo): bool => $codigo !== '')));

        if ($codigos === [] || ! Schema::hasTable('pq_pedidosweb_stock')) {
            return [];
        }

        try {
            $query = $this->buildStockQuery(['q' => null], $incluirComprometidoWeb);
            $query->whereIn('s.cod_articulo', $codigos);

            $resultado = [];

            foreach ($query->get() as $row) {
                $mapped = $this->mapRow($row);
                $resultado[$mapped['codArticulo']] = [
                    'disponibleNeto' => $mapped['disponibleNeto'],
                    'disponibleNetoBase' => $mapped['disponibleNetoBase'],
                ];
            }

            return $resultado;
        } catch (\Throwable) {
            return [];
        }
    }

    public function listar(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = min(100, max(1, (int) ($filters['page_size'] ?? 20)));

        if (! Schema::hasTable('pq_pedidosweb_stock')) {
            return $this->emptyResult($page, $pageSize);
        }

        $query = $this->buildStockQuery($filters, incluirComprometidoWeb: true);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        $items = collect($paginator->items())
            ->map(fn (object $row): array => $this->mapRow($row))
            ->values()
            ->all();

        $fechaProceso = DB::table('pq_pedidosweb_stock')->max('uma_fecha');

        return [
            'items' => $items,
            'page' => (int) $paginator->currentPage(),
            'page_size' => (int) $paginator->perPage(),
            'total' => (int) $paginator->total(),
            'total_pages' => (int) $paginator->lastPage(),
            'metadata' => [
                'fecha_proceso' => $fechaProceso !== null
                    ? (string) $fechaProceso
                    : null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function buildStockQuery(array $filters, bool $incluirComprometidoWeb = true): Builder
    {
        $hasPedidosTables = $incluirComprometidoWeb
            && Schema::hasTable('pq_pedidosweb_pedidosdetalle')
            && Schema::hasTable('pq_pedidosweb_pedidoscabecera');
        $hasArticulosTable = Schema::hasTable('pq_pedidosweb_articulos');

        $query = DB::table('pq_pedidosweb_stock as s');

        if ($hasArticulosTable) {
            $query->leftJoin('pq_pedidosweb_articulos as a', 's.cod_articulo', '=', 'a.codigo');
        }

        if ($hasPedidosTables) {
            $comprometidoWebSub = DB::table('pq_pedidosweb_pedidosdetalle as d')
                ->join('pq_pedidosweb_pedidoscabecera as c', 'd.cod_pedido', '=', 'c.cod_pedido')
                ->where('c.estado', 0)
                ->groupBy('d.cod_articulo')
                ->selectRaw('d.cod_articulo, SUM(d.cantidad) AS comprometido_web');

            $query->leftJoinSub($comprometidoWebSub, 'cw', 'cw.cod_articulo', '=', 's.cod_articulo');
        }

        if ($hasArticulosTable) {
            $baseStockSub = DB::table('pq_pedidosweb_stock as s2')
                ->join('pq_pedidosweb_articulos as a2', 's2.cod_articulo', '=', 'a2.codigo')
                ->whereRaw("NULLIF(LTRIM(RTRIM(a2.[base])), '') IS NOT NULL")
                ->groupBy('a2.base')
                ->selectRaw('a2.[base] AS cod_base, SUM(s2.stock) AS stock_base, SUM(s2.comprometido) AS comprometido_base');

            $query->leftJoinSub($baseStockSub, 'bs', function (Builder $join): void {
                $join->on('bs.cod_base', '=', 'a.base')
                    ->whereRaw(self::BASE_NOT_EMPTY_SQL);
            });

            if ($hasPedidosTables) {
                $baseWebSub = DB::table('pq_pedidosweb_pedidosdetalle as d2')
                    ->join('pq_pedidosweb_pedidoscabecera as c2', 'd2.cod_pedido', '=', 'c2.cod_pedido')
                    ->join('pq_pedidosweb_articulos as a3', 'd2.cod_articulo', '=', 'a3.codigo')
                    ->where('c2.estado', 0)
                    ->whereRaw("NULLIF(LTRIM(RTRIM(a3.[base])), '') IS NOT NULL")
                    ->groupBy('a3.base')
                    ->selectRaw('a3.[base] AS cod_base, SUM(d2.cantidad) AS comprometido_base_web');

                $query->leftJoinSub($baseWebSub, 'bw', function (Builder $join): void {
                    $join->on('bw.cod_base', '=', 'a.base')
                        ->whereRaw(self::BASE_NOT_EMPTY_SQL);
                });
            }
        }

        $comprometidoWebExpr = $hasPedidosTables
            ? 'ISNULL(cw.comprometido_web, 0)'
            : '0';
        $comprometidoBaseWebExpr = $hasPedidosTables
            ? 'ISNULL(bw.comprometido_base_web, 0)'
            : '0';

        if ($hasArticulosTable) {
            $query->selectRaw(<<<SQL
s.cod_articulo,
a.descripcion,
s.stock,
s.comprometido,
{$comprometidoWebExpr} AS comprometido_web,
(s.stock - s.comprometido - {$comprometidoWebExpr}) AS disponible_neto,
a.[base] AS cod_base,
CASE WHEN NULLIF(LTRIM(RTRIM(a.[base])), '') IS NOT NULL THEN bs.stock_base END AS stock_base,
CASE WHEN NULLIF(LTRIM(RTRIM(a.[base])), '') IS NOT NULL THEN bs.comprometido_base END AS comprometido_base,
CASE WHEN NULLIF(LTRIM(RTRIM(a.[base])), '') IS NOT NULL THEN {$comprometidoBaseWebExpr} END AS comprometido_base_web,
CASE
    WHEN NULLIF(LTRIM(RTRIM(a.[base])), '') IS NOT NULL
    THEN (bs.stock_base - bs.comprometido_base - {$comprometidoBaseWebExpr})
END AS disponible_neto_base,
s.uma_fecha
SQL);
        } else {
            $query->selectRaw(<<<SQL
s.cod_articulo,
CAST('' AS nvarchar(255)) AS descripcion,
s.stock,
s.comprometido,
{$comprometidoWebExpr} AS comprometido_web,
(s.stock - s.comprometido - {$comprometidoWebExpr}) AS disponible_neto,
CAST(NULL AS nvarchar(50)) AS cod_base,
CAST(NULL AS decimal(18,4)) AS stock_base,
CAST(NULL AS decimal(18,4)) AS comprometido_base,
CAST(NULL AS decimal(18,4)) AS comprometido_base_web,
CAST(NULL AS decimal(18,4)) AS disponible_neto_base,
s.uma_fecha
SQL);
        }

        $query->orderBy('s.cod_articulo');

        if (filled($filters['q'] ?? null)) {
            $search = '%'.trim((string) $filters['q']).'%';
            $query->where(function (Builder $builder) use ($search, $hasArticulosTable): void {
                $builder->where('s.cod_articulo', 'like', $search);
                if ($hasArticulosTable) {
                    $builder->orWhere('a.descripcion', 'like', $search);
                }
            });
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRow(object $row): array
    {
        $codBase = trim((string) ($row->cod_base ?? ''));
        $hasBase = $codBase !== '';

        return [
            'codArticulo' => (string) $row->cod_articulo,
            'descripcion' => (string) ($row->descripcion ?? ''),
            'stock' => $this->roundDecimal($row->stock),
            'comprometido' => $this->roundDecimal($row->comprometido),
            'comprometidoWeb' => $this->roundDecimal($row->comprometido_web),
            'disponibleNeto' => $this->roundDecimal($row->disponible_neto),
            'codBase' => $hasBase ? $codBase : null,
            'stockBase' => $hasBase && $row->stock_base !== null ? $this->roundDecimal($row->stock_base) : null,
            'comprometidoBase' => $hasBase && $row->comprometido_base !== null ? $this->roundDecimal($row->comprometido_base) : null,
            'comprometidoBaseWeb' => $hasBase && $row->comprometido_base_web !== null ? $this->roundDecimal($row->comprometido_base_web) : null,
            'disponibleNetoBase' => $hasBase && $row->disponible_neto_base !== null ? $this->roundDecimal($row->disponible_neto_base) : null,
        ];
    }

    private function roundDecimal(mixed $value): float
    {
        return round((float) $value, 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyResult(int $page, int $pageSize): array
    {
        return [
            'items' => [],
            'page' => $page,
            'page_size' => $pageSize,
            'total' => 0,
            'total_pages' => 0,
            'metadata' => [
                'fecha_proceso' => null,
            ],
        ];
    }
}
