<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PedidosWeb\ArticuloCargaLookupService;
use Illuminate\Support\Facades\DB;

function bench(string $label, callable $fn): void
{
    $start = microtime(true);
    $result = $fn();
    $rows = is_countable($result) ? count($result) : 0;
    echo sprintf("%-45s rows=%5d time=%6.2fs\n", $label, $rows, microtime(true) - $start);
}

$service = new ArticuloCargaLookupService();

echo DB::selectOne('SELECT DB_NAME() AS db')->db.PHP_EOL.PHP_EOL;

bench('browse sin lista / stock', fn () => $service->buscar(null, 10000, 0));
bench('browse con lista=1 / stock', fn () => $service->buscar(null, 10000, 1));
bench('solo_catalogo sin lista', fn () => $service->buscar(null, 10000, 0, [], true));
bench('solo_catalogo con lista=1', fn () => $service->buscar(null, 10000, 1, [], true));

// SQL directo: join lista sin CTEs stock
bench('SQL directo articulos+lp lista=1', function () {
    return DB::select(
        'SELECT TOP (10000) a.codigo, ISNULL(lp.precio, 0) AS precio
         FROM pq_pedidosweb_articulos a
         LEFT JOIN pq_pedidosweb_listaprecios_articulos lp
           ON lp.cod_articulo = a.codigo AND lp.cod_lista = ?
         WHERE (a.usa_esc IS NULL OR UPPER(LTRIM(RTRIM(CAST(a.usa_esc AS NVARCHAR(20))))) <> ?)
         ORDER BY a.descripcion',
        [1, 'B'],
    );
});

bench('SQL directo articulos sin lp', function () {
    return DB::select(
        'SELECT TOP (10000) a.codigo
         FROM pq_pedidosweb_articulos a
         WHERE (a.usa_esc IS NULL OR UPPER(LTRIM(RTRIM(CAST(a.usa_esc AS NVARCHAR(20))))) <> ?)
         ORDER BY a.descripcion',
        ['B'],
    );
});
