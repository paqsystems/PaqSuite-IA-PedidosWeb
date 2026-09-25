<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\PedidosWeb\CabeceraInicialService;

$user = User::query()->first();
if ($user === null) {
    echo "sin usuario\n";
    exit(1);
}

$service = app(CabeceraInicialService::class);
$codCliente = $argv[1] ?? '10897';

$start = microtime(true);
$result = $service->buildForCliente($codCliente, $user);
echo sprintf(
    "cabecera-inicial cliente=%s lista=%d time=%.2fs\n",
    $codCliente,
    (int) ($result['cabecera']['lista_precios'] ?? 0),
    microtime(true) - $start,
);
