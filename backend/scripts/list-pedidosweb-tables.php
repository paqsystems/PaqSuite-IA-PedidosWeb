<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select("SELECT name FROM sys.tables WHERE name LIKE '%pedidos%' OR name LIKE '%Pedidos%' OR name LIKE '%login%' OR name LIKE '%Login%' OR name LIKE '%vended%' OR name LIKE '%client%' ORDER BY name");

foreach ($tables as $table) {
    echo $table->name.PHP_EOL;
}
