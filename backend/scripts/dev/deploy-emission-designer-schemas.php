<?php

declare(strict_types=1);

/**
 * Despliega emissionDesignerSchema del manifiesto host vía SP.
 * Útil tras installation:update vacío/idempotente o si falló el run (kind truncado).
 *
 * Uso (desde backend/): php scripts/dev/deploy-emission-designer-schemas.php
 */

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PaqSuite\LaravelCore\Emissions\Designer\EmissionDesignerSchemaCanonicalValidator;
use PaqSuite\LaravelCore\Emissions\Designer\EmissionDesignerSchemaRepository;

$backendRoot = dirname(__DIR__, 2);
$composer = json_decode((string) file_get_contents($backendRoot.'/composer.json'), true);
$semver = trim((string) ($composer['require']['paqsuite/laravel-core'] ?? ''));
if ($semver === '') {
    $semver = '1.3.18-beta.28';
}

$configured = (string) config('paqsuite.installation.hostManifestPath', '');
$manifestPath = $configured !== '' && is_readable($configured)
    ? $configured
    : $backendRoot.'/database/manifest/pedidosweb-'.$semver.'.json';

if (! is_readable($manifestPath)) {
    fwrite(STDERR, "Host manifest not readable: {$manifestPath}\n");
    exit(1);
}

$manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);

if (! Schema::hasTable('pq_emission_designer_schemas')) {
    fwrite(STDERR, "Missing pq_emission_designer_schemas — corré migration Framework 000041 primero.\n");
    exit(1);
}

$sp = DB::selectOne(
    "SELECT OBJECT_ID(N'dbo.pq_sp_emission_designer_schema_upsert', N'P') AS oid"
);
if ($sp === null || $sp->oid === null) {
    $sqlPath = $backendRoot.'/vendor/paqsuite/laravel-core/database/sp/pq_sp_emission_designer_schema.sql';
    $sql = (string) file_get_contents($sqlPath);
    DB::unprepared($sql);
    echo "Deployed pq_sp_emission_designer_schema_*\n";
}

$repo = new EmissionDesignerSchemaRepository();
$validator = new EmissionDesignerSchemaCanonicalValidator();
$connection = (string) config('database.default');
$count = 0;

foreach ($manifest['entries'] as $entry) {
    if (($entry['kind'] ?? '') !== 'emissionDesignerSchema') {
        continue;
    }
    $processCode = (string) ($entry['processCode'] ?? '');
    $path = $backendRoot.'/'.str_replace('\\', '/', (string) $entry['path']);
    $raw = (string) file_get_contents($path);
    $fileChecksum = hash('sha256', $raw);
    if (! hash_equals(strtolower((string) $entry['checksum']), $fileChecksum)) {
        fwrite(STDERR, "Checksum mismatch for {$processCode}\n");
        exit(1);
    }
    $canonical = $validator->validateJsonString($raw);
    $normalizedJson = json_encode($canonical, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    $repo->upsert($connection, $processCode, $canonical['version'], $normalizedJson, $fileChecksum);
    echo "upserted {$processCode}\n";
    $count++;
}

echo "Done ({$count})\n";
