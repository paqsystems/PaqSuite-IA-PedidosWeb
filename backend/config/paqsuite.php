<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenancy mode (Framework unificado)
    |--------------------------------------------------------------------------
    | single = una empresa (MONO canónico)
    | multi  = N empresas
    | Ver PaqSuite-IA-FRAMEWORK docs/10-overrides-framework/
    */
    'tenancy' => env('PAQSUITE_TENANCY', 'single'),

    /*
    |--------------------------------------------------------------------------
    | Database topology
    |--------------------------------------------------------------------------
    | unified = Dictionary y Operativa en la misma conexión
    | split   = Dictionary y Company separados
    */
    'db' => env('PAQSUITE_DB', 'unified'),

    /*
    |--------------------------------------------------------------------------
    | HTTP headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'cliente' => env('PAQSUITE_HEADER_CLIENTE', 'X-Paq-Cliente'),
        'company' => env('PAQSUITE_HEADER_COMPANY', 'X-Company-Id'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database driver reference
    |--------------------------------------------------------------------------
    */
    'databaseDriverReference' => env('PAQSUITE_DB_DRIVER_REFERENCE', 'sqlsrv'),
];
