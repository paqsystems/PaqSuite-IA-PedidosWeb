<?php

$pedidosWebComposerPath = dirname(__DIR__) . '/composer.json';
$pedidosWebComposer = is_readable($pedidosWebComposerPath)
    ? json_decode((string) file_get_contents($pedidosWebComposerPath), true)
    : null;
$pedidosWebLaravelCoreSemver = is_array($pedidosWebComposer)
    ? trim((string) ($pedidosWebComposer['require']['paqsuite/laravel-core'] ?? ''))
    : '';
if ($pedidosWebLaravelCoreSemver === '') {
    $pedidosWebLaravelCoreSemver = '1.3.18-beta.28';
}
$pedidosWebDefaultHostManifest = dirname(__DIR__)
    . '/database/manifest/pedidosweb-' . $pedidosWebLaravelCoreSemver . '.json';

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

    'proyecto' => env('PAQSUITE_PROYECTO', 'pedidosweb'),

    /*
    | GEN-34 — lookup instalación en CLI (installation:update / pedidosweb:tasks-run-due)
    */
    'instalacion' => [
        'resolver' => env('PAQSUITE_INSTALACION_RESOLVER', 'config'),
        'centralConnection' => env('PAQSUITE_CENTRAL_CONNECTION', 'paqsuite_central'),
        'procedure' => env('PAQSUITE_EMPRESAS_CONEXION_PROCEDURE', 'pq_sp_empresas_conexion_get'),
        'cacheTtlSeconds' => (int) env('PAQSUITE_INSTALACION_CACHE_TTL', 60),
        'applyDatabaseMiddleware' => env(
            'PAQSUITE_APPLY_INSTALACION_DB_MIDDLEWARE',
            \PaqSuite\LaravelCore\Http\Middleware\ApplyInstalacionDatabaseMiddleware::class,
        ),
    ],

    'instalaciones' => [
        'DESARROLLO|pedidosweb' => [
            'activo' => true,
            'connection' => env('DB_CONNECTION', 'sqlsrv'),
            'singleCompanyId' => (int) env('PAQSUITE_MONO_EMPRESA_ID', 8),
        ],
        'QUENTO|pedidosweb' => [
            'activo' => true,
            'connection' => env('DB_CONNECTION', 'sqlsrv'),
            'singleCompanyId' => (int) env('PAQSUITE_MONO_EMPRESA_ID', 8),
        ],
        'DEMO|pedidosweb' => [
            'activo' => true,
            'connection' => env('DB_CONNECTION', 'sqlsrv'),
            'singleCompanyId' => (int) env('PAQSUITE_MONO_EMPRESA_ID', 8),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | GEN-13 task routes
    |--------------------------------------------------------------------------
    */
    'tasks' => [
        'routesEnabled' => filter_var(env('PAQSUITE_TASKS_ROUTES_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'middleware' => ['paq.tenant', 'auth:sanctum'],
        'scheduleTimezone' => env('PAQSUITE_TASKS_SCHEDULE_TIMEZONE', 'America/Argentina/Buenos_Aires'),
        'backup' => [
            'backend' => strtolower(trim((string) env('PAQSUITE_TASKS_BACKUP_BACKEND', 'rds'))),
            'rdsGateway' => strtolower(trim((string) env('PAQSUITE_TASKS_BACKUP_RDS_GATEWAY', 'rds'))),
            'windowsGateway' => strtolower(trim((string) env('PAQSUITE_TASKS_BACKUP_WINDOWS_GATEWAY', 'sqlserver'))),
            's3ArnPrefix' => env('TASKS_BACKUP_S3_ARN_PREFIX', 'arn:aws:s3:::paqsuite-pedidosweb-backup/'),
            'windowsDefaultFolder' => env('TASKS_BACKUP_WINDOWS_DEFAULT_FOLDER', ''),
            'windowsService' => env('TASKS_BACKUP_WINDOWS_SERVICE', ''),
            'pollSeconds' => (int) env('TASKS_BACKUP_POLL_SECONDS', 5),
            'lockLeaseSeconds' => (int) env('TASKS_BACKUP_LOCK_LEASE_SECONDS', 900),
            'rdsTimeoutMinutes' => (int) env('TASKS_BACKUP_RDS_TIMEOUT_MINUTES', 120),
            'rdsPollingIntervalSeconds' => (int) env('TASKS_BACKUP_RDS_POLLING_INTERVAL_SECONDS', 30),
            'deferManualRdsPolling' => filter_var(
                env('PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING', false),
                FILTER_VALIDATE_BOOLEAN,
            ),
        ],
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

    /*
    | GEN-15 — emisiones documentales (Field List diseñador vía DIC + field-schema)
    */
    'installation' => [
        'hostManifestPath' => env(
            'PAQSUITE_HOST_MANIFEST',
            $pedidosWebDefaultHostManifest
        ),
        'frameworkPackageRoot' => base_path('vendor/paqsuite/laravel-core'),
        'allowSqlSyncDev' => filter_var(env('PAQSUITE_ALLOW_SQL_SYNC_DEV', true), FILTER_VALIDATE_BOOLEAN),
    ],

    'emissions' => [
        // Obligatorio anteponer tenant + resolución Sanctum opcional.
        // Sin ResolveSanctumUserOptionally, GET field-schema deja user()=null aunque
        // el FE envíe Bearer → emission.error.3001 (AUTH_UNAUTHENTICATED).
        // No usar auth:sanctum estricto: el sidecar autentica con X-Paq-Sidecar-Schema-Token.
        'designerSchemaMiddleware' => [
            'paq.tenant',
            'paq.emissionDesignerSanctum',
        ],
        'dxReporting' => [
            'mode' => env('PAQSUITE_EMISSION_DX_MODE', 'fake'),
            'serviceUrl' => env(
                'PAQSUITE_EMISSION_DX_SERVICE_URL',
                env('PAQSUITE_DX_REPORTING_SIDECAR_URL', 'http://127.0.0.1:5055').'/api/v1/dx-reporting',
            ),
            'apiKey' => env('PAQSUITE_EMISSION_DX_API_KEY', ''),
            'timeoutSeconds' => (int) env('PAQSUITE_EMISSION_DX_TIMEOUT', 60),
        ],
    ],
];
