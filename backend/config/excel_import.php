<?php

return [

    'asyncMaxBytes' => (int) env('EXCEL_IMPORT_ASYNC_MAX_BYTES', 5 * 1024 * 1024),

    'asyncMaxEstimatedRows' => (int) env('EXCEL_IMPORT_ASYNC_MAX_ROWS', 2000),

    'defaultPageSize' => 50,

    'maxPageSize' => 200,

    'historialProcedimiento' => 'pw_historialimportexcel',

    'handlers' => [
        'Importacion.Articulos.AltaHandler' => \App\Services\ExcelImport\Handlers\NoOpArticulosAltaHandler::class,
        'Importacion.Pedidos.IndividualHandler' => \App\Services\ExcelImport\Handlers\PedidoIndividualExcelImportHandler::class,
        'Importacion.Pedidos.MasivoHandler' => \App\Services\ExcelImport\Handlers\PedidoMasivoExcelImportHandler::class,
    ],

];
