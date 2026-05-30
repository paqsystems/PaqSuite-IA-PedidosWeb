<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['pq_pedidosweb_condventa', 'pq_pedidosweb_listaprecios'] as $table) {
    $count = Illuminate\Support\Facades\Schema::hasTable($table)
        ? Illuminate\Support\Facades\DB::table($table)->count()
        : -1;
    echo $table . ': ' . $count . PHP_EOL;
    if ($count > 0) {
        $row = Illuminate\Support\Facades\DB::table($table)->first();
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}

$fks = Illuminate\Support\Facades\DB::select(
    "SELECT fk.name, tp.name AS parent_table, cp.name AS parent_column, tr.name AS ref_table
     FROM sys.foreign_keys fk
     JOIN sys.foreign_key_columns fkc ON fk.object_id = fkc.constraint_object_id
     JOIN sys.tables tp ON fkc.parent_object_id = tp.object_id
     JOIN sys.columns cp ON fkc.parent_object_id = cp.object_id AND fkc.parent_column_id = cp.column_id
     JOIN sys.tables tr ON fkc.referenced_object_id = tr.object_id
     WHERE tp.name IN ('pq_pedidosweb_clientes','pq_pedidosweb_vendedores','pq_pedidosweb_login')
     ORDER BY tp.name"
);

echo PHP_EOL . 'FKs:' . PHP_EOL;
foreach ($fks as $fk) {
    echo $fk->parent_table . '.' . $fk->parent_column . ' -> ' . $fk->ref_table . PHP_EOL;
}
