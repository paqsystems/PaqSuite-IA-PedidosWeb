<?php

/**
 * Smoke HTTP Parte F — CC PQ #12 (28/08/2026).
 * Uso: php scripts/smoke-cc-pq-12-f.php
 */

require __DIR__.'/../vendor/autoload.php';

$baseUrl = 'http://127.0.0.1:8088/api/v1';
$tenant = 'desarrollo';
$login = [
    'codigo' => getenv('SMOKE_LOGIN_CODIGO') ?: 'supervisor.mvp',
    'password' => getenv('SMOKE_LOGIN_PASSWORD') ?: 'ChangeMeInLocalEnv',
];

function request(string $method, string $url, array $headers = [], ?array $json = null): array
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
        CURLOPT_TIMEOUT => 90,
        CURLOPT_POSTFIELDS => $json !== null ? json_encode($json) : null,
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'status' => $status,
        'body' => is_string($body) ? $body : '',
        'error' => $error,
        'json' => json_decode(is_string($body) ? $body : '', true),
    ];
}

function assertOk(bool $condition, string $label): void
{
    echo ($condition ? '[OK] ' : '[FAIL] ').$label.PHP_EOL;
    if (! $condition) {
        throw new RuntimeException($label);
    }
}

echo "=== Smoke CC #12 — Parte F ===\n";

$health = request('GET', $baseUrl.'/health', ['X-Paq-Cliente: '.$tenant]);
assertOk($health['status'] === 200, 'GET /health 200');

$loginResp = request('POST', $baseUrl.'/auth/login', ['X-Paq-Cliente: '.$tenant], $login);
assertOk($loginResp['status'] === 200, 'POST /auth/login 200');
$token = $loginResp['json']['resultado']['token'] ?? null;
assertOk(is_string($token) && $token !== '', 'Token Sanctum obtenido');

$authHeaders = [
    'X-Paq-Cliente: '.$tenant,
    'Authorization: Bearer '.$token,
    'Accept: application/json',
];

// Ítem 1 — deuda por cliente (base panel carga)
$clientes = request('GET', $baseUrl.'/clientes?page_size=5', $authHeaders);
assertOk($clientes['status'] === 200, 'GET /clientes 200');
$codCliente = $clientes['json']['resultado']['items'][0]['codCliente'] ?? null;
assertOk(is_string($codCliente) && $codCliente !== '', 'Cliente visible para smoke');

$deuda = request('GET', $baseUrl.'/consultas/deuda?cod_cliente='.urlencode($codCliente).'&page_size=50', $authHeaders);
assertOk($deuda['status'] === 200, 'GET /consultas/deuda?cod_cliente 200');
assertOk(isset($deuda['json']['resultado']['items']), 'Deuda items en envelope');

// Ítem 6 — historial fechas + OpenAPI params
$historialSinFechas = request('GET', $baseUrl.'/consultas/historial-ventas?page_size=5', $authHeaders);
assertOk($historialSinFechas['status'] === 200, 'GET historial-ventas sin fechas 200');

$historialRango = request(
    'GET',
    $baseUrl.'/consultas/historial-ventas?fecha_desde=2025-01-01&fecha_hasta=2025-12-31&page_size=5',
    $authHeaders
);
assertOk($historialRango['status'] === 200, 'GET historial-ventas con rango fechas 200');

$historialDesde = request(
    'GET',
    $baseUrl.'/consultas/historial-ventas?fecha_desde=2024-06-01&page_size=5',
    $authHeaders
);
assertOk($historialDesde['status'] === 200, 'GET historial-ventas solo fecha_desde 200');

$historialHasta = request(
    'GET',
    $baseUrl.'/consultas/historial-ventas?fecha_hasta=2024-12-31&page_size=5',
    $authHeaders
);
assertOk($historialHasta['status'] === 200, 'GET historial-ventas solo fecha_hasta 200');

// Ítem 3 — stock + articulos stockeable
$stock = request('GET', $baseUrl.'/consultas/stock?page_size=100', $authHeaders);
assertOk($stock['status'] === 200, 'GET /consultas/stock 200');

$articulos = request('GET', $baseUrl.'/articulos?page_size=20', $authHeaders);
assertOk($articulos['status'] === 200, 'GET /articulos 200');
$itemsArticulos = $articulos['json']['resultado']['items'] ?? [];
$tieneCampoStockeable = is_array($itemsArticulos)
    && ($itemsArticulos === [] || array_key_exists('stockeable', $itemsArticulos[0]));
assertOk($tieneCampoStockeable, 'API articulos expone stockeable');

// Ítem 3 — parámetro informativo
$params = request('GET', $baseUrl.'/config/parametros?programa=PedidosWeb', $authHeaders);
assertOk($params['status'] === 200, 'GET /config/parametros PedidosWeb 200');
$claves = array_column($params['json']['resultado']['items'] ?? [], 'clave');
assertOk(in_array('IncluyeArticulosNoStockeables', $claves, true), 'Param IncluyeArticulosNoStockeables listado');

// OpenAPI UI accesible
$openApiUi = request('GET', 'http://127.0.0.1:8088/api/documentation', ['Accept: text/html']);
assertOk($openApiUi['status'] === 200, 'OpenAPI UI /api/documentation 200');

$openApiJson = request('GET', 'http://127.0.0.1:8088/docs?api-docs.json', ['Accept: application/json']);
assertOk($openApiJson['status'] === 200, 'OpenAPI JSON accesible');
$historialPath = $openApiJson['json']['paths']['/api/v1/consultas/historial-ventas']['get']['parameters'] ?? [];
$paramNames = array_column($historialPath, 'name');
assertOk(in_array('fecha_desde', $paramNames, true), 'OpenAPI documenta fecha_desde');
assertOk(in_array('fecha_hasta', $paramNames, true), 'OpenAPI documenta fecha_hasta');

echo PHP_EOL.'Smoke CC #12 F: TODOS OK'.PHP_EOL;
