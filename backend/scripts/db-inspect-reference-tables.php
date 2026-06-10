<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['pq_pedidosweb_condventa', 'pq_pedidosweb_listaprecios'] as $table) {
    echo PHP_EOL . '=== ' . $table . ' ===' . PHP_EOL;
    $cols = Illuminate\Support\Facades\DB::select(
        'SELECT c.name, t.name AS type_name, c.max_length, c.is_nullable
         FROM sys.columns c
         JOIN sys.types t ON c.user_type_id = t.user_type_id
         WHERE c.object_id = OBJECT_ID(?)
         ORDER BY c.column_id',
        [$table]
    );
    foreach ($cols as $col) {
        echo $col->name . ' | ' . $col->type_name . ' | nullable=' . ($col->is_nullable ? 'Y' : 'N') . PHP_EOL;
    }
}
