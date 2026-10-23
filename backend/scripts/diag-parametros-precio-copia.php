<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PedidosWeb\PedidosWebParameterService;
use Illuminate\Support\Facades\DB;

$keys = [
    'ArticulosPrecioCero',
    'Articulopreciocero',
    'ArticulosSinPrecio',
    'Articulossinprecio',
    'ActualizarPrecioCopia',
];

foreach ($keys as $key) {
    $row = DB::table('PQ_parametros_gral')
        ->where('Programa', 'PedidosWeb')
        ->where('Clave', $key)
        ->first();

    if ($row === null) {
        echo "{$key}: MISSING\n";
        continue;
    }

    echo "{$key}: Valor_Bool=" . var_export($row->Valor_Bool, true) . "\n";
}

$service = new PedidosWebParameterService();
echo "\nResolved via service:\n";
echo 'getActualizarPrecioCopia=' . ($service->getActualizarPrecioCopia() ? 'true' : 'false') . "\n";
echo 'getArticuloPrecioCero=' . ($service->getArticuloPrecioCero() ? 'true' : 'false') . "\n";
echo 'getArticulosSinPrecio=' . ($service->getArticulosSinPrecio() ? 'true' : 'false') . "\n";
