<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = Illuminate\Support\Facades\DB::select(
    'SELECT c.name, t.name AS type_name, c.max_length
     FROM sys.columns c
     JOIN sys.types t ON c.user_type_id = t.user_type_id
     WHERE c.object_id = OBJECT_ID(?)
     ORDER BY c.column_id',
    ['personal_access_tokens']
);

foreach ($cols as $col) {
    echo $col->name . ' | ' . $col->type_name . PHP_EOL;
}
