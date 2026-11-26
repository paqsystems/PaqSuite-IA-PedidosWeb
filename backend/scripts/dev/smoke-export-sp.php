<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$reader = $app->make(App\Support\PedidosWeb\SqlServerStoredProcedureMultiResultReader::class);

try {
    $result = $reader->execute(
        'pq_sp_integracion_export_comprobantes_list',
        ['[{"desde":0,"hasta":0}]', 1, 10, null, null, null, null, 0],
        [8],
    );
    echo 'total='.($result['outputs'][8] ?? '?').PHP_EOL;
    echo 'resultSets='.count($result['resultSets']).PHP_EOL;
    echo 'cabeceras='.count($result['resultSets'][0] ?? []).PHP_EOL;
    echo 'renglones='.count($result['resultSets'][1] ?? []).PHP_EOL;
} catch (Throwable $throwable) {
    fwrite(STDERR, 'ERR: '.$throwable->getMessage().PHP_EOL);
    exit(1);
}