<?php

use App\Services\PedidosWeb\Integracion\Transacciones\Handlers\TransaccionJsonHandler;

return [
    'resources' => [
        'stock' => [
            'handler' => TransaccionJsonHandler::class,
            'resource' => 'stock',
            'apply' => 'pq_sp_integracion_transacciones_stock_apply',
            'finalize' => 'pq_sp_integracion_transacciones_stock_finalize',
            'key' => ['codArticulo'],
            'required' => ['codArticulo', 'stock', 'comprometido'],
            'supports_snapshot' => true,
        ],
        'deuda' => [
            'handler' => TransaccionJsonHandler::class,
            'resource' => 'deuda',
            'apply' => 'pq_sp_integracion_transacciones_deuda_apply',
            'finalize' => 'pq_sp_integracion_transacciones_deuda_finalize',
            'key' => ['codCliente', 'tComp', 'nComp', 'fechaVto'],
            'required' => ['codCliente', 'tComp', 'nComp', 'fechaEmis', 'fechaVto', 'saldo'],
            'supports_snapshot' => true,
        ],
        'cheques' => [
            'handler' => TransaccionJsonHandler::class,
            'resource' => 'cheques',
            'apply' => 'pq_sp_integracion_transacciones_cheques_apply',
            'finalize' => 'pq_sp_integracion_transacciones_cheques_finalize',
            'key' => ['interno', 'numero'],
            'required' => ['interno', 'numero', 'codCliente', 'banco', 'fecha', 'importe', 'origen', 'estado'],
            'supports_snapshot' => true,
        ],
        'historial-ventas' => [
            'handler' => TransaccionJsonHandler::class,
            'resource' => 'historial-ventas',
            'apply' => 'pq_sp_integracion_transacciones_historial_ventas_apply',
            'key' => ['idGva53'],
            'required' => ['idGva53', 'codCliente', 'tipo', 'numero', 'fechaEmision'],
            'supports_snapshot' => false,
        ],
    ],
    'max_items_per_lote' => 1500,
    'max_errores_respuesta' => 50,
];
