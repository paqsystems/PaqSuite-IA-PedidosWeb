<?php

declare(strict_types=1);

namespace App\Support\Installation;

use Database\Seeders\PedidosWebEmissionPilotSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PaqSuite\LaravelCore\Emissions\Designer\EmissionDesignerSchemaCanonicalValidator;
use PaqSuite\LaravelCore\Emissions\Designer\EmissionDesignerSchemaRepository;
use Throwable;

/**
 * Puente host GEN-15 mientras PedidosWeb no corre GEN-34 completo en Forge.
 * Solo CREATE/ALTER PROCEDURE + CREATE TABLE IF + seed/upsert (sin DROP/TRUNCATE).
 */
final class PedidosWebSdkSqlDeployer
{
    /** @var list<string> */
    private const EMISSION_SQL_FILES = [
        'database/sp/pq_sp_emission_core.sql',
        'database/sp/pq_sp_emission_designer_schema.sql',
    ];

    /**
     * @return array{
     *     skipped: bool,
     *     reason?: string,
     *     sqlFiles: list<string>,
     *     designerSchemas: int,
     *     catalogSeeded: bool
     * }
     */
    public function deployEmission(): array
    {
        $packageRoot = $this->laravelCoreRoot();
        if ($packageRoot === null) {
            return [
                'skipped' => true,
                'reason' => 'vendor/paqsuite/laravel-core no está instalado',
                'sqlFiles' => [],
                'designerSchemas' => 0,
                'catalogSeeded' => false,
            ];
        }

        $this->ensureDesignerSchemasTable();

        $applied = [];
        foreach (self::EMISSION_SQL_FILES as $relative) {
            $absolute = $packageRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if (! is_readable($absolute)) {
                return [
                    'skipped' => true,
                    'reason' => "Falta script SDK {$relative}",
                    'sqlFiles' => $applied,
                    'designerSchemas' => 0,
                    'catalogSeeded' => false,
                ];
            }
            $this->applySqlFile($absolute);
            $applied[] = basename($absolute);
        }

        $catalogSeeded = false;
        if (Schema::hasTable('pq_emission_processes') && Schema::hasTable('pq_emission_reports')) {
            (new PedidosWebEmissionPilotSeeder())->run();
            $catalogSeeded = true;
        }

        $designerSchemas = 0;
        try {
            $designerSchemas = $this->upsertDesignerSchemas();
        } catch (Throwable $exception) {
            if (! $this->isMissingDesignerSchemaSupport($exception)) {
                throw $exception;
            }
        }

        return [
            'skipped' => false,
            'sqlFiles' => $applied,
            'designerSchemas' => $designerSchemas,
            'catalogSeeded' => $catalogSeeded,
        ];
    }

    public static function splitGoBatches(string $sql): array
    {
        $batches = preg_split('/^\s*GO\s*$/mi', $sql) ?: [];

        return array_values(array_filter(array_map('trim', $batches), static fn (string $batch): bool => $batch !== ''));
    }

    public static function isMissingEmissionSql(Throwable $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'pq_sp_emission_')
            || str_contains($message, "Invalid object name 'pq_emission_")
            || str_contains($message, "Invalid object name 'PQ_EMISSION_");
    }

    private function laravelCoreRoot(): ?string
    {
        $configured = (string) config('paqsuite.installation.frameworkPackageRoot', '');
        $candidates = array_filter([
            $configured !== '' ? $configured : null,
            base_path('vendor/paqsuite/laravel-core'),
        ]);
        foreach ($candidates as $candidate) {
            if (is_dir($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function applySqlFile(string $absolute): void
    {
        $sql = (string) file_get_contents($absolute);
        $driver = Schema::getConnection()->getDriverName();
        $batches = $driver === 'sqlsrv' ? self::splitGoBatches($sql) : [trim($sql)];

        foreach ($batches as $batch) {
            if ($batch === '') {
                continue;
            }
            DB::unprepared($batch);
        }
    }

    private function ensureDesignerSchemasTable(): void
    {
        if (Schema::hasTable('pq_emission_designer_schemas')) {
            return;
        }

        Schema::create('pq_emission_designer_schemas', function (Blueprint $table): void {
            $table->string('process_code', 128)->primary();
            $table->unsignedInteger('schema_version')->default(1);
            $table->text('schema_json');
            $table->char('checksum_sha256', 64);
            $table->timestamp('updated_at')->useCurrent();
            $table->index(['updated_at'], 'pq_emission_designer_schemas_updated_idx');
        });
    }

    private function upsertDesignerSchemas(): int
    {
        $manifestPath = (string) config('paqsuite.installation.hostManifestPath', '');
        if ($manifestPath === '' || ! is_readable($manifestPath)) {
            return 0;
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($manifest) || ! is_array($manifest['entries'] ?? null)) {
            return 0;
        }

        $repo = new EmissionDesignerSchemaRepository();
        $validator = new EmissionDesignerSchemaCanonicalValidator();
        $connection = (string) config('database.default');
        $hostRoot = base_path();
        $count = 0;

        foreach ($manifest['entries'] as $entry) {
            if (! is_array($entry) || ($entry['kind'] ?? '') !== 'emissionDesignerSchema') {
                continue;
            }
            $processCode = trim((string) ($entry['processCode'] ?? ''));
            $relative = str_replace('\\', '/', (string) ($entry['path'] ?? ''));
            $absolute = $hostRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if ($processCode === '' || ! is_readable($absolute)) {
                continue;
            }
            $raw = (string) file_get_contents($absolute);
            $fileChecksum = hash('sha256', $raw);
            $expected = strtolower((string) ($entry['checksum'] ?? ''));
            if ($expected !== '' && ! hash_equals($expected, $fileChecksum)) {
                continue;
            }
            $canonical = $validator->validateJsonString($raw);
            $normalizedJson = json_encode($canonical, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $repo->upsert($connection, $processCode, $canonical['version'], $normalizedJson, $fileChecksum);
            $count++;
        }

        return $count;
    }

    private function isMissingDesignerSchemaSupport(Throwable $exception): bool
    {
        return self::isMissingEmissionSql($exception)
            || str_contains($exception->getMessage(), 'pq_emission_designer_schemas');
    }
}
