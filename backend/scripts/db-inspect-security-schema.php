<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$database = (string) config('database.connections.sqlsrv.database');
echo 'DB: ' . $database . PHP_EOL;

$tables = ['users', 'pq_menus', 'Pq_Rol', 'Pq_Permiso', 'PQ_RolAtributo', 'PQ_Empresa'];

foreach ($tables as $table) {
    echo PHP_EOL . '=== ' . $table . ' ===' . PHP_EOL;

    if (! Illuminate\Support\Facades\Schema::hasTable($table)) {
        echo '  (table missing)' . PHP_EOL;
        continue;
    }

    $cols = Illuminate\Support\Facades\DB::select(
        'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_NAME = ?
         ORDER BY ORDINAL_POSITION',
        [$table]
    );

    foreach ($cols as $col) {
        $len = $col->CHARACTER_MAXIMUM_LENGTH ?? '';
        echo '  ' . $col->COLUMN_NAME . ' (' . $col->DATA_TYPE . ($len !== '' ? ':' . $len : '') . ') nullable=' . $col->IS_NULLABLE . PHP_EOL;
    }
}
