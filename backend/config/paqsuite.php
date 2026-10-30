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
    | GEN-13 task routes
    |--------------------------------------------------------------------------
    | PedidosWeb todavía no adopta el catálogo de tareas programadas.
    */
    'tasks' => [
        'routesEnabled' => env('PAQSUITE_TASKS_ROUTES_ENABLED', false),
    ],

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

    'supported_locales' => ['es', 'en', 'pt', 'fr', 'it'],

    /*
    | GEN-07 — menú i18n: catálogo en JSON plano (claves menu.* en frontend/src/locales).
    */
    'menuI18n' => [
        'enabled' => filter_var(env('PAQSUITE_MENU_I18N_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'installGate' => filter_var(env('PAQSUITE_MENU_I18N_INSTALL_GATE', false), FILTER_VALIDATE_BOOLEAN),
        'driver' => env('PAQSUITE_MENU_I18N_DRIVER', 'jsonLocales'),
        'repoRoot' => env('PAQSUITE_MENU_I18N_REPO_ROOT', dirname(base_path())),
        'productI18nTsPath' => env(
            'PAQSUITE_MENU_I18N_TS_PATH',
            'frontend/src/features/auth/productI18n.ts'
        ),
        'jsonLocales' => [
            'es' => 'frontend/src/locales/es.json',
            'en' => 'frontend/src/locales/en.json',
            'pt' => 'frontend/src/locales/pt.json',
            'fr' => 'frontend/src/locales/fr.json',
            'it' => 'frontend/src/locales/it.json',
        ],
    ],
];
