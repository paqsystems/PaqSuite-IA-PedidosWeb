<?php

/**
 * Volcado de SQL generado por ArticuloCargaLookupService (solo lectura, no ejecuta).
 * Uso: php scripts/dump-articulo-carga-sql.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PqPedidoswebArticulo;
use App\Services\PedidosWeb\ArticuloCargaLookupService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function buildArticuloCargaSql(
    ?string $q,
    int $pageSize,
    int $codLista,
    array $codigos,
    bool $soloCatalogo,
): string {
    $baseTrim = 'LTRIM(RTRIM(CAST(a.[base] AS NVARCHAR(50))))';
    $baseNotEmpty = "NULLIF({$baseTrim}, '') IS NOT NULL";

    $solicitudPorCodigos = $codigos !== [];
    $incluirDisponible = ! $soloCatalogo;
    $hasStockTable = $incluirDisponible && Schema::hasTable('pq_pedidosweb_stock');
    $hasListaPreciosTable = Schema::hasTable('pq_pedidosweb_listaprecios_articulos');

    $stockExpr = $hasStockTable ? 'ISNULL(s.stock, 0)' : '0';
    $comprometidoExpr = $hasStockTable ? 'ISNULL(s.comprometido, 0)' : '0';
    $stockBaseExpr = $hasStockTable ? 'ISNULL(bs.stock_base, 0)' : '0';
    $comprometidoBaseExpr = $hasStockTable ? 'ISNULL(bs.comprometido_base, 0)' : '0';
    $precioExpr = $codLista > 0 && $hasListaPreciosTable ? 'ISNULL(lp.precio, 0)' : '0';

    $hasPedidosTables = $hasStockTable
        && Schema::hasTable('pq_pedidosweb_pedidosdetalle')
        && Schema::hasTable('pq_pedidosweb_pedidoscabecera');

    $comprometidoWebExpr = $hasPedidosTables ? 'ISNULL(cw.comprometido_web, 0)' : '0';
    $comprometidoBaseWebExpr = $hasPedidosTables ? 'ISNULL(bw.comprometido_base_web, 0)' : '0';

    $disponibleExpr = "({$stockExpr} - {$comprometidoExpr} - {$comprometidoWebExpr})";
    $disponibleBaseExpr = "CASE WHEN {$baseNotEmpty} THEN ({$stockBaseExpr} - {$comprometidoBaseExpr} - {$comprometidoBaseWebExpr}) ELSE NULL END";

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
    $joinBindings = [];
    if ($hasStockTable) {
        $joins[] = 'LEFT JOIN [pq_pedidosweb_stock] AS [s] ON [s].[cod_articulo] = [a].[codigo]';

        $baseStockSub = DB::table('pq_pedidosweb_stock as s2')
            ->join('pq_pedidosweb_articulos as a2', 's2.cod_articulo', '=', 'a2.codigo')
            ->whereRaw("NULLIF(LTRIM(RTRIM(CAST(a2.[base] AS NVARCHAR(50)))), '') IS NOT NULL")
            ->groupBy('a2.base')
            ->selectRaw('a2.[base] AS cod_base, SUM(s2.stock) AS stock_base, SUM(s2.comprometido) AS comprometido_base');

        $joins[] = 'LEFT JOIN ('.$baseStockSub->toSql().') AS [bs] ON LTRIM(RTRIM(CAST([bs].[cod_base] AS NVARCHAR(50)))) = '
            .$baseTrim.' AND '.$baseNotEmpty;
        $joinBindings = array_merge($joinBindings, $baseStockSub->getBindings());
    }
    if ($codLista > 0 && $hasListaPreciosTable) {
        $joins[] = 'LEFT JOIN [pq_pedidosweb_listaprecios_articulos] AS [lp] ON [lp].[cod_articulo] = [a].[codigo] AND [lp].[cod_lista] = ?';
        $joinBindings[] = $codLista;
    }
    if ($hasPedidosTables) {
        $comprometidoWebSub = DB::table('pq_pedidosweb_pedidosdetalle as d')
            ->join('pq_pedidosweb_pedidoscabecera as c', 'd.cod_pedido', '=', 'c.cod_pedido')
            ->where('c.estado', 0)
            ->groupBy('d.cod_articulo')
            ->selectRaw('d.cod_articulo, SUM(d.cantidad) AS comprometido_web');

        $joins[] = 'LEFT JOIN ('.$comprometidoWebSub->toSql().') AS [cw] ON [cw].[cod_articulo] = [a].[codigo]';
        $joinBindings = array_merge($joinBindings, $comprometidoWebSub->getBindings());

        $baseWebSub = DB::table('pq_pedidosweb_pedidosdetalle as d2')
            ->join('pq_pedidosweb_pedidoscabecera as c2', 'd2.cod_pedido', '=', 'c2.cod_pedido')
            ->join('pq_pedidosweb_articulos as a3', 'd2.cod_articulo', '=', 'a3.codigo')
            ->where('c2.estado', 0)
            ->whereRaw("NULLIF(LTRIM(RTRIM(CAST(a3.[base] AS NVARCHAR(50)))), '') IS NOT NULL")
            ->groupBy('a3.base')
            ->selectRaw('a3.[base] AS cod_base, SUM(d2.cantidad) AS comprometido_base_web');

        $joins[] = 'LEFT JOIN ('.$baseWebSub->toSql().') AS [bw] ON LTRIM(RTRIM(CAST([bw].[cod_base] AS NVARCHAR(50)))) = '
            .$baseTrim.' AND '.$baseNotEmpty;
        $joinBindings = array_merge($joinBindings, $baseWebSub->getBindings());
    }

    $fromSql = '[pq_pedidosweb_articulos] AS [a]';
    if ($joins !== []) {
        $fromSql .= ' '.implode(' ', $joins);
    }

    $query = DB::table(DB::raw($fromSql))
        ->selectRaw($selectSql)
        ->limit($pageSize);

    if ($solicitudPorCodigos) {
        $codigos = array_values(array_unique(array_filter(array_map(
            static fn (mixed $codigo): string => trim((string) $codigo),
            $codigos
        ), static fn (string $codigo): bool => $codigo !== '')));
        $query->whereIn('a.codigo', $codigos);
    } else {
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

    if (filled($q)) {
        $search = '%'.trim((string) $q).'%';
        $query->whereRaw(
            '(ISNULL(CAST(a.codigo AS NVARCHAR(50)), \'\') + ISNULL(CAST(a.descripcion AS NVARCHAR(255)), \'\')) LIKE ?',
            [$search],
        );
    }

    $query->orderBy('a.descripcion');

    foreach ($joinBindings as $binding) {
        $query->addBinding($binding, 'join');
    }

    return vsprintf(str_replace('?', '%s', $query->toSql()), array_map(
        static fn ($b) => is_numeric($b) ? (string) $b : "'".str_replace("'", "''", (string) $b)."'",
        $query->getBindings()
    ));
}

$scenarios = [
    'A_catalogo_solo_catalogo_lista5' => [null, 10000, 5, [], true],
    'B_catalogo_completo_lista5' => [null, 10000, 5, [], false],
    'C_busqueda_completo_AJO_lista5' => ['AJO', 100, 5, [], false],
    'D_por_codigo_completo_lista5' => [null, 1, 5, ['AEP'], false],
];

foreach ($scenarios as $name => $args) {
    echo "\n========== {$name} ==========\n";
    echo buildArticuloCargaSql(...$args);
    echo "\n";
}

echo "\nMARCA_USA_ESC_BASE = ".PqPedidoswebArticulo::MARCA_USA_ESC_BASE."\n";
