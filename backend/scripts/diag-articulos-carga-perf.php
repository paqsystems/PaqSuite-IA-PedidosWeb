<?php

/**
 * Diagnóstico de rendimiento — lookup artículos carga (solo lectura).
 * Uso: php scripts/diag-articulos-carga-perf.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PedidosWeb\ArticuloCargaLookupService;
use Illuminate\Support\Facades\DB;

function elapsed(float $start): string
{
    return number_format(microtime(true) - $start, 2).'s';
}

$dbInfo = DB::selectOne('SELECT DB_NAME() AS db_name, @@SERVERNAME AS server_name');
echo '=== Conexión ==='.PHP_EOL;
echo 'database: '.($dbInfo->db_name ?? '?').PHP_EOL;
echo 'server: '.($dbInfo->server_name ?? '?').PHP_EOL.PHP_EOL;

$tables = [
    'pq_pedidosweb_articulos',
    'pq_pedidosweb_stock',
    'pq_pedidosweb_pedidosdetalle',
    'pq_pedidosweb_pedidoscabecera',
    'pq_pedidosweb_listaprecios_articulos',
];

echo '=== Conteos ==='.PHP_EOL;
foreach ($tables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "{$table}: {$count}".PHP_EOL;
    } catch (Throwable $exception) {
        echo "{$table}: ERROR — ".$exception->getMessage().PHP_EOL;
    }
}
echo PHP_EOL;

$service = new ArticuloCargaLookupService();

echo '=== Lookup API (ArticuloCargaLookupService) ==='.PHP_EOL;
foreach ([
    'browse sin lista (page 10000)' => [null, 10000, 0],
    'browse con lista=1 (page 10000)' => [null, 10000, 1],
    'browse sin lista (page 100)' => [null, 100, 0],
] as $label => [$q, $pageSize, $codLista]) {
    $start = microtime(true);
    try {
        $items = $service->buscar($q, $pageSize, $codLista);
        echo "{$label}: rows=".count($items).' time='.elapsed($start).PHP_EOL;
    } catch (Throwable $exception) {
        echo "{$label}: ERROR time=".elapsed($start).' — '.$exception->getMessage().PHP_EOL;
    }
}
echo PHP_EOL;

echo '=== Índices clave ==='.PHP_EOL;
$indexSql = <<<'SQL'
SELECT
    t.name AS table_name,
    i.name AS index_name,
    i.type_desc,
    STRING_AGG(c.name, ', ') WITHIN GROUP (ORDER BY ic.key_ordinal) AS key_columns
FROM sys.tables AS t
INNER JOIN sys.indexes AS i ON t.object_id = i.object_id
INNER JOIN sys.index_columns AS ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns AS c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE t.name IN (
    'pq_pedidosweb_articulos',
    'pq_pedidosweb_stock',
    'pq_pedidosweb_pedidosdetalle',
    'pq_pedidosweb_pedidoscabecera',
    'pq_pedidosweb_listaprecios_articulos'
)
  AND i.type > 0
GROUP BY t.name, i.name, i.type_desc, i.index_id
ORDER BY t.name, i.index_id
SQL;

try {
    foreach (DB::select($indexSql) as $row) {
        echo "{$row->table_name} | {$row->index_name} ({$row->type_desc}) => {$row->key_columns}".PHP_EOL;
    }
} catch (Throwable $exception) {
    echo 'Índices: '.$exception->getMessage().PHP_EOL;
}

echo PHP_EOL.'=== Plan estimado (browse 100 filas, STATISTICS IO/TIME) ==='.PHP_EOL;
try {
    DB::statement('SET STATISTICS IO ON');
    DB::statement('SET STATISTICS TIME ON');
    $service->buscar(null, 100, 0);
    DB::statement('SET STATISTICS IO OFF');
    DB::statement('SET STATISTICS TIME OFF');
} catch (Throwable $exception) {
    echo 'Plan: '.$exception->getMessage().PHP_EOL;
}
