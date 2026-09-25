<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

Config::set('database.connections.diag_sqlsrv', array_merge(
    config('database.connections.sqlsrv'),
    [
        'host' => 'database-1.cf2tcvmvcot6.us-east-2.rds.amazonaws.com',
        'database' => 'paqsystems_pedidosweb_ankasdelsur',
        'username' => 'Admin',
        'password' => 'f30CuP217zHau0pKCNpAgEpv3O',
    ]
));
DB::purge('diag_sqlsrv');
$conn = DB::connection('diag_sqlsrv');

echo 'DB: '.$conn->selectOne('SELECT DB_NAME() AS n')->n.PHP_EOL;

$tables = $conn->select("SELECT name FROM sys.tables WHERE name LIKE 'pq_pedidosweb%' ORDER BY name");
echo 'Tablas pq_pedidosweb: '.count($tables).PHP_EOL;
foreach ($tables as $table) {
    echo '  '.$table->name.PHP_EOL;
}

$hasArticulos = collect($tables)->contains(fn ($t) => $t->name === 'pq_pedidosweb_articulos');
if ($hasArticulos) {
    $total = $conn->selectOne('SELECT COUNT(*) AS c FROM pq_pedidosweb_articulos')->c;
    $empty = $conn->selectOne(
        "SELECT COUNT(*) AS c FROM pq_pedidosweb_articulos WHERE descripcion IS NULL OR LTRIM(RTRIM(CAST(descripcion AS NVARCHAR(255)))) = ''"
    )->c;
    echo "Articulos: total={$total} sin_descripcion={$empty}".PHP_EOL;
    $sample = $conn->table('pq_pedidosweb_articulos')->limit(3)->get(['codigo', 'descripcion']);
    foreach ($sample as $row) {
        echo '  '.json_encode($row, JSON_UNESCAPED_UNICODE).PHP_EOL;
    }
}

$hasParam = collect($tables)->contains(fn ($t) => $t->name === 'PQ_parametros_gral');
if ($hasParam) {
    $param = $conn->table('PQ_parametros_gral')
        ->where('Programa', 'PedidosWeb')
        ->where('Clave', 'CodMotivoCierreExitoso')
        ->first();
    echo 'CodMotivoCierreExitoso: '.json_encode($param, JSON_UNESCAPED_UNICODE).PHP_EOL;
}

$hasCabecera = collect($tables)->contains(fn ($t) => $t->name === 'pq_pedidosweb_pedidoscabecera');
if ($hasCabecera) {
    $pres = $conn->table('pq_pedidosweb_pedidoscabecera')->where('estado', 99)->count();
    echo 'Presupuestos activos: '.$pres.PHP_EOL;

    $samplePres = $conn->table('pq_pedidosweb_pedidoscabecera')
        ->where('estado', 99)
        ->orderByDesc('fecha')
        ->limit(1)
        ->value('cod_pedido');
    if ($samplePres) {
        $detalle = $conn->table('pq_pedidosweb_pedidosdetalle')
            ->where('cod_pedido', $samplePres)
            ->limit(5)
            ->get(['renglon', 'cod_articulo', 'descripcion_articulo']);
        echo 'Detalle sample presupuesto '.$samplePres.':'.PHP_EOL;
        foreach ($detalle as $row) {
            echo '  '.json_encode($row, JSON_UNESCAPED_UNICODE).PHP_EOL;
        }
    }
}

$missing = ['pq_pedidosweb_motivos_cierre', 'PQ_parametros_gral'];
foreach ($missing as $tableName) {
    $exists = collect($tables)->contains(fn ($t) => strcasecmp($t->name, $tableName) === 0);
    echo ($exists ? 'OK' : 'FALTA').' tabla '.$tableName.PHP_EOL;
}
