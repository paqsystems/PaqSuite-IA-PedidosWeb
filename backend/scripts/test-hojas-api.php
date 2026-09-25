<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$baseUrl = 'http://127.0.0.1:8000';
$tenant = 'desarrollo';
$filePath = realpath(__DIR__.'/../../PEDIDO_INDIVIDUAL_plantilla.xlsx');
$loginPassword = (string) env('SEED_MVP_PASSWORD', 'ChangeMeInLocalEnv');

foreach (['vendedor.acotado.mvp', 'supervisor.mvp'] as $loginCodigo) {
    echo "\n--- user={$loginCodigo} ---\n";

    $login = json_decode((string) file_get_contents($baseUrl.'/api/v1/auth/login', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nX-Paq-Cliente: {$tenant}\r\n",
            'content' => json_encode(['codigo' => $loginCodigo, 'password' => $loginPassword]),
            'ignore_errors' => true,
        ],
    ])), true);

    $token = $login['resultado']['token'] ?? null;
    echo 'login error='.($login['error'] ?? '?').PHP_EOL;
    if (! $token) {
        continue;
    }

    $meta = file_get_contents(
        $baseUrl.'/api/v1/excel-import/procesos/PEDIDO_INDIVIDUAL',
        false,
        stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer {$token}\r\nX-Paq-Cliente: {$tenant}\r\nAccept: application/json\r\n",
                'ignore_errors' => true,
            ],
        ])
    );
    echo "metadata: {$meta}\n";

    $boundary = '----WebKitFormBoundary'.bin2hex(random_bytes(8));
    $body = "--{$boundary}\r\n"
        ."Content-Disposition: form-data; name=\"archivo\"; filename=\"PEDIDO_INDIVIDUAL_plantilla.xlsx\"\r\n"
        ."Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet\r\n\r\n"
        .file_get_contents($filePath)."\r\n"
        ."--{$boundary}--\r\n";

    $hojas = file_get_contents(
        $baseUrl.'/api/v1/excel-import/procesos/PEDIDO_INDIVIDUAL/archivo/hojas',
        false,
        stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    "Authorization: Bearer {$token}",
                    "X-Paq-Cliente: {$tenant}",
                    "Content-Type: multipart/form-data; boundary={$boundary}",
                    'Content-Length: '.strlen($body),
                ]),
                'content' => $body,
                'ignore_errors' => true,
            ],
        ])
    );
    echo "hojas: {$hojas}\n";
}
