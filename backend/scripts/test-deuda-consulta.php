<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\PedidosWeb\DeudaConsultaService;
use App\Services\PedidosWeb\PedidosWebSchemaBootstrap;

app(PedidosWebSchemaBootstrap::class)->ensureMvpSchema();

$user = User::query()->where('codigo', 'supervisor.mvp')->first();
if ($user === null) {
    echo "Usuario supervisor.mvp no encontrado\n";
    exit(1);
}

$result = app(DeudaConsultaService::class)->listar($user, ['page' => 1, 'page_size' => 10]);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
