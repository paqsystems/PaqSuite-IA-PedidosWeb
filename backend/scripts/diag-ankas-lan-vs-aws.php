<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

$targets = [
    'lan' => [
        'host' => '192.168.41.2',
        'database' => 'Ankas_del_sur',
        'username' => 'Axoft',
        'password' => 'Axoft',
    ],
    'aws' => [
        'host' => 'database-1.cf2tcvmvcot6.us-east-2.rds.amazonaws.com',
        'database' => 'paqsystems_pedidosweb_ankasdelsur',
        'username' => 'Admin',
        'password' => 'f30CuP217zHau0pKCNpAgEpv3O',
    ],
];

foreach ($targets as $label => $connection) {
    echo '========== '.$label.' ('.$connection['database'].') =========='.PHP_EOL;
    Config::set('database.connections.diag_sqlsrv', array_merge(
        config('database.connections.sqlsrv'),
        [
            'host' => $connection['host'],
            'database' => $connection['database'],
            'username' => $connection['username'],
            'password' => $connection['password'],
        ]
    ));
    DB::purge('diag_sqlsrv');

    try {
        $dbName = DB::connection('diag_sqlsrv')->selectOne('SELECT DB_NAME() AS db_name')->db_name;
        echo 'Connected: '.$dbName.PHP_EOL;

        $motivos = DB::connection('diag_sqlsrv')->table('pq_pedidosweb_motivos_cierre')->count();
        echo 'Motivos cierre: '.$motivos.PHP_EOL;

        $param = DB::connection('diag_sqlsrv')->table('PQ_parametros_gral')
            ->where('Programa', 'PedidosWeb')
            ->where('Clave', 'CodMotivoCierreExitoso')
            ->first();
        echo 'CodMotivoCierreExitoso: '.($param->Valor_Int ?? 'N/A').PHP_EOL;

        $emptyDesc = DB::connection('diag_sqlsrv')->selectOne(
            "SELECT COUNT(*) AS c FROM pq_pedidosweb_articulos WHERE descripcion IS NULL OR LTRIM(RTRIM(CAST(descripcion AS NVARCHAR(255)))) = ''"
        );
        $totalArt = DB::connection('diag_sqlsrv')->selectOne('SELECT COUNT(*) AS c FROM pq_pedidosweb_articulos');
        echo 'Articulos sin descripcion: '.$emptyDesc->c.' / '.$totalArt->c.PHP_EOL;

        $emptyDetalle = DB::connection('diag_sqlsrv')->selectOne(
            "SELECT COUNT(*) AS c FROM pq_pedidosweb_pedidosdetalle WHERE descripcion_articulo IS NULL OR LTRIM(RTRIM(CAST(descripcion_articulo AS NVARCHAR(255)))) = ''"
        );
        $totalDetalle = DB::connection('diag_sqlsrv')->selectOne('SELECT COUNT(*) AS c FROM pq_pedidosweb_pedidosdetalle');
        echo 'Detalle sin descripcion_articulo: '.$emptyDetalle->c.' / '.$totalDetalle->c.PHP_EOL;

        $presupuestos = DB::connection('diag_sqlsrv')->table('pq_pedidosweb_pedidoscabecera')
            ->where('estado', 99)->count();
        echo 'Presupuestos activos: '.$presupuestos.PHP_EOL;
    } catch (Throwable $throwable) {
        echo 'ERROR: '.$throwable->getMessage().PHP_EOL;
    }

    echo PHP_EOL;
}
