<?php

namespace App\Services\PedidosWeb;

use App\Models\PqPedidoswebArticulo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lookup de artículos para carga de comprobante — una sola consulta SQL.
 *
 * @see docs/02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md §3
 */
final class ArticuloCargaLookupService
{
    private const BASE_TRIM_SQL = 'LTRIM(RTRIM(CAST(a.[base] AS NVARCHAR(50))))';

    private const BASE_NOT_EMPTY_SQL = "NULLIF(".self::BASE_TRIM_SQL.", '') IS NOT NULL";

    /**
     * @param  list<string>  $codigos
     * @return list<array{
     *     codArticulo: string,
     *     descripcion: string,
     *     porcIva: float,
     *     bonificacion: float,
     *     precio: float,
     *     disponibleNeto: float,
     *     disponibleNetoBase: float|null
     * }>
     */
    public function buscar(
        ?string $q,
        int $pageSize,
        int $codLista,
        array $codigos = [],
        bool $soloCatalogo = false,
    ): array {
        if (! Schema::hasTable('pq_pedidosweb_articulos')) {
            return [];
        }

        $pageSize = min(10000, max(1, $pageSize));
        $solicitudPorCodigos = $codigos !== [];
        $incluirDisponible = ! $soloCatalogo;
        $hasStockTable = $incluirDisponible && Schema::hasTable('pq_pedidosweb_stock');
        $hasListaPreciosTable = Schema::hasTable('pq_pedidosweb_listaprecios_articulos');

        $stockExpr = $hasStockTable ? 'ISNULL(s.stock, 0)' : '0';
        $comprometidoExpr = $hasStockTable ? 'ISNULL(s.comprometido, 0)' : '0';
        $stockBaseExpr = $hasStockTable ? 'ISNULL(b.stock, 0)' : '0';
        $comprometidoBaseExpr = $hasStockTable ? 'ISNULL(b.comprometido, 0)' : '0';
        $precioExpr = $codLista > 0 && $hasListaPreciosTable ? 'ISNULL(lp.precio, 0)' : '0';

        // CC PQ #5: listbox carga sin comprometido_web (pedidos ingresados) hasta optimizar SQL.
        $disponibleExpr = "({$stockExpr} - {$comprometidoExpr})";
        $disponibleBaseExpr = 'CASE WHEN '.self::BASE_NOT_EMPTY_SQL
            ." THEN ({$stockBaseExpr} - {$comprometidoBaseExpr})"
            .' ELSE NULL END';

        $selectSql = <<<SQL
a.codigo,
a.descripcion,
a.porc_iva,
a.bonificacion,
{$precioExpr} AS precio,
{$disponibleExpr} AS disponible_neto,
{$disponibleBaseExpr} AS disponible_neto_base
SQL;

        $joins = [];
        if ($hasStockTable) {
            $joins[] = 'LEFT JOIN [pq_pedidosweb_stock] AS [s] ON [s].[cod_articulo] = [a].[codigo]';
            $joins[] = 'LEFT JOIN [pq_pedidosweb_stock] AS [b] ON [b].[cod_articulo] = '.self::BASE_TRIM_SQL
                .' AND '.self::BASE_NOT_EMPTY_SQL;
        }
        if ($codLista > 0 && $hasListaPreciosTable) {
            $joins[] = 'LEFT JOIN [pq_pedidosweb_listaprecios_articulos] AS [lp] ON [lp].[cod_articulo] = [a].[codigo] AND [lp].[cod_lista] = ?';
        }

        $fromSql = '[pq_pedidosweb_articulos] AS [a]';
        if ($joins !== []) {
            $fromSql .= ' '.implode(' ', $joins);
        }

        $query = DB::table(DB::raw($fromSql))
            ->selectRaw($selectSql)
            ->limit($pageSize);

        $bindings = [];
        if ($codLista > 0 && $hasListaPreciosTable) {
            $bindings[] = $codLista;
        }

        if ($solicitudPorCodigos) {
            $codigos = array_values(array_unique(array_filter(array_map(
                static fn (mixed $codigo): string => trim((string) $codigo),
                $codigos
            ), static fn (string $codigo): bool => $codigo !== '')));

            if ($codigos === []) {
                return [];
            }

            $query->whereIn('a.codigo', $codigos);
        } else {
            $this->applyExcluirArticulosBaseCarga($query);
        }

        if (filled($q)) {
            $search = '%'.trim((string) $q).'%';
            $query->whereRaw(
                '(ISNULL(CAST(a.codigo AS NVARCHAR(50)), \'\') + ISNULL(CAST(a.descripcion AS NVARCHAR(255)), \'\')) LIKE ?',
                [$search],
            );
        }

        $query->orderBy('a.descripcion');

        foreach ($bindings as $binding) {
            $query->addBinding($binding, 'join');
        }

        return collect($query->get())
            ->map(function (object $row): array {
                $disponibleNetoBase = $row->disponible_neto_base;

                return [
                    'codArticulo' => (string) $row->codigo,
                    'descripcion' => (string) ($row->descripcion ?? ''),
                    'porcIva' => round((float) ($row->porc_iva ?? 0), 4),
                    'bonificacion' => round((float) ($row->bonificacion ?? 0), 4),
                    'precio' => round((float) ($row->precio ?? 0), 2),
                    'disponibleNeto' => round((float) ($row->disponible_neto ?? 0), 2),
                    'disponibleNetoBase' => $disponibleNetoBase !== null
                        ? round((float) $disponibleNetoBase, 2)
                        : null,
                ];
            })
            ->values()
            ->all();
    }

    private function applyExcluirArticulosBaseCarga(Builder $query): void
    {
        $query->where(function (Builder $builder): void {
            $builder->whereNull('a.usa_esc')
                ->orWhereRaw(
                    'UPPER(LTRIM(RTRIM(CAST(a.usa_esc AS NVARCHAR(20))))) <> ?',
                    [PqPedidoswebArticulo::MARCA_USA_ESC_BASE],
                );
        });

        $query->whereNotExists(function (Builder $subquery): void {
            $subquery->selectRaw('1')
                ->from('pq_pedidosweb_articulos as pw_art_presentacion')
                ->whereRaw("NULLIF(LTRIM(RTRIM(CAST(pw_art_presentacion.[base] AS NVARCHAR(50)))), '') IS NOT NULL")
                ->whereRaw(
                    'LTRIM(RTRIM(CAST(pw_art_presentacion.[base] AS NVARCHAR(50)))) = LTRIM(RTRIM(CAST(a.codigo AS NVARCHAR(50))))',
                )
                ->whereRaw(
                    'LTRIM(RTRIM(CAST(pw_art_presentacion.codigo AS NVARCHAR(50)))) <> LTRIM(RTRIM(CAST(a.codigo AS NVARCHAR(50))))',
                );
        });
    }
}
