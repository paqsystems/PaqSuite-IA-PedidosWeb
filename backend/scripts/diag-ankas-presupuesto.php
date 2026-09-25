<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo 'DB: '.config('database.connections.sqlsrv.database').PHP_EOL;

$param = DB::table('PQ_parametros_gral')
    ->where('Programa', 'PedidosWeb')
    ->where('Clave', 'CodMotivoCierreExitoso')
    ->first();
echo 'CodMotivoCierreExitoso param: '.json_encode($param, JSON_UNESCAPED_UNICODE).PHP_EOL;

$motivos = DB::table('pq_pedidosweb_motivos_cierre')->get();
echo 'Motivos cierre ('.count($motivos).'):'.PHP_EOL;
foreach ($motivos as $motivo) {
    echo '  id='.$motivo->id_motivo
        .' tipo='.$motivo->tipo_cierre
        .' activo='.$motivo->activo
        .' desc='.($motivo->descripcion ?? '')
        .PHP_EOL;
}

$presupuestos = DB::table('pq_pedidosweb_pedidoscabecera')
    ->where('estado', 99)
    ->orderByDesc('fecha')
    ->limit(3)
    ->get(['cod_pedido', 'nro_visible', 'cod_cliente', 'estado']);

echo 'Presupuestos activos sample:'.PHP_EOL;
foreach ($presupuestos as $presupuesto) {
    echo '  '.json_encode($presupuesto, JSON_UNESCAPED_UNICODE).PHP_EOL;
}

if ($presupuestos->isNotEmpty()) {
    $codPedido = $presupuestos[0]->cod_pedido;
    $detalle = DB::table('pq_pedidosweb_pedidosdetalle')
        ->where('cod_pedido', $codPedido)
        ->limit(5)
        ->get(['renglon', 'cod_articulo', 'descripcion_articulo']);

    echo 'Detalle presupuesto '.$codPedido.':'.PHP_EOL;
    foreach ($detalle as $renglon) {
        echo '  '.json_encode($renglon, JSON_UNESCAPED_UNICODE).PHP_EOL;

        $articulo = DB::table('pq_pedidosweb_articulos')
            ->where('codigo', $renglon->cod_articulo)
            ->first(['codigo', 'descripcion']);
        echo '    articulo catalogo: '.json_encode($articulo, JSON_UNESCAPED_UNICODE).PHP_EOL;
    }
}

$emptyDesc = DB::selectOne(
    "SELECT COUNT(*) AS c FROM pq_pedidosweb_articulos WHERE descripcion IS NULL OR LTRIM(RTRIM(CAST(descripcion AS NVARCHAR(255)))) = ''"
);
$totalArt = DB::selectOne('SELECT COUNT(*) AS c FROM pq_pedidosweb_articulos');
echo 'Articulos sin descripcion: '.$emptyDesc->c.' / '.$totalArt->c.PHP_EOL;

$sampleArticulos = DB::table('pq_pedidosweb_articulos')->limit(3)->get(['codigo', 'descripcion']);
echo 'Articulos sample:'.PHP_EOL;
foreach ($sampleArticulos as $articulo) {
    echo '  '.json_encode($articulo, JSON_UNESCAPED_UNICODE).PHP_EOL;
}

$users = DB::table('users')->limit(10)->get(['codigo', 'nombre']);
echo 'Users sample:'.PHP_EOL;
foreach ($users as $user) {
    echo '  '.json_encode($user, JSON_UNESCAPED_UNICODE).PHP_EOL;
}

$logins = DB::table('pq_pedidosweb_login')->limit(5)->get();
echo 'Login comercial sample:'.PHP_EOL;
foreach ($logins as $login) {
    echo '  '.json_encode($login, JSON_UNESCAPED_UNICODE).PHP_EOL;
}
