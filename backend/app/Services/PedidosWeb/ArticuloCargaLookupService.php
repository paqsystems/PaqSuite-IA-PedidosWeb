<?php

namespace App\Services\PedidosWeb;

use App\Models\PqPedidoswebArticulo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lookup de artículos para carga de comprobante — una sola consulta SQL.
 *
 * Stock/disponible: join directo a pq_pedidosweb_stock (presentación y fila del código base).
 * La fila base (cod_articulo = articulos.base) la carga el ERP con stock/comprometido agregados.
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
        $codigos = $this->normalizeCodigos($codigos);
        $solicitudPorCodigos = $codigos !== [];
        $incluirDisponible = ! $soloCatalogo && Schema::hasTable('pq_pedidosweb_stock');
        $hasListaPreciosTable = Schema::hasTable('pq_pedidosweb_listaprecios_articulos');
        $hasPedidosTables = $incluirDisponible
            && Schema::hasTable('pq_pedidosweb_pedidosdetalle')
            && Schema::hasTable('pq_pedidosweb_pedidoscabecera');

        if ($incluirDisponible) {
            [$sql, $bindings] = $this->buildSqlConDisponible(
                $pageSize,
                $codLista,
                $hasListaPreciosTable,
                $hasPedidosTables,
                $solicitudPorCodigos,
                $codigos,
                $q,
            );
        } else {
            [$sql, $bindings] = $this->buildSqlSoloCatalogo(
                $pageSize,
                $codLista,
                $hasListaPreciosTable,
                $solicitudPorCodigos,
                $codigos,
                $q,
            );
        }

        return collect(DB::select($sql, $bindings))
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

    /**
     * @param  list<string>  $codigos
     * @return array{0: string, 1: list<mixed>}
     */
    private function buildSqlConDisponible(
        int $pageSize,
        int $codLista,
        bool $hasListaPreciosTable,
        bool $hasPedidosTables,
        bool $solicitudPorCodigos,
        array $codigos,
        ?string $q,
    ): array {
        $ctes = [];
        if ($hasPedidosTables) {
            $ctes[] = <<<'SQL'
pedidos_ingresados AS (
    SELECT
        pd.cod_articulo,
        SUM(pd.cantidad) AS comprometido_web
    FROM pq_pedidosweb_pedidosdetalle AS pd
    INNER JOIN pq_pedidosweb_pedidoscabecera AS pc ON pd.cod_pedido = pc.cod_pedido
    WHERE pc.[estado] = 0
    GROUP BY pd.cod_articulo
),
pedidos_ingresados_base AS (
    SELECT
        LTRIM(RTRIM(CAST(a.[base] AS NVARCHAR(50)))) AS cod_base,
        SUM(piw.comprometido_web) AS comprometido_base_web
    FROM pedidos_ingresados AS piw
    INNER JOIN pq_pedidosweb_articulos AS a ON a.codigo = piw.cod_articulo
    WHERE NULLIF(LTRIM(RTRIM(CAST(a.[base] AS NVARCHAR(50)))), '') IS NOT NULL
    GROUP BY LTRIM(RTRIM(CAST(a.[base] AS NVARCHAR(50))))
)
SQL;
        }

        $comprometidoWebExpr = $hasPedidosTables ? 'ISNULL(piw.comprometido_web, 0)' : '0';
        $comprometidoBaseWebExpr = $hasPedidosTables ? 'ISNULL(pib.comprometido_base_web, 0)' : '0';
        $precioExpr = $codLista > 0 && $hasListaPreciosTable ? 'ISNULL(lp.precio, 0)' : '0';

        $joins = [
            'LEFT JOIN pq_pedidosweb_stock AS s ON s.cod_articulo = a.codigo',
            'LEFT JOIN pq_pedidosweb_stock AS s_base ON s_base.cod_articulo = '.self::BASE_TRIM_SQL
                .' AND '.self::BASE_NOT_EMPTY_SQL,
        ];
        if ($hasPedidosTables) {
            $joins[] = 'LEFT JOIN pedidos_ingresados AS piw ON piw.cod_articulo = a.codigo';
            $joins[] = 'LEFT JOIN pedidos_ingresados_base AS pib ON pib.cod_base = '.self::BASE_TRIM_SQL
                .' AND '.self::BASE_NOT_EMPTY_SQL;
        }
        if ($codLista > 0 && $hasListaPreciosTable) {
            $joins[] = 'LEFT JOIN pq_pedidosweb_listaprecios_articulos AS lp ON lp.cod_articulo = a.codigo AND lp.cod_lista = ?';
        }

        $disponibleExpr = "(ISNULL(s.stock, 0) - ISNULL(s.comprometido, 0) - {$comprometidoWebExpr})";
        $disponibleBaseExpr = 'CASE WHEN '.self::BASE_NOT_EMPTY_SQL
            ." THEN (ISNULL(s_base.stock, 0) - ISNULL(s_base.comprometido, 0) - {$comprometidoBaseWebExpr})"
            .' ELSE NULL END';

        $sql = ($ctes !== [] ? 'WITH '.implode(",\n", $ctes)."\n" : '')
            ."SELECT TOP ({$pageSize})\n"
            ."    a.codigo,\n"
            ."    a.descripcion,\n"
            ."    a.porc_iva,\n"
            ."    a.bonificacion,\n"
            ."    {$precioExpr} AS precio,\n"
            ."    {$disponibleExpr} AS disponible_neto,\n"
            ."    {$disponibleBaseExpr} AS disponible_neto_base\n"
            ."FROM pq_pedidosweb_articulos AS a\n"
            .implode("\n", $joins)."\n"
            .'WHERE 1=1';

        $bindings = [];
        if ($codLista > 0 && $hasListaPreciosTable) {
            $bindings[] = $codLista;
        }

        [$sql, $bindings] = $this->appendFiltrosBusqueda(
            $sql,
            $bindings,
            $solicitudPorCodigos,
            $codigos,
            $q,
        );

        $sql .= "\nORDER BY a.descripcion";

        return [$sql, $bindings];
    }

    /**
     * @param  list<string>  $codigos
     * @return array{0: string, 1: list<mixed>}
     */
    private function buildSqlSoloCatalogo(
        int $pageSize,
        int $codLista,
        bool $hasListaPreciosTable,
        bool $solicitudPorCodigos,
        array $codigos,
        ?string $q,
    ): array {
        $precioExpr = $codLista > 0 && $hasListaPreciosTable ? 'ISNULL(lp.precio, 0)' : '0';
        $joinLista = $codLista > 0 && $hasListaPreciosTable
            ? 'LEFT JOIN pq_pedidosweb_listaprecios_articulos AS lp ON lp.cod_articulo = a.codigo AND lp.cod_lista = ?'
            : '';

        $sql = "SELECT TOP ({$pageSize})\n"
            ."    a.codigo,\n"
            ."    a.descripcion,\n"
            ."    a.porc_iva,\n"
            ."    a.bonificacion,\n"
            ."    {$precioExpr} AS precio,\n"
            ."    CAST(0 AS DECIMAL(18, 4)) AS disponible_neto,\n"
            .'    CAST(NULL AS DECIMAL(18, 4)) AS disponible_neto_base'."\n"
            ."FROM pq_pedidosweb_articulos AS a\n"
            .$joinLista."\n"
            .'WHERE 1=1';

        $bindings = [];
        if ($codLista > 0 && $hasListaPreciosTable) {
            $bindings[] = $codLista;
        }

        [$sql, $bindings] = $this->appendFiltrosBusqueda(
            $sql,
            $bindings,
            $solicitudPorCodigos,
            $codigos,
            $q,
        );

        $sql .= "\nORDER BY a.descripcion";

        return [$sql, $bindings];
    }

    /**
     * @param  list<string>  $codigos
     * @param  list<mixed>  $bindings
     * @return array{0: string, 1: list<mixed>}
     */
    private function appendFiltrosBusqueda(
        string $sql,
        array $bindings,
        bool $solicitudPorCodigos,
        array $codigos,
        ?string $q,
    ): array {
        if ($solicitudPorCodigos) {
            $placeholders = implode(', ', array_fill(0, count($codigos), '?'));
            $sql .= "\nAND a.codigo IN ({$placeholders})";
            $bindings = array_merge($bindings, $codigos);
        } else {
            $sql .= "\nAND (a.usa_esc IS NULL OR UPPER(LTRIM(RTRIM(CAST(a.usa_esc AS NVARCHAR(20))))) <> ?)";
            $bindings[] = PqPedidoswebArticulo::MARCA_USA_ESC_BASE;
            $sql .= "\nAND NOT EXISTS (
    SELECT 1
    FROM pq_pedidosweb_articulos AS pw_art_presentacion
    WHERE NULLIF(LTRIM(RTRIM(CAST(pw_art_presentacion.[base] AS NVARCHAR(50)))), '') IS NOT NULL
      AND LTRIM(RTRIM(CAST(pw_art_presentacion.[base] AS NVARCHAR(50)))) = LTRIM(RTRIM(CAST(a.codigo AS NVARCHAR(50))))
      AND LTRIM(RTRIM(CAST(pw_art_presentacion.codigo AS NVARCHAR(50)))) <> LTRIM(RTRIM(CAST(a.codigo AS NVARCHAR(50))))
)";
        }

        if (filled($q)) {
            $sql .= "\nAND (ISNULL(CAST(a.codigo AS NVARCHAR(50)), '') + ISNULL(CAST(a.descripcion AS NVARCHAR(255)), '')) LIKE ?";
            $bindings[] = '%'.trim((string) $q).'%';
        }

        return [$sql, $bindings];
    }

    /**
     * @param  list<string>  $codigos
     * @return list<string>
     */
    private function normalizeCodigos(array $codigos): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $codigo): string => trim((string) $codigo),
            $codigos
        ), static fn (string $codigo): bool => $codigo !== '')));
    }
}
