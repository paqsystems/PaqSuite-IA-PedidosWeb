<?php

/**
 * Smoke API — importación Excel PEDIDO_INDIVIDUAL (SPEC-101-16).
 * Uso: php scripts/smoke-pedido-individual-excel.php [--base-url=http://127.0.0.1:8000]
 */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\PqPedidoswebArticulo;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = 'http://127.0.0.1:8000';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--base-url=')) {
        $baseUrl = rtrim(substr($arg, 11), '/');
    }
}

$tenant = 'desarrollo';
$loginCodigo = 'vendedor.acotado.mvp';
$loginPassword = (string) env('SEED_MVP_PASSWORD', 'ChangeMeInLocalEnv');

function smokeStep(string $label, callable $fn): void
{
    echo "\n=== {$label} ===\n";
    $fn();
    echo "OK\n";
}

function smokeRequest(
    string $method,
    string $url,
    array $headers = [],
    mixed $body = null,
): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    if ($body !== null) {
        if (is_string($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
    }

    $responseBody = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($responseBody === false) {
        throw new RuntimeException("curl failed for {$method} {$url}");
    }

    return [
        'status' => $status,
        'body' => $responseBody,
        'contentType' => $contentType,
        'json' => json_decode($responseBody, true),
    ];
}

function smokeJson(array $response, int $expectedStatus = 200): array
{
    if ($response['status'] !== $expectedStatus) {
        throw new RuntimeException(
            "HTTP {$response['status']} esperado {$expectedStatus}: ".substr((string) $response['body'], 0, 500)
        );
    }

    $json = $response['json'];
    if (! is_array($json)) {
        throw new RuntimeException('Respuesta no es JSON');
    }

    if (($json['error'] ?? -1) !== 0) {
        throw new RuntimeException('error='.$json['error'].' respuesta='.($json['respuesta'] ?? ''));
    }

    return $json;
}

/** @return list<string> */
function pedidoIndividualSpanishHeaders(): array
{
    return [
        'codigo cliente',
        'codigo de articulo',
        'cantidad',
        'precio lista',
        'bonif renglon',
        'codigo perfil',
        'condicion de venta',
        'codigo transporte',
        'direccion entrega',
        'codigo lista',
        'nivel',
        'bonificacion 1',
        'bonificacion 2',
        'bonificacion 3',
        'expreso',
        'direccion expreso',
        'fecha entrega',
        'observaciones',
        'leyenda 1',
        'leyenda 2',
        'leyenda 3',
        'leyenda 4',
        'leyenda 5',
    ];
}

function buildPedidoIndividualWorkbook(string $codCliente, string $codArticulo, float $cantidad): string
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Hoja1');

    foreach (pedidoIndividualSpanishHeaders() as $index => $header) {
        $sheet->setCellValue([$index + 1, 1], $header);
    }

    $sheet->setCellValue([1, 2], $codCliente);
    $sheet->setCellValue([2, 2], $codArticulo);
    $sheet->setCellValue([3, 2], $cantidad);

    $path = tempnam(sys_get_temp_dir(), 'pedido_individual_smoke_');
    if ($path === false) {
        throw new RuntimeException('No se pudo crear archivo temporal');
    }

    $target = $path.'.xlsx';
    @unlink($path);
    (new Xlsx($spreadsheet))->save($target);

    return $target;
}

/** @return array{codCliente: string, codArticulo: string} */
function resolveFixtureFromDb(): array
{
    $cliente = DB::table('pq_pedidosweb_clientes')
        ->where('cod_vended', 'VENACOT01')
        ->orderBy('cod_client')
        ->value('cod_client');

    if (! is_string($cliente) || $cliente === '') {
        $cliente = DB::table('pq_pedidosweb_clientes')->orderBy('cod_client')->value('cod_client');
    }

    if (! is_string($cliente) || $cliente === '') {
        throw new RuntimeException('No hay clientes en pq_pedidosweb_clientes');
    }

    $lista = (int) (DB::table('pq_pedidosweb_clientes')->where('cod_client', $cliente)->value('lista_precios') ?? 1);

    $codArticulo = PqPedidoswebArticulo::query()
        ->excluirArticulosBaseCarga()
        ->whereIn('codigo', function ($query) use ($lista): void {
            $query->select('cod_articulo')
                ->from('pq_pedidosweb_listaprecios_articulos')
                ->where('cod_lista', $lista)
                ->where('precio', '>', 0);
        })
        ->orderBy('codigo')
        ->value('codigo');

    if (! is_string($codArticulo) || trim($codArticulo) === '') {
        throw new RuntimeException('No hay articulos aptos (no BASE) con precio en lista');
    }

    $codArticulo = trim($codArticulo);

    return ['codCliente' => $cliente, 'codArticulo' => $codArticulo];
}

echo "Smoke PEDIDO_INDIVIDUAL — base {$baseUrl} tenant {$tenant}\n";

$fixture = resolveFixtureFromDb();
echo "Fixture: cliente={$fixture['codCliente']} articulo={$fixture['codArticulo']}\n";

$commonHeaders = [
    'Accept: application/json',
    'X-Paq-Cliente: '.$tenant,
];

$token = '';

smokeStep('Login', function () use ($baseUrl, $commonHeaders, $loginCodigo, $loginPassword, &$token): void {
    $response = smokeRequest(
        'POST',
        $baseUrl.'/api/v1/auth/login',
        array_merge($commonHeaders, ['Content-Type: application/json']),
        json_encode(['codigo' => $loginCodigo, 'password' => $loginPassword], JSON_THROW_ON_ERROR),
    );
    $json = smokeJson($response);
    $token = (string) ($json['resultado']['token'] ?? '');
    if ($token === '') {
        throw new RuntimeException('Token vacío');
    }
    echo "token obtenido\n";
});

$authHeaders = array_merge($commonHeaders, ['Authorization: Bearer '.$token]);

smokeStep('Config pública excelImportEnabled', function () use ($baseUrl, $authHeaders): void {
    $json = smokeJson(smokeRequest('GET', $baseUrl.'/api/v1/config/public', $authHeaders));
    if (! ($json['resultado']['excelImportEnabled'] ?? false)) {
        throw new RuntimeException('excelImportEnabled=false');
    }
});

smokeStep('Metadata PEDIDO_INDIVIDUAL', function () use ($baseUrl, $authHeaders): void {
    $json = smokeJson(smokeRequest(
        'GET',
        $baseUrl.'/api/v1/excel-import/procesos/PEDIDO_INDIVIDUAL',
        $authHeaders,
    ));
    if (($json['resultado']['codigoProceso'] ?? '') !== 'PEDIDO_INDIVIDUAL') {
        throw new RuntimeException('codigoProceso inesperado');
    }
    if (! ($json['resultado']['generaPlantilla'] ?? false)) {
        throw new RuntimeException('generaPlantilla=false');
    }
});

smokeStep('Descarga plantilla ES', function () use ($baseUrl, $authHeaders): void {
    $response = smokeRequest(
        'GET',
        $baseUrl.'/api/v1/excel-import/procesos/PEDIDO_INDIVIDUAL/plantilla',
        array_merge($authHeaders, ['Accept-Language: es']),
    );
    if ($response['status'] !== 200) {
        throw new RuntimeException('Plantilla HTTP '.$response['status']);
    }
    if (! str_contains($response['contentType'], 'spreadsheetml')) {
        throw new RuntimeException('Content-Type inesperado: '.$response['contentType']);
    }
    if (strlen((string) $response['body']) < 1000) {
        throw new RuntimeException('Archivo plantilla demasiado pequeño');
    }
    echo 'bytes='.strlen((string) $response['body'])."\n";
});

$workbookPath = buildPedidoIndividualWorkbook($fixture['codCliente'], $fixture['codArticulo'], 2.0);
$guid = '';

smokeStep('Crear lote desde Excel', function () use (
    $baseUrl,
    $authHeaders,
    $workbookPath,
    &$guid,
): void {
    $postFields = [
        'hojaSeleccionada' => 'Hoja1',
        'archivo' => new CURLFile($workbookPath, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'pedido_individual_smoke.xlsx'),
    ];

    $response = smokeRequest(
        'POST',
        $baseUrl.'/api/v1/excel-import/procesos/PEDIDO_INDIVIDUAL/lotes',
        array_merge($authHeaders, ['Accept: application/json']),
        $postFields,
    );
    $json = smokeJson($response);
    $guid = (string) ($json['resultado']['guidImportacion'] ?? '');
    $estado = (string) ($json['resultado']['estadoImportacion'] ?? '');
    $validas = (int) ($json['resultado']['cantidadFilasValidas'] ?? 0);
    $errores = (int) ($json['resultado']['cantidadFilasConError'] ?? 0);

    echo "guid={$guid} estado={$estado} validas={$validas} errores={$errores}\n";

    if ($guid === '') {
        throw new RuntimeException('guidImportacion vacío');
    }
    if ($estado !== 'lista_para_procesar') {
        throw new RuntimeException('estado inesperado: '.$estado);
    }
    if ($validas < 1) {
        throw new RuntimeException('Sin filas válidas');
    }
});

smokeStep('Filas válidas enriquecidas (sin procesar)', function () use ($baseUrl, $authHeaders, $guid, $fixture): void {
    $json = smokeJson(smokeRequest(
        'GET',
        $baseUrl.'/api/v1/excel-import/lotes/'.$guid.'/filas/validas',
        $authHeaders,
    ));
    $total = (int) ($json['resultado']['total'] ?? 0);
    $item = $json['resultado']['items'][0]['datos'] ?? null;
    if ($total < 1 || ! is_array($item)) {
        throw new RuntimeException('Sin payload en filas válidas');
    }
    if ((float) ($item['precio'] ?? 0) <= 0) {
        throw new RuntimeException('precio no resuelto antes de procesar');
    }
    echo 'precio pre-proceso='.($item['precio'] ?? '?')."\n";
});

smokeStep('Procesar lote', function () use ($baseUrl, $authHeaders, $guid): void {
    $json = smokeJson(smokeRequest(
        'POST',
        $baseUrl.'/api/v1/excel-import/lotes/'.$guid.'/procesar',
        array_merge($authHeaders, ['Content-Type: application/json']),
        '{}',
    ));
    $estado = (string) ($json['resultado']['estadoImportacion'] ?? '');
    echo "estado post-proceso={$estado}\n";
    if (! in_array($estado, ['procesado_ok', 'procesada'], true)) {
        throw new RuntimeException('Procesamiento no exitoso: '.$estado);
    }
});

@unlink($workbookPath);

echo "\n=== SMOKE COMPLETO OK ===\n";
