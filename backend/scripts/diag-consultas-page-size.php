<?php

declare(strict_types=1);

/**
 * Verifica que page_size=1000 trae más de 20 filas en consultas ERP.
 * Uso: php scripts/diag-consultas-page-size.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\PedidosWeb\ChequesConsultaService;
use App\Services\PedidosWeb\DeudaConsultaService;
use App\Services\PedidosWeb\HistorialVentasConsultaService;
use App\Services\PedidosWeb\StockConsultaService;
use App\Support\ConsultaPaginacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo 'DB=' . (DB::selectOne('SELECT DB_NAME() AS db')->db ?? '?') . PHP_EOL;
echo 'MAX_PAGE_SIZE=' . ConsultaPaginacion::MAX_PAGE_SIZE . PHP_EOL;

$tables = [
    'pq_pedidosweb_deuda',
    'pq_pedidosweb_stock',
    'pq_pedidosweb_cheques',
    'pq_pedidosweb_ventadetallada',
];

foreach ($tables as $table) {
    if (! Schema::hasTable($table)) {
        echo "{$table}: MISSING" . PHP_EOL;
        continue;
    }

    $count = (int) DB::table($table)->count();
    echo "{$table}: count={$count}" . PHP_EOL;
}

$user = User::query()->where('codigo', env('TEST_USUARIO', 'PAQ'))->first();

if ($user === null) {
    $user = User::query()->where('activo', 1)->orderBy('id')->get()->first(
        function (User $candidate): bool {
            try {
                app(\App\Services\Visibility\VisibleClientsResolver::class)
                    ->visibleClientsForUser($candidate)
                    ->limit(1)
                    ->exists();

                return true;
            } catch (\Throwable) {
                return false;
            }
        }
    );
}

if ($user === null) {
    echo 'No hay usuario activo con perfil comercial para probar servicios.' . PHP_EOL;
    exit(1);
}

echo 'user=' . $user->codigo . PHP_EOL;

$filters = ['page' => 1, 'page_size' => ConsultaPaginacion::MAX_PAGE_SIZE];

$stock = app(StockConsultaService::class)->listar($filters);
echo 'stock items=' . count($stock['items']) . ' total=' . ($stock['total'] ?? '?') . PHP_EOL;

$deuda = app(DeudaConsultaService::class)->listar($user, $filters);
echo 'deuda items=' . count($deuda['items']) . ' total=' . ($deuda['total'] ?? '?') . PHP_EOL;

$cheques = app(ChequesConsultaService::class)->listar($user, $filters);
echo 'cheques items=' . count($cheques['items']) . ' total=' . ($cheques['total'] ?? '?') . PHP_EOL;

$historial = app(HistorialVentasConsultaService::class)->listar($user, $filters);
echo 'historial items=' . count($historial['items']) . ' total=' . ($historial['total'] ?? '?')
    . ' dias=' . ($historial['metadata']['dias_ventas_detalladas'] ?? '?') . PHP_EOL;
