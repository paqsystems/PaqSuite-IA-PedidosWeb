<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$row = DB::table('PQ_parametros_gral')
    ->where('Programa', 'PedidosWeb')
    ->where('Clave', 'ActualizarPrecioCopia')
    ->first();

echo $row ? json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'NO_ROW';
echo PHP_EOL;

$count = DB::table('PQ_parametros_gral')->where('Programa', 'PedidosWeb')->count();
echo 'Total PedidosWeb params: ' . $count . PHP_EOL;

$sample = DB::table('PQ_parametros_gral')
    ->where('Programa', 'PedidosWeb')
    ->where('Clave', 'ArticulosPrecioCero')
    ->first();
echo 'Sample row ArticulosPrecioCero:' . PHP_EOL;
echo json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
