<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$targets = ['pq_pedidosweb_login', 'pq_pedidosweb_perfil', 'pq_pedidosweb_clientes', 'pq_pedidosweb_vendedores'];

foreach ($targets as $table) {
    echo PHP_EOL . '=== ' . $table . ' ===' . PHP_EOL;
    $cols = Illuminate\Support\Facades\DB::select(
        'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
        [$table]
    );
    foreach ($cols as $col) {
        echo '  ' . $col->COLUMN_NAME . ' (' . $col->DATA_TYPE . ':' . ($col->CHARACTER_MAXIMUM_LENGTH ?? '') . ')' . PHP_EOL;
    }
    $count = Illuminate\Support\Facades\DB::table($table)->count();
    echo '  rows: ' . $count . PHP_EOL;
}

if (Illuminate\Support\Facades\Schema::hasTable('pq_pedidosweb_login')) {
    $sample = Illuminate\Support\Facades\DB::table('pq_pedidosweb_login')->limit(3)->get();
    echo PHP_EOL . 'Sample login rows:' . PHP_EOL;
    foreach ($sample as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}
