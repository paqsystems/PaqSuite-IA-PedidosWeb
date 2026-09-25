<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$queries = [
    'version' => "SELECT @@VERSION AS v",
    'edition' => "SELECT SERVERPROPERTY('Edition') AS edition, SERVERPROPERTY('ProductVersion') AS version",
    'rowcounts' => "
        SELECT 'articulos' t, COUNT(*) c FROM pq_pedidosweb_articulos
        UNION ALL SELECT 'stock', COUNT(*) FROM pq_pedidosweb_stock
        UNION ALL SELECT 'listaprecios_art', COUNT(*) FROM pq_pedidosweb_listaprecios_articulos
        UNION ALL SELECT 'pedidosdetalle', COUNT(*) FROM pq_pedidosweb_pedidosdetalle
    ",
    'indexes_articulos' => "
        SELECT i.name idx, i.type_desc, c.name col
        FROM sys.indexes i
        JOIN sys.index_columns ic ON ic.object_id=i.object_id AND ic.index_id=i.index_id
        JOIN sys.columns c ON c.object_id=ic.object_id AND c.column_id=ic.column_id
        WHERE i.object_id = OBJECT_ID('pq_pedidosweb_articulos')
        ORDER BY i.name, ic.key_ordinal
    ",
    'indexes_stock' => "
        SELECT i.name idx, i.type_desc, c.name col
        FROM sys.indexes i
        JOIN sys.index_columns ic ON ic.object_id=i.object_id AND ic.index_id=i.index_id
        JOIN sys.columns c ON c.object_id=ic.object_id AND c.column_id=ic.column_id
        WHERE i.object_id = OBJECT_ID('pq_pedidosweb_stock')
        ORDER BY i.name, ic.key_ordinal
    ",
    'indexes_lista' => "
        SELECT i.name idx, i.type_desc, c.name col
        FROM sys.indexes i
        JOIN sys.index_columns ic ON ic.object_id=i.object_id AND ic.index_id=i.index_id
        JOIN sys.columns c ON c.object_id=ic.object_id AND c.column_id=ic.column_id
        WHERE i.object_id = OBJECT_ID('pq_pedidosweb_listaprecios_articulos')
        ORDER BY i.name, ic.key_ordinal
    ",
    'stats_age' => "
        SELECT TOP 10
            OBJECT_NAME(s.object_id) AS table_name,
            s.name AS stat_name,
            STATS_DATE(s.object_id, s.stats_id) AS last_updated
        FROM sys.stats s
        WHERE OBJECT_NAME(s.object_id) LIKE 'pq_pedidosweb_%'
        ORDER BY last_updated ASC
    ",
];

foreach ($queries as $label => $sql) {
    echo "\n=== {$label} ===\n";
    $start = microtime(true);
    try {
        $rows = DB::select($sql);
        echo 'elapsed_s='.round(microtime(true)-$start, 2)."\n";
        echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
    } catch (Throwable $e) {
        echo 'ERROR: '.$e->getMessage()."\n";
    }
}
