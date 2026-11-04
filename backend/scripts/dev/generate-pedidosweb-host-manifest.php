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
        if (str_starts_with($base, '2026_11_04_1000')) {
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

echo "Wrote {$outFile} (" . count($entries) . " migrations)\n";
