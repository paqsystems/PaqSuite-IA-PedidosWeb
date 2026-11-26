<?php

/**
 * Collection Postman — GET /api/v1/integracion/comprobantes (SPEC-101-25).
 *
 * Salida versionada (importar en Postman):
 *   docs/06-operacion/postman/PedidosWeb-Integracion-Export-Comprobantes.postman_collection.json
 *   docs/06-operacion/postman/PedidosWeb-Integracion-Export-Comprobantes.postman_environment.json
 */

declare(strict_types=1);

$repoRoot = dirname(__DIR__, 2);
$outputDirectory = $repoRoot.'/docs/06-operacion/postman';
$generatedAt = new DateTimeImmutable('now', new DateTimeZone('America/Argentina/Buenos_Aires'));

if (! is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0777, true);
}

$envelopeOkTests = [
    'pm.test("HTTP 200", function () { pm.response.to.have.status(200); });',
    'const body = pm.response.json();',
    'pm.test("envelope GEN-20 exito", function () {',
    '    pm.expect(body.error).to.eql(0);',
    '    pm.expect(body.respuesta).to.eql("integracion.export.ok");',
    '});',
    'pm.test("resultado paginado", function () {',
    '    const r = body.resultado;',
    '    pm.expect(r).to.include.keys("items", "page", "pageSize", "total", "totalPages", "metadata");',
    '    pm.expect(r.items).to.be.an("array");',
    '    pm.expect(r.metadata).to.have.property("fechaProceso");',
    '});',
    'pm.test("item export shape", function () {',
    '    if (body.resultado.items.length === 0) { return; }',
    '    const item = body.resultado.items[0];',
    '    pm.expect(item).to.include.keys("cabecera", "renglones", "cierre");',
    '    pm.expect(item.cabecera).to.have.property("codPedido");',
    '    pm.expect(item.cabecera).to.have.property("codUsuarioWeb");',
    '    pm.expect(item.cabecera).to.not.have.property("puedeEditar");',
    '    pm.expect(item.renglones).to.be.an("array");',
    '});',
];

$loginTestScript = [
    'const body = pm.response.json();',
    'pm.test("login OK", function () {',
    '    pm.response.to.have.status(200);',
    '    pm.expect(body.error).to.eql(0);',
    '});',
    'const token = body?.resultado?.token;',
    'if (token) {',
    '    pm.environment.set("integrationToken", token);',
    '}',
];

$authHeaders = [
    ['key' => 'Accept', 'value' => 'application/json'],
    ['key' => 'Content-Type', 'value' => 'application/json'],
    ['key' => 'X-Paq-Cliente', 'value' => '{{tenant}}'],
];

$integrationHeaders = [
    ['key' => 'Accept', 'value' => 'application/json'],
    ['key' => 'X-Paq-Cliente', 'value' => '{{tenant}}'],
    ['key' => 'Authorization', 'value' => 'Bearer {{integrationToken}}'],
];

$makeGet = static function (
    string $name,
    string $description,
    string $rawUrl,
    array $path,
    array $query,
    array $headers,
    array $testExec,
): array {
    return [
        'name' => $name,
        'request' => [
            'method' => 'GET',
            'header' => $headers,
            'url' => [
                'raw' => $rawUrl,
                'host' => ['{{backendUrl}}'],
                'path' => $path,
                'query' => $query,
            ],
            'description' => $description,
        ],
        'event' => [
            [
                'listen' => 'test',
                'script' => ['type' => 'text/javascript', 'exec' => $testExec],
            ],
        ],
        'response' => [],
    ];
};

$exportItems = [
    $makeGet(
        'export - pedidos ingresados (estado 0:0)',
        'Página mínima de pedidos en estado 0 (ingresados). Ajustar estadoRangos según catálogo producto.',
        '{{backendUrl}}/api/v1/integracion/comprobantes?estadoRangos=0:0&page=1&pageSize={{exportPageSize}}',
        ['api', 'v1', 'integracion', 'comprobantes'],
        [
            ['key' => 'estadoRangos', 'value' => '0:0'],
            ['key' => 'page', 'value' => '1'],
            ['key' => 'pageSize', 'value' => '{{exportPageSize}}'],
        ],
        $integrationHeaders,
        $envelopeOkTests,
    ),
    $makeGet(
        'export - union OR (-1:-1 y 99:99)',
        'Múltiples query estadoRangos = unión OR de intervalos (SPEC §4).',
        '{{backendUrl}}/api/v1/integracion/comprobantes?estadoRangos=-1:-1&estadoRangos=99:99&pageSize={{exportPageSize}}',
        ['api', 'v1', 'integracion', 'comprobantes'],
        [
            ['key' => 'estadoRangos', 'value' => '-1:-1'],
            ['key' => 'estadoRangos', 'value' => '99:99'],
            ['key' => 'pageSize', 'value' => '{{exportPageSize}}'],
        ],
        $integrationHeaders,
        array_merge($envelopeOkTests, [
            'pm.test("estados dentro de rangos pedidos", function () {',
            '    const estados = body.resultado.items.map((i) => i.cabecera.estado);',
            '    estados.forEach((e) => pm.expect([-1, 99]).to.include(e));',
            '});',
        ]),
    ),
    $makeGet(
        'export - filtro codCliente',
        'Filtra por cliente exacto. Definir exportCodCliente en el environment.',
        '{{backendUrl}}/api/v1/integracion/comprobantes?estadoRangos=-1:99&codCliente={{exportCodCliente}}&pageSize={{exportPageSize}}',
        ['api', 'v1', 'integracion', 'comprobantes'],
        [
            ['key' => 'estadoRangos', 'value' => '-1:99'],
            ['key' => 'codCliente', 'value' => '{{exportCodCliente}}'],
            ['key' => 'pageSize', 'value' => '{{exportPageSize}}'],
        ],
        $integrationHeaders,
        array_merge($envelopeOkTests, [
            'pm.test("codCliente filtrado", function () {',
            '    const esperado = pm.environment.get("exportCodCliente");',
            '    body.resultado.items.forEach((i) => pm.expect(i.cabecera.codCliente).to.eql(esperado));',
            '});',
        ]),
    ),
    $makeGet(
        'export - filtro codPedido',
        'Un comprobante puntual. Completar exportCodPedido en el environment con un codPedido real.',
        '{{backendUrl}}/api/v1/integracion/comprobantes?estadoRangos=-1:99&codPedido={{exportCodPedido}}',
        ['api', 'v1', 'integracion', 'comprobantes'],
        [
            ['key' => 'estadoRangos', 'value' => '-1:99'],
            ['key' => 'codPedido', 'value' => '{{exportCodPedido}}'],
        ],
        $integrationHeaders,
        array_merge($envelopeOkTests, [
            'pm.test("codPedido exacto", function () {',
            '    const esperado = pm.environment.get("exportCodPedido");',
            '  if (!esperado) { pm.test.skip("exportCodPedido vacío"); return; }',
            '    pm.expect(body.resultado.items.length).to.be.at.most(1);',
            '    if (body.resultado.items.length === 1) {',
            '        pm.expect(body.resultado.items[0].cabecera.codPedido).to.eql(esperado);',
            '    }',
            '});',
        ]),
    ),
    $makeGet(
        'export - presupuestos cerrados (98:98)',
        'Estado 98: cierre presente o null si no hay fila en presupuestos_cierres.',
        '{{backendUrl}}/api/v1/integracion/comprobantes?estadoRangos=98:98&pageSize={{exportPageSize}}',
        ['api', 'v1', 'integracion', 'comprobantes'],
        [
            ['key' => 'estadoRangos', 'value' => '98:98'],
            ['key' => 'pageSize', 'value' => '{{exportPageSize}}'],
        ],
        $integrationHeaders,
        array_merge($envelopeOkTests, [
            'pm.test("estado 98 en items", function () {',
            '    body.resultado.items.forEach((i) => {',
            '        pm.expect(i.cabecera.estado).to.eql(98);',
            '        pm.expect(i).to.have.property("cierre");',
            '    });',
            '});',
        ]),
    ),
    $makeGet(
        'export - rango fechas',
        'fechaDesde/fechaHasta inclusivos sobre fecha de cabecera (ISO 8601).',
        '{{backendUrl}}/api/v1/integracion/comprobantes?estadoRangos=0:99&fechaDesde={{exportFechaDesde}}&fechaHasta={{exportFechaHasta}}&pageSize={{exportPageSize}}',
        ['api', 'v1', 'integracion', 'comprobantes'],
        [
            ['key' => 'estadoRangos', 'value' => '0:99'],
            ['key' => 'fechaDesde', 'value' => '{{exportFechaDesde}}'],
            ['key' => 'fechaHasta', 'value' => '{{exportFechaHasta}}'],
            ['key' => 'pageSize', 'value' => '{{exportPageSize}}'],
        ],
        $integrationHeaders,
        $envelopeOkTests,
    ),
    $makeGet(
        'export - alias page_size',
        'Compatibilidad query page_size → pageSize.',
        '{{backendUrl}}/api/v1/integracion/comprobantes?estadoRangos=0:0&page_size=25',
        ['api', 'v1', 'integracion', 'comprobantes'],
        [
            ['key' => 'estadoRangos', 'value' => '0:0'],
            ['key' => 'page_size', 'value' => '25'],
        ],
        $integrationHeaders,
        array_merge($envelopeOkTests, [
            'pm.test("pageSize desde page_size", function () {',
            '    pm.expect(body.resultado.pageSize).to.eql(25);',
            '});',
        ]),
    ),
];

$negativeItems = [
    $makeGet(
        'export - sin token (401)',
        'Debe responder sin Bearer.',
        '{{backendUrl}}/api/v1/integracion/comprobantes?estadoRangos=0:0',
        ['api', 'v1', 'integracion', 'comprobantes'],
        [['key' => 'estadoRangos', 'value' => '0:0']],
        [
            ['key' => 'Accept', 'value' => 'application/json'],
            ['key' => 'X-Paq-Cliente', 'value' => '{{tenant}}'],
        ],
        [
            'pm.test("HTTP 401", function () { pm.response.to.have.status(401); });',
            'const body = pm.response.json();',
            'pm.test("envelope no autenticado", function () {',
            '    pm.expect(body.error).to.eql(2002);',
            '    pm.expect(body.respuesta).to.eql("auth.unauthenticated");',
            '});',
        ],
    ),
    $makeGet(
        'export - sin estadoRangos (422)',
        'estadoRangos obligatorio.',
        '{{backendUrl}}/api/v1/integracion/comprobantes',
        ['api', 'v1', 'integracion', 'comprobantes'],
        [],
        $integrationHeaders,
        [
            'pm.test("HTTP 422", function () { pm.response.to.have.status(422); });',
            'const body = pm.response.json();',
            'pm.test("clave envelope", function () {',
            '    pm.expect(body.error).to.eql(1002);',
            '    pm.expect(body.respuesta).to.eql("integracion.export.estadoRangosRequerido");',
            '});',
        ],
    ),
    $makeGet(
        'export - rango invalido (422)',
        'desde > hasta.',
        '{{backendUrl}}/api/v1/integracion/comprobantes?estadoRangos=1:0',
        ['api', 'v1', 'integracion', 'comprobantes'],
        [['key' => 'estadoRangos', 'value' => '1:0']],
        $integrationHeaders,
        [
            'pm.test("HTTP 422", function () { pm.response.to.have.status(422); });',
            'const body = pm.response.json();',
            'pm.test("clave envelope", function () {',
            '    pm.expect(body.respuesta).to.eql("integracion.export.rangoInvalido");',
            '});',
        ],
    ),
];

$collection = [
    'info' => [
        '_postman_id' => 'a4b1c2d3-e5f6-4789-a012-3456789abcde',
        'name' => 'PedidosWeb - Export comprobantes ERP',
        'description' => "SPEC-101-25 · GET /api/v1/integracion/comprobantes\n\n1. Ejecutar **Auth / login - usuario integración**.\n2. Ejecutar requests de **Export (éxito)**.\n3. Validar **Export (errores esperados)**.\n\nUsuario seed: integracion.erp.mvp con ability pw_integracion_importacion.",
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'item' => [
        [
            'name' => 'Auth',
            'item' => [
                [
                    'name' => 'login - usuario integración',
                    'request' => [
                        'method' => 'POST',
                        'header' => $authHeaders,
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'codigo' => '{{loginCodigo}}',
                                'password' => '{{loginPassword}}',
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            'options' => ['raw' => ['language' => 'json']],
                        ],
                        'url' => [
                            'raw' => '{{backendUrl}}/api/v1/auth/login',
                            'host' => ['{{backendUrl}}'],
                            'path' => ['api', 'v1', 'auth', 'login'],
                        ],
                        'description' => 'Guarda resultado.token en integrationToken.',
                    ],
                    'event' => [
                        [
                            'listen' => 'test',
                            'script' => ['type' => 'text/javascript', 'exec' => $loginTestScript],
                        ],
                    ],
                    'response' => [],
                ],
            ],
        ],
        [
            'name' => 'Export (éxito)',
            'description' => 'Contrato cabecera + renglones + cierre; paginación y filtros.',
            'item' => $exportItems,
        ],
        [
            'name' => 'Export (errores esperados)',
            'item' => $negativeItems,
        ],
    ],
];

$defaultFechaDesde = $generatedAt->modify('-30 days')->format('Y-m-d');
$defaultFechaHasta = $generatedAt->format('Y-m-d');

$environment = [
    'id' => 'b5c6d7e8-f901-4234-a567-890abcdef012',
    'name' => 'PedidosWeb - Export comprobantes (local)',
    'values' => [
        ['key' => 'backendUrl', 'value' => 'http://127.0.0.1:8088', 'type' => 'default', 'enabled' => true],
        ['key' => 'tenant', 'value' => 'demo', 'type' => 'default', 'enabled' => true],
        ['key' => 'loginCodigo', 'value' => 'integracion.erp.mvp', 'type' => 'default', 'enabled' => true],
        ['key' => 'loginPassword', 'value' => '', 'type' => 'secret', 'enabled' => true],
        ['key' => 'integrationToken', 'value' => '', 'type' => 'secret', 'enabled' => true],
        ['key' => 'exportPageSize', 'value' => '20', 'type' => 'default', 'enabled' => true],
        ['key' => 'exportCodCliente', 'value' => 'CLIMVP001', 'type' => 'default', 'enabled' => true],
        ['key' => 'exportCodPedido', 'value' => '', 'type' => 'default', 'enabled' => true],
        ['key' => 'exportFechaDesde', 'value' => $defaultFechaDesde, 'type' => 'default', 'enabled' => true],
        ['key' => 'exportFechaHasta', 'value' => $defaultFechaHasta, 'type' => 'default', 'enabled' => true],
    ],
    '_postman_variable_scope' => 'environment',
    '_postman_exported_at' => $generatedAt->format('c'),
    '_postman_exported_using' => 'PaqSuite-IA-PedidosWeb generar-postman-integracion-export.php',
];

$collectionPath = $outputDirectory.'/PedidosWeb-Integracion-Export-Comprobantes.postman_collection.json';
$environmentPath = $outputDirectory.'/PedidosWeb-Integracion-Export-Comprobantes.postman_environment.json';

$jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

file_put_contents($collectionPath, json_encode($collection, $jsonFlags).PHP_EOL);
file_put_contents($environmentPath, json_encode($environment, $jsonFlags).PHP_EOL);

echo json_encode([
    'collection' => $collectionPath,
    'environment' => $environmentPath,
    'exportRequests' => count($exportItems),
    'negativeRequests' => count($negativeItems),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;