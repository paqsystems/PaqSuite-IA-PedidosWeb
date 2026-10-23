<?php

$outputDirectory = dirname(__DIR__).'/storage/app/postman/integracion';
$generatedAt = new DateTimeImmutable('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
if (! is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0777, true);
}

$fixtureDirectories = glob($outputDirectory.'/20*', GLOB_ONLYDIR) ?: [];
rsort($fixtureDirectories);
$latestFixtureDirectory = $fixtureDirectories[0] ?? null;

$resources = [
    'maestros' => [
        'provincias' => true,
        'condventa' => true,
        'transportes' => true,
        'perfiles' => true,
        'vendedores' => true,
        'listaprecios' => true,
        'escalas' => true,
        'articulos' => true,
        'listaprecios-articulos' => false,
        'descuentocantidad' => false,
        'clientes' => true,
        'clientes-direcciones' => false,
        'clientes-contactos' => false,
        'parametros' => false,
    ],
    'transacciones' => [
        'stock' => true,
        'deuda' => true,
        'cheques' => true,
        'historial-ventas' => false,
    ],
];

$requestItems = [];
$requestItems['auth'][] = [
    'name' => 'login - obtener token',
    'request' => [
        'method' => 'POST',
        'header' => [
            ['key' => 'Accept', 'value' => 'application/json'],
            ['key' => 'Content-Type', 'value' => 'application/json'],
            ['key' => 'X-Paq-Cliente', 'value' => '{{tenant}}'],
        ],
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
        'description' => 'Ejecutar primero. El script toma resultado.token y lo guarda en integrationToken para las requests de integración.',
    ],
    'event' => [
        [
            'listen' => 'test',
            'script' => [
                'type' => 'text/javascript',
                'exec' => [
                    'const body = pm.response.json();',
                    'const token = body?.resultado?.token;',
                    'if (token) {',
                    '    pm.environment.set("integrationToken", token);',
                    '    console.log("integrationToken actualizado");',
                    '}',
                ],
            ],
        ],
    ],
    'response' => [],
];

foreach ($resources as $kind => $resourceDefinitions) {
    foreach ($resourceDefinitions as $resource => $supportsSnapshot) {
        $mode = $supportsSnapshot ? 'snapshot' : 'delta';
        $fixtures = findFixtures($latestFixtureDirectory, $kind, $resource);
        $splitSnapshot = $supportsSnapshot && count($fixtures) > 1;
        foreach ($fixtures as $fixture) {
            $isEmpty = $fixture === null;
            $suffix = $isEmpty ? 'sin-datos' : $fixture['suffix'];
            $requestMode = $splitSnapshot ? 'delta' : $mode;
            $requestItems[$kind][] = [
                'name' => $resource.'-'.$suffix,
                'request' => [
                    'method' => 'POST',
                    'header' => [
                        ['key' => 'Accept', 'value' => 'application/json'],
                        ['key' => 'Content-Type', 'value' => 'application/json'],
                        ['key' => 'X-Paq-Cliente', 'value' => '{{tenant}}'],
                        ['key' => 'Authorization', 'value' => 'Bearer {{integrationToken}}'],
                    ],
                    'body' => [
                        'mode' => 'raw',
                        'raw' => json_encode([
                            'modo' => $requestMode,
                            'fechaProceso' => '{{fechaProceso}}',
                            'origen' => 'postman',
                            'items' => $isEmpty ? [] : $fixture['items'],
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'options' => ['raw' => ['language' => 'json']],
                    ],
                    'url' => [
                        'raw' => '{{backendUrl}}/api/v1/integracion/'.$kind.'/'.$resource,
                        'host' => ['{{backendUrl}}'],
                        'path' => ['api', 'v1', 'integracion', $kind, $resource],
                    ],
                    'description' => $isEmpty
                        ? 'Sin datos en la base actual. Este request prueba el endpoint con items vacío; snapshot vacío debe ser rechazado por contrato.'
                        : ($splitSnapshot
                            ? 'Lote '.$fixture['suffix'].' enviado como delta para evitar que cada parte de un snapshot dividido elimine registros pertenecientes a las otras partes. La baja completa requiere un contrato de snapshot multi-lote.'
                        : 'Lote completo '.$fixture['suffix'].' generado desde la base. Los snapshots eliminan los registros ausentes cuando no tienen referencias que impidan la baja.'
                        ),
                ],
                'response' => [],
            ];
        }
    }
}

$collection = [
    'info' => [
        '_postman_id' => 'c9a9e76d-72a4-4b3f-9b0a-8e1c6a7b8d90',
        'name' => 'PedidosWeb - Integración ERP',
        'description' => 'Ejecutar primero Auth/login - obtener token. El token se guarda automáticamente en integrationToken y luego puede usarse en todos los endpoints de importación.',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'variable' => [
        ['key' => 'fixtureDirectory', 'value' => $latestFixtureDirectory !== null ? basename($latestFixtureDirectory) : ''],
    ],
    'item' => [
        [
            'name' => 'Auth',
            'description' => 'Login tenant-aware. Completar loginPassword en el environment.',
            'item' => $requestItems['auth'],
        ],
        [
            'name' => 'Maestros',
            'description' => 'Orden contractual: provincias, condiciones, transportes, perfiles, vendedores, listas, escalas, artículos, precios, descuentos, clientes, direcciones, contactos y parámetros.',
            'item' => $requestItems['maestros'],
        ],
        [
            'name' => 'Transacciones',
            'description' => 'Stock, deuda, cheques e historial de ventas.',
            'item' => $requestItems['transacciones'],
        ],
    ],
];

$environment = [
    'id' => 'e2a8d5d3-9a04-4f5c-8e1d-1f63a68c7f41',
    'name' => 'PedidosWeb - Integración local',
    'values' => [
        ['key' => 'backendUrl', 'value' => 'http://localhost:8088', 'type' => 'default', 'enabled' => true],
        ['key' => 'tenant', 'value' => 'demo', 'type' => 'default', 'enabled' => true],
        ['key' => 'loginCodigo', 'value' => 'PAQ', 'type' => 'default', 'enabled' => true],
        ['key' => 'loginPassword', 'value' => 'REEMPLAZAR_CON_PASSWORD', 'type' => 'secret', 'enabled' => true],
        ['key' => 'integrationToken', 'value' => 'REEMPLAZAR_CON_TOKEN_INTEGRACION', 'type' => 'secret', 'enabled' => true],
        ['key' => 'fechaProceso', 'value' => $generatedAt->format('c'), 'type' => 'default', 'enabled' => true],
    ],
    '_postman_variable_scope' => 'environment',
    '_postman_exported_at' => $generatedAt->format('c'),
    '_postman_exported_using' => 'PaqSuite-IA-PedidosWeb',
];

file_put_contents(
    $outputDirectory.'/PedidosWeb-Integracion.postman_collection.json',
    json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL
);
file_put_contents(
    $outputDirectory.'/PedidosWeb-Integracion.postman_environment.json',
    json_encode($environment, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL
);

echo json_encode([
    'collection' => 'PedidosWeb-Integracion.postman_collection.json',
    'environment' => 'PedidosWeb-Integracion.postman_environment.json',
    'requests' => array_sum(array_map('count', $requestItems)),
    'fixtureDirectory' => $latestFixtureDirectory !== null ? basename($latestFixtureDirectory) : null,
], JSON_UNESCAPED_UNICODE).PHP_EOL;

function findFixtures(?string $fixtureDirectory, string $kind, string $resource): array
{
    if ($fixtureDirectory === null) {
        return [null];
    }

    $files = glob($fixtureDirectory.'/quento/'.$kind.'/'.$resource.'-*.json') ?: [];
    sort($files);
    if ($files === []) {
        return [null];
    }

    $fixtures = [];
    foreach ($files as $file) {
        $payload = json_decode((string) file_get_contents($file), true);
        if (! is_array($payload) || ! isset($payload['items']) || ! is_array($payload['items'])) {
            continue;
        }
        $fixtures[] = [
            'suffix' => pathinfo($file, PATHINFO_FILENAME),
            'items' => $payload['items'],
        ];
    }

    return $fixtures !== [] ? $fixtures : [null];
}
