<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
foreach (['pq_pedidosweb_deuda', 'pq_pedidosweb_clientes'] as $t) {
    echo "=== $t ===\n";
    if (!Schema::hasTable($t)) { echo "NO\n"; continue; }
    foreach (DB::select('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=? ORDER BY ORDINAL_POSITION', [$t]) as $c) {
        echo "  {$c->COLUMN_NAME}\n";
    }
}
