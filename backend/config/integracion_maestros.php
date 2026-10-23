<?php

use App\Services\PedidosWeb\Integracion\Maestros\Handlers\CatalogoMaestroHandler;
use App\Services\PedidosWeb\Integracion\Maestros\Handlers\ClientesMaestroHandler;
use App\Services\PedidosWeb\Integracion\Maestros\Handlers\ClientesRelacionMaestroHandler;
use App\Services\PedidosWeb\Integracion\Maestros\Handlers\ParametrosMaestroHandler;

return [

    /*
    | Recursos POST /api/v1/integracion/maestros/{recurso}
    | TR-22b/22c reemplazan stubs por handlers + SP.
    */
    'resources' => [
        'provincias' => [
            'handler' => CatalogoMaestroHandler::class,
            'resource' => 'provincias',
            'apply' => 'pq_sp_integracion_maestros_provincias_apply',
            'finalize' => 'pq_sp_integracion_maestros_provincias_finalize_snapshot',
            'key' => ['codProvin'],
            'supports_snapshot' => true,
        ],
        'condventa' => [
            'handler' => CatalogoMaestroHandler::class,
            'resource' => 'condventa',
            'apply' => 'pq_sp_integracion_maestros_condventa_apply',
            'finalize' => 'pq_sp_integracion_maestros_condventa_finalize_snapshot',
            'key' => ['codigo'],
            'supports_snapshot' => true,
        ],
        'transportes' => [
            'handler' => CatalogoMaestroHandler::class,
            'resource' => 'transportes',
            'apply' => 'pq_sp_integracion_maestros_transportes_apply',
            'finalize' => 'pq_sp_integracion_maestros_transportes_finalize_snapshot',
            'key' => ['codigo'],
            'supports_snapshot' => true,
        ],
        'perfiles' => [
            'handler' => CatalogoMaestroHandler::class,
            'resource' => 'perfiles',
            'apply' => 'pq_sp_integracion_maestros_perfiles_apply',
            'finalize' => 'pq_sp_integracion_maestros_perfiles_finalize_snapshot',
            'key' => ['codPerfil'],
            'supports_snapshot' => true,
        ],
        'vendedores' => [
            'handler' => CatalogoMaestroHandler::class,
            'resource' => 'vendedores',
            'apply' => 'pq_sp_integracion_maestros_vendedores_apply',
            'key' => ['codVended'],
            'supports_snapshot' => true,
        ],
        'listaprecios' => [
            'handler' => CatalogoMaestroHandler::class,
            'resource' => 'listaprecios',
            'apply' => 'pq_sp_integracion_maestros_listaprecios_apply',
            'finalize' => 'pq_sp_integracion_maestros_listaprecios_finalize_snapshot',
            'key' => ['codLista'],
            'supports_snapshot' => true,
        ],
        'escalas' => [
            'handler' => CatalogoMaestroHandler::class,
            'resource' => 'escalas',
            'apply' => 'pq_sp_integracion_maestros_escalas_apply',
            'finalize' => 'pq_sp_integracion_maestros_escalas_finalize_snapshot',
            'key' => ['codEscala'],
            'supports_snapshot' => true,
        ],
        'articulos' => [
            'handler' => CatalogoMaestroHandler::class,
            'resource' => 'articulos',
            'apply' => 'pq_sp_integracion_maestros_articulos_apply',
            'key' => ['codigo'],
            'supports_snapshot' => true,
        ],
        'listaprecios-articulos' => [
            'handler' => CatalogoMaestroHandler::class,
            'resource' => 'listaprecios-articulos',
            'apply' => 'pq_sp_integracion_maestros_listaprecios_articulos_apply',
            'key' => ['codLista', 'codArticulo'],
            'supports_snapshot' => false,
        ],
        'descuentocantidad' => [
            'handler' => CatalogoMaestroHandler::class,
            'resource' => 'descuentocantidad',
            'apply' => 'pq_sp_integracion_maestros_descuentocantidad_apply',
            'key' => ['codArticu', 'cantidad'],
            'supports_snapshot' => false,
        ],
        'clientes' => [
            'handler' => ClientesMaestroHandler::class,
            'resource' => 'clientes',
            'apply' => 'pq_sp_integracion_maestros_clientes_apply',
            'key' => ['codClient'],
            'supports_snapshot' => true,
        ],
        'clientes-direcciones' => [
            'handler' => ClientesRelacionMaestroHandler::class,
            'resource' => 'clientes-direcciones',
            'apply' => 'pq_sp_integracion_maestros_clientes_direcciones_apply',
            'key' => ['codClient', 'idDe'],
            'supports_snapshot' => false,
        ],
        'clientes-contactos' => [
            'handler' => ClientesRelacionMaestroHandler::class,
            'resource' => 'clientes-contactos',
            'apply' => 'pq_sp_integracion_maestros_clientes_contactos_apply',
            'key' => ['codClient', 'codContacto'],
            'supports_snapshot' => false,
        ],
        'parametros' => [
            'handler' => ParametrosMaestroHandler::class,
            'resource' => 'parametros',
            'apply' => 'pq_sp_integracion_maestros_parametros_apply',
            'key' => ['clave'],
            'supports_snapshot' => false,
        ],
    ],

    'max_items_per_lote' => 1500,

    'max_errores_respuesta' => 50,

];
