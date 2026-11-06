<?php

declare(strict_types=1);

/**
 * Genera database/emission-designer-schemas/{processCode}.json (+ .port-names.json)
 * alineados a los mappers de consulta / EmissionDatasetPort.
 *
 * Uso: php scripts/dev/generate-emission-designer-schemas.php
 */

$backendRoot = dirname(__DIR__, 2);
$outDir = $backendRoot.'/database/emission-designer-schemas';

if (! is_dir($outDir) && ! mkdir($outDir, 0775, true) && ! is_dir($outDir)) {
    fwrite(STDERR, "Cannot create {$outDir}\n");
    exit(1);
}

$comprobanteFields = [
    ['name' => 'codCliente', 'type' => 'string'],
    ['name' => 'razonSocial', 'type' => 'string'],
    ['name' => 'nombreFantasia', 'type' => 'string'],
    ['name' => 'fecha', 'type' => 'date'],
    ['name' => 'nivel', 'type' => 'number'],
    ['name' => 'observaciones', 'type' => 'string'],
    ['name' => 'incluyeIva', 'type' => 'boolean'],
    ['name' => 'moneda', 'type' => 'number'],
    ['name' => 'estado', 'type' => 'number'],
    ['name' => 'fechaModif', 'type' => 'date'],
    ['name' => 'total', 'type' => 'number'],
    ['name' => 'totalIva', 'type' => 'number'],
    ['name' => 'leyenda1', 'type' => 'string'],
    ['name' => 'leyenda2', 'type' => 'string'],
    ['name' => 'leyenda3', 'type' => 'string'],
    ['name' => 'leyenda4', 'type' => 'string'],
    ['name' => 'leyenda5', 'type' => 'string'],
    ['name' => 'descuento', 'type' => 'number'],
    ['name' => 'bonif1', 'type' => 'number'],
    ['name' => 'bonif2', 'type' => 'number'],
    ['name' => 'bonif3', 'type' => 'number'],
    ['name' => 'codPerfil', 'type' => 'string'],
    ['name' => 'perfilDescripcion', 'type' => 'string'],
    ['name' => 'codVended', 'type' => 'string'],
    ['name' => 'vendedorDescripcion', 'type' => 'string'],
    ['name' => 'codCondvta', 'type' => 'number'],
    ['name' => 'condicionVentaDescripcion', 'type' => 'string'],
    ['name' => 'idDe', 'type' => 'number'],
    ['name' => 'direccionEntregaDescripcion', 'type' => 'string'],
    ['name' => 'codTranspor', 'type' => 'string'],
    ['name' => 'transporteDescripcion', 'type' => 'string'],
    ['name' => 'listaPrecios', 'type' => 'number'],
    ['name' => 'listaPreciosDescripcion', 'type' => 'string'],
    ['name' => 'expreso', 'type' => 'string'],
    ['name' => 'expresoDire', 'type' => 'string'],
    ['name' => 'fechaEntrega', 'type' => 'date'],
    ['name' => 'usuarioCreacion', 'type' => 'string'],
    ['name' => 'fechaCreacion', 'type' => 'date'],
    ['name' => 'usuarioModificacion', 'type' => 'string'],
    ['name' => 'fechahoraInicioProceso', 'type' => 'date'],
    ['name' => 'fechahoraUltimaActividad', 'type' => 'date'],
    ['name' => 'numeroVisible', 'type' => 'number'],
    ['name' => 'guidSufijo', 'type' => 'string'],
];

$detalleExtra = [
    ['name' => 'renglon', 'type' => 'number'],
    ['name' => 'codArticulo', 'type' => 'string'],
    ['name' => 'descripcionArticulo', 'type' => 'string'],
    ['name' => 'especial', 'type' => 'string'],
    ['name' => 'cantidad', 'type' => 'number'],
    ['name' => 'cantidadVenta', 'type' => 'number'],
    ['name' => 'porcBonif', 'type' => 'number'],
    ['name' => 'precioLista', 'type' => 'number'],
    ['name' => 'precioNeto', 'type' => 'number'],
    ['name' => 'importeBruto', 'type' => 'number'],
    ['name' => 'importeNeto', 'type' => 'number'],
    ['name' => 'ivaNeto', 'type' => 'number'],
    ['name' => 'importeNetoConIva', 'type' => 'number'],
];

$schemas = [
    'pw_presupuestosingresados' => [
        'datasetDisplayName' => 'Presupuestos ingresados',
        'fields' => array_merge(
            [['name' => 'codPresupuesto', 'type' => 'string']],
            $comprobanteFields,
            [
                ['name' => 'puedeEditar', 'type' => 'boolean'],
                ['name' => 'puedeConvertir', 'type' => 'boolean'],
                ['name' => 'puedeCerrar', 'type' => 'boolean'],
                ['name' => 'puedeCopiar', 'type' => 'boolean'],
            ],
        ),
    ],
    'pw_pedidosingresados' => [
        'datasetDisplayName' => 'Pedidos ingresados',
        'fields' => array_merge(
            [['name' => 'codPedido', 'type' => 'string']],
            $comprobanteFields,
            [
                ['name' => 'puedeEditar', 'type' => 'boolean'],
                ['name' => 'puedeEliminar', 'type' => 'boolean'],
                ['name' => 'puedeCopiar', 'type' => 'boolean'],
            ],
        ),
    ],
    'pw_pedidospendientes' => [
        'datasetDisplayName' => 'Pedidos pendientes',
        'fields' => array_merge(
            [['name' => 'codPedido', 'type' => 'string']],
            $comprobanteFields,
            [
                ['name' => 'puedeEditar', 'type' => 'boolean'],
                ['name' => 'puedeEliminar', 'type' => 'boolean'],
                ['name' => 'puedeCopiar', 'type' => 'boolean'],
            ],
        ),
    ],
    'pw_detallepedidos' => [
        'datasetDisplayName' => 'Detalle de pedidos',
        'fields' => array_merge(
            [['name' => 'codPedido', 'type' => 'string']],
            $comprobanteFields,
            $detalleExtra,
        ),
    ],
    'pw_deudaclientes' => [
        'datasetDisplayName' => 'Deudas',
        'fields' => [
            ['name' => 'codCliente', 'type' => 'string'],
            ['name' => 'razonSocial', 'type' => 'string'],
            ['name' => 'tipo', 'type' => 'string'],
            ['name' => 'numero', 'type' => 'string'],
            ['name' => 'fecha', 'type' => 'date'],
            ['name' => 'vencimiento', 'type' => 'date'],
            ['name' => 'saldo', 'type' => 'number'],
        ],
    ],
    'pw_consultacheques' => [
        'datasetDisplayName' => 'Cheques',
        'fields' => [
            ['name' => 'interno', 'type' => 'string'],
            ['name' => 'numero', 'type' => 'string'],
            ['name' => 'codCliente', 'type' => 'string'],
            ['name' => 'nombreCliente', 'type' => 'string'],
            ['name' => 'banco', 'type' => 'string'],
            ['name' => 'fecha', 'type' => 'date'],
            ['name' => 'importe', 'type' => 'number'],
            ['name' => 'origen', 'type' => 'string'],
            ['name' => 'estado', 'type' => 'string'],
        ],
    ],
    'pw_historialventas' => [
        'datasetDisplayName' => 'Historial ventas',
        'fields' => [
            ['name' => 'codCliente', 'type' => 'string'],
            ['name' => 'razonSocial', 'type' => 'string'],
            ['name' => 'nRemito', 'type' => 'string'],
            ['name' => 'tipo', 'type' => 'string'],
            ['name' => 'numero', 'type' => 'string'],
            ['name' => 'fechaEmision', 'type' => 'date'],
            ['name' => 'condVta', 'type' => 'number'],
            ['name' => 'porcDesc', 'type' => 'number'],
            ['name' => 'cotiz', 'type' => 'number'],
            ['name' => 'moneda', 'type' => 'string'],
            ['name' => 'totalComp', 'type' => 'number'],
            ['name' => 'codTransp', 'type' => 'string'],
            ['name' => 'nomTransp', 'type' => 'string'],
            ['name' => 'codArticulo', 'type' => 'string'],
            ['name' => 'descripcion', 'type' => 'string'],
            ['name' => 'codDep', 'type' => 'string'],
            ['name' => 'um', 'type' => 'string'],
            ['name' => 'cantidad', 'type' => 'number'],
            ['name' => 'precio', 'type' => 'number'],
            ['name' => 'totSinImp', 'type' => 'number'],
            ['name' => 'nCompRem', 'type' => 'string'],
            ['name' => 'cantRem', 'type' => 'number'],
            ['name' => 'fechaRem', 'type' => 'date'],
        ],
    ],
    'pw_consultastock' => [
        'datasetDisplayName' => 'Stock',
        'fields' => [
            ['name' => 'codArticulo', 'type' => 'string'],
            ['name' => 'descripcion', 'type' => 'string'],
            ['name' => 'stock', 'type' => 'number'],
            ['name' => 'comprometido', 'type' => 'number'],
            ['name' => 'comprometidoWeb', 'type' => 'number'],
            ['name' => 'disponibleNeto', 'type' => 'number'],
            ['name' => 'codBase', 'type' => 'string'],
            ['name' => 'stockBase', 'type' => 'number'],
            ['name' => 'comprometidoBase', 'type' => 'number'],
            ['name' => 'comprometidoBaseWeb', 'type' => 'number'],
            ['name' => 'disponibleNetoBase', 'type' => 'number'],
        ],
    ],
];

foreach ($schemas as $processCode => $meta) {
    $payload = [
        'version' => 1,
        'datasetDisplayName' => $meta['datasetDisplayName'],
        'fields' => $meta['fields'],
    ];
    $jsonPath = $outDir.'/'.$processCode.'.json';
    file_put_contents(
        $jsonPath,
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
    );

    $fieldNames = array_map(static fn (array $f): string => $f['name'], $meta['fields']);
    $portPath = $outDir.'/'.$processCode.'.port-names.json';
    file_put_contents(
        $portPath,
        json_encode(['fieldNames' => $fieldNames], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
    );

    echo "Wrote {$processCode}.json + .port-names.json\n";
}

echo 'Done ('.count($schemas)." schemas)\n";
