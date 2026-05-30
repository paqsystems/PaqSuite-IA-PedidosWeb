<?php

putenv('APP_ENV=testing');
putenv('SEED_MVP_PASSWORD=TestSeedPassword123');
putenv('SEED_MVP_SYNC_COMMERCIAL=true');

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'syncCommercial=' . (config('paqsuite_seed.syncCommercial') ? 'true' : 'false') . PHP_EOL;

$menuExit = $kernel->call('paqsuite:seed-menus-mvp');
echo 'menu exit: ' . $menuExit . PHP_EOL;
echo Illuminate\Support\Facades\Artisan::output();

$secExit = $kernel->call('paqsuite:seed-seguridad-mvp');
echo 'security exit: ' . $secExit . PHP_EOL;
echo Illuminate\Support\Facades\Artisan::output();
