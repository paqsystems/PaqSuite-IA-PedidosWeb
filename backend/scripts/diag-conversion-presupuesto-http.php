<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$baseUrl = $argv[1] ?? 'http://127.0.0.1:8088/api/v1';
$tenant = $argv[2] ?? 'desarrollo';
$usuario = $argv[3] ?? getenv('TEST_USUARIO') ?: 'PAQ';
$password = $argv[4] ?? getenv('TEST_PASSWORD') ?: 'PaqSystems*';

function httpJson(string $method, string $url, ?array $body = null, array $headers = []): array
{
    $ch = curl_init($url);
    $httpHeaders = array_merge(['Content-Type: application/json'], $headers);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $httpHeaders,
        CURLOPT_TIMEOUT => 120,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'status' => $status,
        'error' => $error,
        'raw' => $raw,
        'json' => json_decode((string) $raw, true),
    ];
}

echo "=== Login {$usuario} @ {$tenant} ===".PHP_EOL;
$login = httpJson('POST', "{$baseUrl}/auth/login", [
    'codigo' => $usuario,
    'password' => $password,
], ["X-Paq-Cliente: {$tenant}"]);

echo 'HTTP '.$login['status'].PHP_EOL;
if ($login['status'] !== 200) {
    echo $login['raw'].PHP_EOL;
    exit(1);
}

$token = $login['json']['resultado']['token'] ?? null;
if (! is_string($token) || $token === '') {
    echo 'Sin token'.PHP_EOL;
    exit(1);
}

$authHeaders = [
    "X-Paq-Cliente: {$tenant}",
    "Authorization: Bearer {$token}",
];

echo PHP_EOL.'=== Presupuesto activo ==='.PHP_EOL;
$presupuestoId = $argv[5] ?? '1fb09641-eb50-441a-9311-6f0cf7618ac3';
$show = httpJson('GET', "{$baseUrl}/presupuestos/{$presupuestoId}", null, $authHeaders);
echo 'HTTP '.$show['status'].PHP_EOL;
echo json_encode($show['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;

if ($show['status'] !== 200) {
    exit(1);
}

$resultado = $show['json']['resultado'];
$cabecera = $resultado['cabecera'];
$detalle = $resultado['detalle'];

echo PHP_EOL.'=== Articulos lookup primer renglon ==='.PHP_EOL;
$codArticulo = $detalle[0]['cod_articulo'] ?? '';
$listaPrecios = (int) ($cabecera['lista_precios'] ?? 0);
$articulos = httpJson(
    'GET',
    "{$baseUrl}/articulos?codigos=".rawurlencode($codArticulo)."&lista_precios={$listaPrecios}",
    null,
    $authHeaders
);
echo 'HTTP '.$articulos['status'].PHP_EOL;
echo json_encode($articulos['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;

echo PHP_EOL.'=== Intento conversion presupuesto -> pedido ==='.PHP_EOL;
$grabarBody = [
    'accionGrabacion' => 'pedido',
    'cod_presupuesto_origen' => $presupuestoId,
    'cabecera' => $cabecera,
    'renglones' => $detalle,
];
$grabar = httpJson('POST', "{$baseUrl}/pedidos", $grabarBody, $authHeaders);
echo 'HTTP '.$grabar['status'].PHP_EOL;
echo json_encode($grabar['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
