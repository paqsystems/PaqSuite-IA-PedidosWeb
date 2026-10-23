<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

function bench(string $label, string $sql, array $bindings = []): void
{
    $start = microtime(true);
    $rows = DB::select($sql, $bindings);
    echo sprintf("%-50s rows=%5d time=%6.2fs\n", $label, count($rows), microtime(true) - $start);
}

$notExists = <<<'SQL'
AND NOT EXISTS (
    SELECT 1 FROM pq_pedidosweb_articulos AS pw
    WHERE NULLIF(LTRIM(RTRIM(CAST(pw.[base] AS NVARCHAR(50)))), '') IS NOT NULL
      AND LTRIM(RTRIM(CAST(pw.[base] AS NVARCHAR(50)))) = LTRIM(RTRIM(CAST(a.codigo AS NVARCHAR(50))))
      AND LTRIM(RTRIM(CAST(pw.codigo AS NVARCHAR(50)))) <> LTRIM(RTRIM(CAST(a.codigo AS NVARCHAR(50))))
)
SQL;

$usaEsc = "(a.usa_esc IS NULL OR UPPER(LTRIM(RTRIM(CAST(a.usa_esc AS NVARCHAR(20))))) <> 'B')";

$ctes = <<<'SQL'
WITH pedidos_ingresados AS (
    SELECT pd.cod_articulo, SUM(pd.cantidad) AS comprometido_web
    FROM pq_pedidosweb_pedidosdetalle pd
    INNER JOIN pq_pedidosweb_pedidoscabecera pc ON pd.cod_pedido = pc.cod_pedido
    WHERE pc.estado = 0 GROUP BY pd.cod_articulo
),
pedidos_ingresados_base AS (
    SELECT a3.base AS cod_base, SUM(d2.cantidad) AS comprometido_base_web
    FROM pq_pedidosweb_pedidosdetalle d2
    INNER JOIN pq_pedidosweb_pedidoscabecera c2 ON d2.cod_pedido = c2.cod_pedido
    INNER JOIN pq_pedidosweb_articulos a3 ON d2.cod_articulo = a3.codigo
    WHERE c2.estado = 0 AND NULLIF(LTRIM(RTRIM(CAST(a3.base AS NVARCHAR(50)))), '') IS NOT NULL
    GROUP BY a3.base
),
stock_por_base AS (
    SELECT a2.base AS cod_base, SUM(s2.stock) AS stock_base, SUM(s2.comprometido) AS comprometido_base
    FROM pq_pedidosweb_stock s2
    INNER JOIN pq_pedidosweb_articulos a2 ON s2.cod_articulo = a2.codigo
    WHERE NULLIF(LTRIM(RTRIM(CAST(a2.base AS NVARCHAR(50)))), '') IS NOT NULL
    GROUP BY a2.base
)
SQL;

echo DB::selectOne('SELECT DB_NAME() AS db')->db.PHP_EOL.PHP_EOL;

bench('A) solo articulos + usa_esc + order', "SELECT TOP (10000) a.codigo FROM pq_pedidosweb_articulos a WHERE {$usaEsc} ORDER BY a.descripcion");

bench('B) + NOT EXISTS', "SELECT TOP (10000) a.codigo FROM pq_pedidosweb_articulos a WHERE {$usaEsc} {$notExists} ORDER BY a.descripcion");

bench('C) + stock joins sin CTE', "SELECT TOP (10000) a.codigo
    FROM pq_pedidosweb_articulos a
    LEFT JOIN pq_pedidosweb_stock s ON s.cod_articulo = a.codigo
    WHERE {$usaEsc} {$notExists}
    ORDER BY a.descripcion");

bench('D) CTEs completos', "{$ctes}
    SELECT TOP (10000) a.codigo,
      (ISNULL(s.stock,0)-ISNULL(s.comprometido,0)-ISNULL(piw.comprometido_web,0)) AS disp
    FROM pq_pedidosweb_articulos a
    LEFT JOIN pq_pedidosweb_stock s ON s.cod_articulo = a.codigo
    LEFT JOIN stock_por_base bs ON bs.cod_base = a.base
    LEFT JOIN pedidos_ingresados piw ON piw.cod_articulo = a.codigo
    LEFT JOIN pedidos_ingresados_base pib ON pib.cod_base = a.base
    WHERE {$usaEsc} {$notExists}
    ORDER BY a.descripcion");
