<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$sqlPath = __DIR__.'/sql/integracion-transacciones/00-deploy-all.sql';
if (! is_file($sqlPath)) {
    fwrite(STDERR, "No se encontró: {$sqlPath}\n");
    exit(1);
}

$sql = (string) file_get_contents($sqlPath);
$batches = preg_split('/^\s*GO\s*$/mi', $sql) ?: [];
$batchCount = 0;

foreach ($batches as $batch) {
    $batch = trim($batch);
    if ($batch === '') {
        continue;
    }
    DB::unprepared($batch);
    $batchCount++;
}

echo "applied {$batchCount} integracion-transacciones SQL batches on ".config('database.connections.'.config('database.default').'.database')."\n";
