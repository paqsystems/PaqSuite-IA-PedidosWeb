<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo 'DB='.DB::connection()->getDatabaseName().PHP_EOL;
foreach (['pq_excel_procesos', 'pq_excel_procesos_campos', 'pq_excel_importaciones'] as $table) {
    echo $table.': '.(Schema::hasTable($table) ? 'SI' : 'NO').PHP_EOL;
}

if (Schema::hasTable('pq_excel_procesos')) {
    $proceso = DB::table('pq_excel_procesos')->where('codigo_proceso', 'PEDIDO_INDIVIDUAL')->first();
    if ($proceso) {
        echo 'PEDIDO_INDIVIDUAL genera_plantilla='.$proceso->genera_plantilla.' activo='.$proceso->activo.PHP_EOL;
        echo 'campos='.DB::table('pq_excel_procesos_campos')->where('id_proceso', $proceso->id)->count().PHP_EOL;
    } else {
        echo 'PEDIDO_INDIVIDUAL: NO EXISTE'.PHP_EOL;
    }
}

echo 'EXCEL_IMPORT_ENABLED='.config('excel_import.enabled', env('EXCEL_IMPORT_ENABLED', false) ? 'true' : 'false').PHP_EOL;
