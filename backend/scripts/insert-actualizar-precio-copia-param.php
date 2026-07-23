<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$programa = 'PedidosWeb';
$clave = 'ActualizarPrecioCopia';

$exists = DB::table('PQ_parametros_gral')
    ->where('Programa', $programa)
    ->where('Clave', $clave)
    ->exists();

if ($exists) {
    echo "OK: la fila {$clave} ya existe.\n";
    exit(0);
}

DB::table('PQ_parametros_gral')->insert([
    'Programa' => $programa,
    'Clave' => $clave,
    'tipo_valor' => 'B',
    'Valor_String' => null,
    'Valor_Text' => null,
    'Valor_Int' => null,
    'Valor_DateTime' => null,
    'Valor_Bool' => 0,
    'Valor_Decimal' => null,
    'CAPTION' => 'Actualizar precios al copiar comprobante',
    'TOOLTIP' => 'Si está activo, al copiar un pedido o presupuesto los precios de los renglones se resuelven desde la lista de precios vigente del comprobante origen. Si está inactivo, se conservan los precios del detalle origen, validando según los parámetros de artículos con precio cero o sin precio.',
]);

echo "INSERT OK: {$clave}\n";

$count = DB::table('PQ_parametros_gral')->where('Programa', $programa)->count();
echo "Total PedidosWeb params: {$count}\n";
