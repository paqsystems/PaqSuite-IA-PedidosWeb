<?php

declare(strict_types=1);

/**
 * Regenera database/manifest/pedidosweb-{semver}.json (checksums host).
 * Uso: php scripts/dev/generate-pedidosweb-host-manifest.php
 */

$backendRoot = dirname(__DIR__, 2);
$composer = json_decode((string) file_get_contents($backendRoot . '/composer.json'), true);
$semver = trim((string) ($composer['require']['paqsuite/laravel-core'] ?? ''));
if ($semver === '') {
    fwrite(STDERR, "No semver paqsuite/laravel-core in composer.json\n");
    exit(1);
}

$migrationDir = $backendRoot . '/database/migrations';
$files = array_values(array_filter(
    glob($migrationDir . '/*.php') ?: [],
    static function (string $absolute): bool {
        $base = basename($absolute);
        if (str_contains($base, 'pedidosweb')) {
            return true;
        }
        if (str_starts_with($base, '2026_11_04_1000') || str_starts_with($base, '2026_11_06_1000')) {
            return true;
        }
        if ($base === '2026_10_24_230000_create_jobs_table.php') {
            return true;
        }

        return false;
    }
));
sort($files);

/** @var array<string, list<string>> $dependsOnByBase */
$dependsOnByBase = [
    '2026_11_04_100000_align_pq_empresa_gen06_for_sp.php' => [
        'fw-migration-2026_10_30_000038_evolve_pq_empresa_unified_gen06',
    ],
    '2026_11_04_100003_align_pq_emission_jobs_for_framework_gen15.php' => [
        'pedidosweb-migration-2026_11_04_100002_create_pq_emission_tables',
    ],
    '2026_11_04_100004_backfill_pq_menus_label_key_columns.php' => [
        'fw-sp-pq_menus_add_label_key_nullable',
    ],
    '2026_11_04_100005_rebuild_pq_empresa_unified_gen06.php' => [
        'pedidosweb-migration-2026_11_04_100000_align_pq_empresa_gen06_for_sp',
    ],
    '2026_11_04_100015_widen_pq_installation_run_step_kind.php' => [
        'fw-migration-2026_10_27_000037_create_pq_installation_control_tables',
    ],
    '2026_11_06_100016_deploy_gen15_sdk_sql.php' => [
        'pedidosweb-migration-2026_11_04_100002_create_pq_emission_tables',
        'fw-sp-pq_sp_emission_core',
        'fw-sp-pq_sp_emission_designer_schema',
    ],
];

$entries = [];
foreach ($files as $absolute) {
    $base = basename($absolute);
    $relative = 'database/migrations/' . $base;
    $entries[] = [
        'id' => 'pedidosweb-migration-' . pathinfo($absolute, PATHINFO_FILENAME),
        'kind' => 'migration',
        'target' => 'dictionary',
        'path' => $relative,
        'checksum' => hash_file('sha256', $absolute),
        'dependsOn' => $dependsOnByBase[$base] ?? [],
    ];
}

// GEN-15 / CC#13 — Field List schemas (DIC) para el diseñador DX.
$designerSchemaDir = $backendRoot . '/database/emission-designer-schemas';
$designerSchemaFiles = is_dir($designerSchemaDir)
    ? (glob($designerSchemaDir . '/*.json') ?: [])
    : [];
sort($designerSchemaFiles);

foreach ($designerSchemaFiles as $absolute) {
    $basename = basename($absolute);
    if (str_ends_with($basename, '.port-names.json')) {
        continue;
    }

    $processCode = pathinfo($basename, PATHINFO_FILENAME);
    $relative = 'database/emission-designer-schemas/' . $basename;
    $portNamesRel = 'database/emission-designer-schemas/' . $processCode . '.port-names.json';
    $entry = [
        'id' => 'pedidosweb-emission-designer-schema-' . $processCode,
        'kind' => 'emissionDesignerSchema',
        'target' => 'dictionary',
        'path' => $relative,
        'checksum' => hash_file('sha256', $absolute),
        'dependsOn' => [
            'fw-migration-2026_11_03_000041_create_pq_emission_designer_schemas_table',
            'fw-sp-pq_sp_emission_designer_schema',
        ],
        'processCode' => $processCode,
    ];
    if (is_readable($backendRoot . '/' . $portNamesRel)) {
        $entry['emissionPortSchemaRef'] = $portNamesRel;
    }
    $entries[] = $entry;
}

usort($entries, static fn (array $a, array $b): int => strcmp($a['id'], $b['id']));

$manifest = [
    'manifestId' => 'pedidosweb-' . $semver,
    'semver' => $semver,
    'omitFrameworkEntryIds' => [
        'fw-sp-pq_sp_admin_empresa_tango_dictionary',
        'fw-sp-pq_sp_excel_core',
        'fw-sp-pq_sp_parametros_core',
        'fw-sp-pq_sp_pivot_layout_core',
        'fw-sp-pq_sp_pivots_consulta_get',
        'fw-sp-pq_sp_pivots_plantillas_core',
    ],
    'entries' => $entries,
];

$outDir = $backendRoot . '/database/manifest';
if (! is_dir($outDir) && ! mkdir($outDir, 0775, true) && ! is_dir($outDir)) {
    fwrite(STDERR, "Cannot create {$outDir}\n");
    exit(1);
}

$outFile = $outDir . '/pedidosweb-' . $semver . '.json';
file_put_contents(
    $outFile,
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
);

$designerCount = count(array_filter(
    $entries,
    static fn (array $e): bool => ($e['kind'] ?? '') === 'emissionDesignerSchema'
));
echo "Wrote {$outFile} (" . count($entries) . " entries, {$designerCount} designer schemas)\n";
