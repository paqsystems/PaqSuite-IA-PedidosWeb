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
$token = $login['json']['resultado']['token'] ?? null;
$auth = [
    'X-Paq-Cliente: desarrollo',
    'Authorization: Bearer '.$token,
    'Accept: application/json',
];

$resp = req('GET', $base.'/consultas/historial-ventas?page=1&page_size=1000', $auth);
$items = $resp['json']['resultado']['items'] ?? [];
$keys = [];
foreach ($items as $index => $item) {
    $key = ($item['codCliente'] ?? '').'-'.($item['tipo'] ?? '').'-'.($item['numero'] ?? '').'-'
        .($item['codArticulo'] ?? '').'-'.($item['fechaEmision'] ?? $index);
    $keys[$key] = ($keys[$key] ?? 0) + 1;
}
$dupes = array_filter($keys, static fn (int $n): bool => $n > 1);
echo 'items='.count($items).PHP_EOL;
echo 'uniqueKeys='.count($keys).PHP_EOL;
echo 'duplicateKeyGroups='.count($dupes).PHP_EOL;
if ($dupes !== []) {
    arsort($dupes);
    echo 'topDupes='.json_encode(array_slice($dupes, 0, 5, true)).PHP_EOL;
}
