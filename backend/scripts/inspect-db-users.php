<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo 'connection='.config('database.default').PHP_EOL;
echo 'database='.config('database.connections.sqlsrv.database').PHP_EOL;

$tables = DB::select("SELECT name FROM sys.tables WHERE name LIKE '%user%' OR name LIKE '%User%' ORDER BY name");
echo 'tables: '.implode(', ', array_map(static fn ($t) => $t->name, $tables)).PHP_EOL;

foreach (['users', 'Users', 'USERS'] as $tableName) {
    try {
        $count = DB::table($tableName)->count();
        echo "{$tableName}.count={$count}".PHP_EOL;
    } catch (Throwable $e) {
        echo "{$tableName}: error".PHP_EOL;
    }
}
