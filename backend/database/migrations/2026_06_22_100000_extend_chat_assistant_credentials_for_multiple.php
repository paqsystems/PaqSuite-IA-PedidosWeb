<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permite múltiples configuraciones LLM por usuario (display_name + sin unique user_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_asistente_ia_credenciales')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            $this->upSqlServer();

            return;
        }

        $this->upGeneric();
    }

    private function upSqlServer(): void
    {
        if (! Schema::hasColumn('pq_asistente_ia_credenciales', 'display_name')) {
            DB::statement(<<<'SQL'
ALTER TABLE [pq_asistente_ia_credenciales]
    ADD [display_name] NVARCHAR(80) NOT NULL CONSTRAINT [DF_pq_asistente_ia_credenciales_display_name] DEFAULT ('');
SQL);
        }

        DB::statement(<<<'SQL'
IF EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'UX_pq_asistente_ia_credenciales_user_id'
      AND object_id = OBJECT_ID('pq_asistente_ia_credenciales')
)
    DROP INDEX [UX_pq_asistente_ia_credenciales_user_id]
    ON [pq_asistente_ia_credenciales];
SQL);

        DB::statement(<<<'SQL'
IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'IX_pq_asistente_ia_credenciales_user_id'
      AND object_id = OBJECT_ID('pq_asistente_ia_credenciales')
)
    CREATE INDEX [IX_pq_asistente_ia_credenciales_user_id]
        ON [pq_asistente_ia_credenciales] ([user_id]);
SQL);

        $this->backfillDisplayNames();
    }

    private function upGeneric(): void
    {
        Schema::table('pq_asistente_ia_credenciales', function (Blueprint $table): void {
            if (! Schema::hasColumn('pq_asistente_ia_credenciales', 'display_name')) {
                $table->string('display_name', 80)->default('');
            }
        });

        Schema::table('pq_asistente_ia_credenciales', function (Blueprint $table): void {
            $table->dropUnique(['user_id']);
            $table->index('user_id');
        });

        $this->backfillDisplayNames();
    }

    private function backfillDisplayNames(): void
    {
        $credentials = DB::table('pq_asistente_ia_credenciales')
            ->select(['id_credencial', 'provider_id', 'model_id', 'display_name'])
            ->get();

        foreach ($credentials as $credential) {
            $displayName = trim((string) ($credential->display_name ?? ''));

            if ($displayName !== '') {
                continue;
            }

            DB::table('pq_asistente_ia_credenciales')
                ->where('id_credencial', $credential->id_credencial)
                ->update([
                    'display_name' => trim((string) $credential->provider_id).' / '.trim((string) $credential->model_id),
                ]);
        }
    }

    public function down(): void
    {
        // No revertir: podría haber filas duplicadas por user_id.
    }
};
