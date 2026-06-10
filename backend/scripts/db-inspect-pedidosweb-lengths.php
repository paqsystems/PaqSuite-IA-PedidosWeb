<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = ['pq_pedidosweb_login', 'pq_pedidosweb_clientes', 'pq_pedidosweb_vendedores'];

foreach ($tables as $table) {
    echo PHP_EOL . '=== ' . $table . ' ===' . PHP_EOL;
    $cols = Illuminate\Support\Facades\DB::select(
        'SELECT c.name, t.name AS type_name, c.max_length
         FROM sys.columns c
         JOIN sys.types t ON c.user_type_id = t.user_type_id
         WHERE c.object_id = OBJECT_ID(?)
         ORDER BY c.column_id',
        [$table]
    );

    foreach ($cols as $col) {
        $charLen = $col->max_length > 0 && $col->type_name === 'nvarchar' ? (int) ($col->max_length / 2) : $col->max_length;
        echo $col->name . ' | ' . $col->type_name . ' | len=' . $charLen . PHP_EOL;
    }
}
