<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$path = __DIR__.'/../../PEDIDO_INDIVIDUAL_plantilla.xlsx';
$service = app(App\Services\ExcelImport\ExcelWorkbookService::class);

echo 'file='.realpath($path).PHP_EOL;
echo 'size='.filesize($path).PHP_EOL;

try {
    $hojas = $service->listSheetNames($path);
    echo 'hojas='.json_encode($hojas).PHP_EOL;
} catch (Throwable $e) {
    echo get_class($e).': '.$e->getMessage().PHP_EOL;
    if ($e instanceof App\Exceptions\ExcelImportFlowException) {
        echo 'respuestaKey='.$e->respuestaKey().PHP_EOL;
    }
}
