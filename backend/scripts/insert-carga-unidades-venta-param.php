<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Seed\PqParametrosGralPedidosWebSeeder;

$jsonPath = dirname(__DIR__, 2) . '/docs/backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json';

$inserted = app(PqParametrosGralPedidosWebSeeder::class)->insertMissingFromJsonFile($jsonPath);

echo $inserted > 0
    ? "INSERT OK: {$inserted} parámetro(s) PedidosWeb nuevo(s).\n"
    : "OK: no faltaban parámetros PedidosWeb del seed.\n";
