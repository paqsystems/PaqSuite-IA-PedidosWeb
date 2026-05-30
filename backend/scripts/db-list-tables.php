<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = Illuminate\Support\Facades\DB::select(
    "SELECT TABLE_SCHEMA, TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME"
);

echo 'Total tables: ' . count($rows) . PHP_EOL;

foreach ($rows as $row) {
    echo $row->TABLE_SCHEMA . '.' . $row->TABLE_NAME . PHP_EOL;
}
