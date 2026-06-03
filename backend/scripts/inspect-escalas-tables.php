<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

foreach (['pq_pedidosweb_escalas_cabecera', 'pq_pedidosweb_escalas_detalle'] as $table) {
    echo PHP_EOL."=== {$table} ===".PHP_EOL;

    if (! Schema::hasTable($table)) {
        echo "NO EXISTE\n";
        continue;
    }

    $cols = DB::select(
        'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, IS_NULLABLE
         FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
        [$table]
    );

    foreach ($cols as $col) {
        $type = $col->DATA_TYPE;
        if ($col->CHARACTER_MAXIMUM_LENGTH) {
            $type .= "({$col->CHARACTER_MAXIMUM_LENGTH})";
        } elseif ($col->NUMERIC_PRECISION) {
            $type .= "({$col->NUMERIC_PRECISION},{$col->NUMERIC_SCALE})";
        }
        echo "  {$col->COLUMN_NAME} {$type} ".($col->IS_NULLABLE === 'YES' ? 'NULL' : 'NOT NULL').PHP_EOL;
    }

    $pks = DB::select(
        "SELECT kcu.COLUMN_NAME
         FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
         JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
           ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME AND tc.TABLE_NAME = kcu.TABLE_NAME
         WHERE tc.TABLE_NAME = ? AND tc.CONSTRAINT_TYPE = 'PRIMARY KEY'
         ORDER BY kcu.ORDINAL_POSITION",
        [$table]
    );

    if ($pks !== []) {
        echo '  PK: '.implode(', ', array_map(fn ($r) => $r->COLUMN_NAME, $pks)).PHP_EOL;
    }
}
