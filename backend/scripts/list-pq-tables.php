<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo 'DB='.config('database.connections.sqlsrv.database').PHP_EOL;

$tables = DB::select(
    "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME LIKE 'pq_pedidosweb%' ORDER BY TABLE_NAME"
);

echo 'Tablas pq_pedidosweb: '.count($tables).PHP_EOL;

foreach ($tables as $row) {
    echo '  - '.$row->TABLE_NAME.PHP_EOL;
}

echo PHP_EOL.'Tablas MONO:'.PHP_EOL;

foreach (['users', 'pq_menus', 'Pq_Permiso', 'PQ_Empresa', 'Pq_Rol'] as $table) {
    echo '  '.$table.': '.(Schema::hasTable($table) ? 'si' : 'no').PHP_EOL;
}
