<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$frameworkRoot = null;
foreach ($argv as $index => $argument) {
    if ($argument === '--framework-root' && isset($argv[$index + 1])) {
        $frameworkRoot = $argv[$index + 1];
    }
}

$frameworkRoot ??= dirname(__DIR__, 3).'/PaqSuite-IA-FRAMEWORK';
$storedProceduresPath = $frameworkRoot.'/packages/php/laravel-core/database/sp/pq_sp_importation_core.sql';

if (! is_file($storedProceduresPath)) {
    fwrite(STDERR, "No se encontró el SQL de GEN-36: {$storedProceduresPath}\n");
    exit(1);
}

$tables = [
    'pq_gen_importations' => static function (Blueprint $table): void {
        $table->string('importation_id', 128);
        $table->string('resource', 64);
        $table->string('tenant_id', 128)->default('');
        $table->string('status', 20)->default('received');
        $table->unsignedInteger('processed')->nullable();
        $table->unsignedInteger('added')->nullable();
        $table->unsignedInteger('updated')->nullable();
        $table->unsignedInteger('deleted')->nullable();
        $table->unsignedInteger('added_errors')->nullable();
        $table->unsignedInteger('updated_errors')->nullable();
        $table->unsignedInteger('deleted_errors')->nullable();
        $table->text('errors_json')->nullable();
        $table->dateTime('started_at')->nullable();
        $table->dateTime('finished_at')->nullable();
        $table->timestamps();
        $table->primary(['importation_id', 'resource', 'tenant_id'], 'pq_gen_importations_pk');
        $table->unique(['importation_id', 'tenant_id'], 'pq_gen_importations_importation_tenant_uidx');
        $table->index(['tenant_id', 'resource', 'status'], 'pq_gen_importations_active_idx');
    },
    'pq_gen_importation_batches' => static function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->string('importation_id', 128);
        $table->string('resource', 64);
        $table->string('tenant_id', 128)->default('');
        $table->unsignedInteger('batch_number');
        $table->boolean('is_last_batch')->default(false);
        $table->string('mode', 16);
        $table->dateTime('process_date');
        $table->text('payload_json');
        $table->unsignedBigInteger('payload_bytes');
        $table->char('payload_hash', 64);
        $table->dateTime('received_at');
        $table->unique(
            ['importation_id', 'resource', 'tenant_id', 'batch_number'],
            'pq_gen_importation_batches_uidx',
        );
        $table->index(['resource', 'tenant_id', 'payload_hash'], 'pq_gen_importation_batches_hash_idx');
    },
    'pq_gen_importation_errors' => static function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->string('importation_id', 128);
        $table->string('resource', 64);
        $table->unsignedInteger('batch_number')->nullable();
        $table->unsignedInteger('item_index')->nullable();
        $table->text('error_json');
        $table->timestamps();
        $table->index(['importation_id', 'resource'], 'pq_gen_importation_errors_lookup_idx');
    },
    'pq_gen_importation_locks' => static function (Blueprint $table): void {
        $table->string('resource', 64);
        $table->string('tenant_id', 128)->default('');
        $table->string('importation_id', 128);
        $table->dateTime('acquired_at');
        $table->unique(['resource', 'tenant_id'], 'pq_gen_importation_locks_uidx');
    },
];

foreach ($tables as $tableName => $definition) {
    if (! Schema::hasTable($tableName)) {
        Schema::create($tableName, $definition);
        echo "created table {$tableName}\n";
    } else {
        echo "table {$tableName} already exists\n";
    }
}

if (Schema::hasTable('pq_gen_importations')) {
    $uniqueIndexExists = DB::selectOne(
        "SELECT 1 AS present
         FROM sys.indexes
         WHERE object_id = OBJECT_ID('pq_gen_importations')
           AND name = 'pq_gen_importations_importation_tenant_uidx'",
    );
    if ($uniqueIndexExists === null) {
        Schema::table('pq_gen_importations', static function (Blueprint $table): void {
            $table->unique(
                ['importation_id', 'tenant_id'],
                'pq_gen_importations_importation_tenant_uidx',
            );
        });
        echo "added unique importation_id/tenant_id index\n";
    }
}

if (! Schema::hasColumn('pq_gen_importations', 'received_at')) {
    Schema::table('pq_gen_importations', static function (Blueprint $table): void {
        $table->dateTime('received_at')->nullable();
    });
    echo "added column pq_gen_importations.received_at\n";
}

$sql = (string) file_get_contents($storedProceduresPath);
$batches = preg_split('/^\s*GO\s*$/mi', $sql) ?: [];
$procedureCount = 0;
foreach ($batches as $batch) {
    $batch = trim($batch);
    if ($batch === '') {
        continue;
    }
    DB::unprepared($batch);
    $procedureCount++;
}

echo "applied {$procedureCount} GEN-36 procedure batches\n";
