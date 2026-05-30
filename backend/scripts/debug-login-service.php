<?php

putenv('SEED_MVP_PASSWORD=TestSeedPassword123');
putenv('SEED_MVP_SYNC_COMMERCIAL=true');

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kernel->call('paqsuite:seed-menus-mvp');
$kernel->call('paqsuite:seed-seguridad-mvp');

try {
    $loginService = $app->make(App\Services\Auth\LoginService::class);
    $result = $loginService->login('cliente.mvp', 'TestSeedPassword123');
    echo 'OK: ' . json_encode(array_keys($result), JSON_THROW_ON_ERROR) . PHP_EOL;
} catch (Throwable $exception) {
    echo 'ERR: ' . $exception->getMessage() . PHP_EOL;
    echo $exception->getTraceAsString() . PHP_EOL;
}
