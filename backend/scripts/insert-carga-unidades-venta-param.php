<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$programa = 'PedidosWeb';
$clave = 'CargaUnidadesVenta';

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
    'CAPTION' => 'Carga de pedidos por unidades de venta',
    'TOOLTIP' => 'Si está activo, la cantidad ingresada en renglón, Excel o asistente se interpreta como unidades de venta (cantidad_venta). Si está inactivo, se interpreta como unidades de stock/precio (cantidad). Los importes se calculan siempre sobre cantidad.',
]);

echo "INSERT OK: {$clave}\n";

$count = DB::table('PQ_parametros_gral')->where('Programa', $programa)->count();
echo "Total PedidosWeb params: {$count}\n";
