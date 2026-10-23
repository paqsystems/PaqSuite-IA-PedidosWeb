<?php

require __DIR__.'/../vendor/autoload.php';

function req(string $method, string $url, array $headers = [], ?array $json = null): array
{
    $ch = curl_init($url);
    $httpHeaders = $headers;
    if ($json !== null) {
        $httpHeaders[] = 'Content-Type: application/json';
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $httpHeaders,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_POSTFIELDS => $json !== null ? json_encode($json) : null,
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['status' => $status, 'json' => json_decode((string) $body, true)];
}

$base = 'http://127.0.0.1:8088/api/v1';
$login = req('POST', $base.'/auth/login', ['X-Paq-Cliente: desarrollo'], [
    'codigo' => getenv('SMOKE_LOGIN_CODIGO') ?: 'supervisor.mvp',
    'password' => getenv('SMOKE_LOGIN_PASSWORD') ?: 'ChangeMeInLocalEnv',
]);

echo 'login='.$login['status'].PHP_EOL;
$token = $login['json']['resultado']['token'] ?? null;
if (! is_string($token) || $token === '') {
    echo 'no token'.PHP_EOL;
    exit(1);
}

$auth = [
    'X-Paq-Cliente: desarrollo',
    'Authorization: Bearer '.$token,
    'Accept: application/json',
];

foreach ([
    'default' => '/consultas/historial-ventas',
    'page1000' => '/consultas/historial-ventas?page=1&page_size=1000',
    'deuda1000' => '/consultas/deuda?page=1&page_size=1000',
] as $label => $path) {
    $resp = req('GET', $base.$path, $auth);
    $r = $resp['json']['resultado'] ?? [];
    echo $label
        .' status='.$resp['status']
        .' items='.count($r['items'] ?? [])
        .' page_size='.($r['page_size'] ?? '?')
        .' total='.($r['total'] ?? '?')
        .' total_pages='.($r['total_pages'] ?? '?')
        .PHP_EOL;
}
