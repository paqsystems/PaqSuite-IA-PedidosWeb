<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$sql = file_get_contents(__DIR__.'/../../articulos_stock_aws.sql');
// quitar comentarios de bloque /* */ para ejecución simple
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

$start = microtime(true);
try {
    $rows = DB::select($sql);
    echo 'ok rows='.count($rows).' elapsed_s='.round(microtime(true) - $start, 2).PHP_EOL;
} catch (Throwable $e) {
    echo 'error elapsed_s='.round(microtime(true) - $start, 2).' msg='.$e->getMessage().PHP_EOL;
}
