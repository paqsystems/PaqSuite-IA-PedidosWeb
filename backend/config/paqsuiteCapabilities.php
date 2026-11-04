<?php

return [
    'emissionEnabled' => filter_var(env('PAQSUITE_EMISSION_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'parameters' => [
        'Emission' => [
            'EmissionEnabled' => env('PAQSUITE_EMISSION_ENABLED', true) ? 'S' : 'N',
            'EmissionAsyncMaxMB' => 5,
            'EmissionAsyncMaxRows' => 2000,
            'EmissionArtifactRetentionDays' => 30,
        ],
    ],

    'emissions' => [
        'previewTtlMinutes' => 15,
        'dxReporting' => [
            'mode' => env('PAQSUITE_EMISSION_DX_MODE', 'fake'),
        ],
        /**
         * Procesos emisibles enlazados a informes PedidosWeb (CC #19).
         * Tras installation:update pueden existir filas adicionales en catálogo GEN-15.
         */
        'catalogProcessCodes' => [
            'pw_presupuestosingresados',
            'pw_pedidosingresados',
            'pw_pedidospendientes',
            'pw_detallepedidos',
            'pw_deudaclientes',
            'pw_consultacheques',
            'pw_historialventas',
            'pw_consultastock',
        ],
    ],
];
