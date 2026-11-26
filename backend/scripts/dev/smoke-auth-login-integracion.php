<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$password = (string) config('paqsuite_seed.mvpPassword');
$response = Http::withHeaders([
    'X-Paq-Cliente' => 'demo',
    'Accept' => 'application/json',
])->post('http://127.0.0.1:8088/api/v1/auth/login', [
    'codigo' => 'integracion.erp.mvp',
    'password' => $password,
]);

echo 'status='.$response->status().PHP_EOL;
echo $response->body().PHP_EOL;