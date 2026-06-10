<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = ['pq_pedidosweb_clientes', 'pq_pedidosweb_vendedores', 'pq_pedidosweb_login'];

foreach ($tables as $table) {
    echo PHP_EOL . '=== ' . $table . ' ===' . PHP_EOL;

    $cols = Illuminate\Support\Facades\DB::select(
        'SELECT c.name AS column_name, t.name AS type_name, c.max_length, c.is_identity, c.is_nullable
         FROM sys.columns c
         JOIN sys.types t ON c.user_type_id = t.user_type_id
         WHERE c.object_id = OBJECT_ID(?)
         ORDER BY c.column_id',
        [$table]
    );

    foreach ($cols as $col) {
        echo $col->column_name
            . ' | ' . $col->type_name
            . ' | identity=' . ($col->is_identity ? 'YES' : 'NO')
            . PHP_EOL;
    }
}

$pks = Illuminate\Support\Facades\DB::select(
    "SELECT tc.TABLE_NAME, kcu.COLUMN_NAME
     FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
     JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
       ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
     WHERE tc.CONSTRAINT_TYPE = 'PRIMARY KEY'
       AND tc.TABLE_NAME IN ('pq_pedidosweb_clientes','pq_pedidosweb_vendedores','pq_pedidosweb_login')
     ORDER BY tc.TABLE_NAME"
);

echo PHP_EOL . '=== PRIMARY KEYS ===' . PHP_EOL;
foreach ($pks as $pk) {
    echo $pk->TABLE_NAME . ' -> ' . $pk->COLUMN_NAME . PHP_EOL;
}
