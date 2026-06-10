<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['pq_pedidosweb_clientes', 'pq_pedidosweb_vendedores', 'pq_pedidosweb_login'] as $table) {
    echo PHP_EOL . '=== ' . $table . ' ===' . PHP_EOL;
    $cols = Illuminate\Support\Facades\DB::select(
        'SELECT COLUMN_NAME, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_NAME = ?
         ORDER BY ORDINAL_POSITION',
        [$table]
    );
    foreach ($cols as $col) {
        echo $col->COLUMN_NAME . ' | nullable=' . $col->IS_NULLABLE . ' | ' . $col->DATA_TYPE . PHP_EOL;
    }
}
