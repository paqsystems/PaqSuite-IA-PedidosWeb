<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PedidosWeb\PedidosWebSchemaBootstrap;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
Config::set('database.default', 'diag_sqlsrv');

$bootstrap = app(PedidosWebSchemaBootstrap::class);
$bootstrap->ensureMvpSchema();

echo 'motivos='.DB::table('pq_pedidosweb_motivos_cierre')->count().PHP_EOL;
echo 'has_descripcion_col='.(Schema::hasColumn('pq_pedidosweb_pedidosdetalle', 'descripcion_articulo') ? 'yes' : 'no').PHP_EOL;

$presupuestoId = DB::table('pq_pedidosweb_pedidoscabecera')->where('estado', 99)->orderByDesc('fecha')->value('cod_pedido');
echo 'sample_presupuesto='.$presupuestoId.PHP_EOL;

if ($presupuestoId) {
    $detalle = DB::table('pq_pedidosweb_pedidosdetalle')
        ->where('cod_pedido', $presupuestoId)
        ->limit(2)
        ->get(['renglon', 'cod_articulo', 'descripcion_articulo']);
    foreach ($detalle as $row) {
        echo '  detalle '.json_encode($row, JSON_UNESCAPED_UNICODE).PHP_EOL;
    }
}
