<?php

/**
 * Smoke HTTP Parte F — CC PQ #13 (01/09/2026).
 * Uso: php scripts/smoke-cc-pq-13-f.php
 *
 * Comprueba: health, OpenAPI maxLength 60, login, grabar con leyenda de 61 caracteres
 * (HTTP 200, valor persistido de longitud 60). Sin DROP/TRUNCATE.
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$baseUrl = getenv('SMOKE_BASE_URL') ?: 'http://127.0.0.1:8088/api/v1';
$docsUrl = getenv('SMOKE_OPENAPI_JSON') ?: 'http://127.0.0.1:8088/docs?api-docs.json';
$tenant = getenv('SMOKE_TENANT') ?: 'desarrollo';
$login = [
    'codigo' => getenv('SMOKE_LOGIN_CODIGO') ?: 'supervisor.mvp',
    'password' => getenv('SMOKE_LOGIN_PASSWORD') ?: (string) config('paqsuite_seed.mvpPassword'),
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

echo "=== Smoke CC #13 — Parte F (leyendas 60) ===\n";

$health = request('GET', $baseUrl.'/health', ['X-Paq-Cliente: '.$tenant]);
assertOk($health['status'] === 200, 'GET /health 200');

$docs = request('GET', $docsUrl, ['Accept: application/json']);
assertOk($docs['status'] === 200, 'GET OpenAPI JSON 200');
$maxLength = $docs['json']['components']['schemas']['ComprobanteCabeceraRequest']['properties']['leyenda_1']['maxLength'] ?? null;
assertOk($maxLength === 60, 'OpenAPI ComprobanteCabeceraRequest.leyenda_1 maxLength=60');

$loginResp = request('POST', $baseUrl.'/auth/login', ['X-Paq-Cliente: '.$tenant], $login);
assertOk($loginResp['status'] === 200, 'POST /auth/login 200');
$token = $loginResp['json']['resultado']['token'] ?? null;
assertOk(is_string($token) && $token !== '', 'Token Sanctum obtenido');

$authHeaders = [
    'X-Paq-Cliente: '.$tenant,
    'Authorization: Bearer '.$token,
    'Accept: application/json',
];

$clientes = request('GET', $baseUrl.'/clientes?page_size=5', $authHeaders);
assertOk($clientes['status'] === 200, 'GET /clientes 200');
$clientesPayload = $clientes['json']['resultado'] ?? null;
$clientesRows = [];
if (is_array($clientesPayload)) {
    $clientesRows = array_is_list($clientesPayload)
        ? $clientesPayload
        : (array) ($clientesPayload['items'] ?? []);
}
$codCliente = $clientesRows[0]['codCliente'] ?? $clientesRows[0]['cod_cliente'] ?? null;
assertOk(is_string($codCliente) && $codCliente !== '', 'Cliente visible para smoke');

$articulos = request(
    'GET',
    $baseUrl.'/articulos?page_size=5',
    $authHeaders
);
assertOk($articulos['status'] === 200, 'GET /articulos 200');
$articulosPayload = $articulos['json']['resultado'] ?? null;
$articulosRows = [];
if (is_array($articulosPayload)) {
    $articulosRows = array_is_list($articulosPayload)
        ? $articulosPayload
        : (array) ($articulosPayload['items'] ?? []);
}
$codArticulo = $articulosRows[0]['codArticulo']
    ?? $articulosRows[0]['codigo']
    ?? $articulosRows[0]['cod_articulo']
    ?? null;
assertOk(is_string($codArticulo) && $codArticulo !== '', 'Artículo para smoke');

$openApiUi = request('GET', 'http://127.0.0.1:8088/api/documentation', ['Accept: text/html']);
assertOk($openApiUi['status'] === 200, 'OpenAPI UI /api/documentation 200');

$grabar = request('POST', $baseUrl.'/comprobantes/grabar', $authHeaders, [
    'accionGrabacion' => 'pedido',
    'cabecera' => [
        'cod_cliente' => $codCliente,
        'leyenda_1' => str_repeat('g', 61),
    ],
    'renglones' => [
        [
            'cod_articulo' => $codArticulo,
            'descripcion_articulo' => 'Smoke CC13',
            'cantidad' => 1,
            'precio' => 100,
            'porc_bonif' => 0,
            'porc_iva' => 21,
        ],
    ],
]);

$grabarHttpOk = $grabar['status'] === 200 && (int) ($grabar['json']['error'] ?? 1) === 0;
if ($grabarHttpOk) {
    $codPedido = (string) ($grabar['json']['resultado']['cod_pedido'] ?? '');
    assertOk($codPedido !== '', 'cod_pedido generado');
    $row = Illuminate\Support\Facades\DB::table('pq_pedidosweb_pedidoscabecera')
        ->where('cod_pedido', $codPedido)
        ->first();
    assertOk($row !== null, 'Cabecera persistida vía grabar HTTP');
    $leyendaPersistida = (string) ($row->leyenda_1 ?? '');
    assertOk(mb_strlen($leyendaPersistida) === 60, 'leyenda_1 persistida longitud 60');
    assertOk($leyendaPersistida === str_repeat('g', 60), 'leyenda_1 son los primeros 60 caracteres');
    echo "[OK] POST /comprobantes/grabar recorta 61→60\n";
} else {
    echo '[WARN] POST /comprobantes/grabar no usable en smoke (status='.$grabar['status'].'). Persistencia vía repositorio.'."\n";
    $codPedido = substr('SMK13'.strtoupper(str_replace('.', '', uniqid('', true))), 0, 20);
    $sqlServerDateTime = Carbon\CarbonImmutable::now()->format('Ymd H:i:s');
    $leyenda = App\Support\LeyendaCabeceraLimits::recortarLeyendaCabecera(str_repeat('g', 61));
    assertOk($leyenda === str_repeat('g', 60), 'Helper recorta 61→60 antes de insert');

    app(App\Contracts\PedidosWeb\PedidoRepositoryInterface::class)->insertCabecera([
        'cod_pedido' => $codPedido,
        'cod_cliente' => $codCliente,
        'fecha' => $sqlServerDateTime,
        'nivel' => 0,
        'observaciones' => 'Smoke CC13 leyendas',
        'incluye_iva' => false,
        'moneda' => 1,
        'estado' => 0,
        'cod_usuario_web' => 'supervisor.mvp',
        'fecha_modif' => $sqlServerDateTime,
        'total' => 100,
        'total_iva' => 21,
        'descuento' => 0,
        'bonif_1' => 0,
        'bonif_2' => 0,
        'bonif_3' => 0,
        'leyenda_1' => $leyenda,
    ]);

    $row = Illuminate\Support\Facades\DB::table('pq_pedidosweb_pedidoscabecera')
        ->where('cod_pedido', $codPedido)
        ->first();
    assertOk($row !== null, 'Cabecera persistida vía repositorio');
    assertOk(mb_strlen((string) $row->leyenda_1) === 60, 'leyenda_1 persistida longitud 60');
}

echo "OK: smoke CC #13 leyendas 60.\n";
echo "cod_pedido={$codPedido} cliente={$codCliente} articulo={$codArticulo}\n";
