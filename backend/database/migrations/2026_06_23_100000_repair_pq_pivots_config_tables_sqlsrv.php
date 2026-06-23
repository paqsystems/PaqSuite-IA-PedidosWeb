<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reparación idempotente para entornos SQL Server donde la migración pivots
 * quedó a medias o pq_pivots_config existía con esquema incompatible.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        if (! Schema::hasTable('pq_pivots_config')) {
            return;
        }

        $this->repairPqPivotsConfigPrimaryKeyColumn();
        $this->ensurePqPivotsConfigPrimaryKeyConstraint();
        $this->ensurePqPivotsConfigLastUsedPivotForeignKey();
    }

    private function repairPqPivotsConfigPrimaryKeyColumn(): void
    {
        if (Schema::hasColumn('pq_pivots_config', 'pivot_id')) {
            return;
        }

        if (Schema::hasColumn('pq_pivots_config', 'id')) {
            DB::statement("EXEC sp_rename N'pq_pivots_config.id', N'pivot_id', 'COLUMN'");

            return;
        }

        throw new RuntimeException(
            'pq_pivots_config existe en SQL Server sin columnas pivot_id/id reconocibles. '
            .'Revise el esquema manualmente antes de continuar migrate.',
        );
    }

    private function ensurePqPivotsConfigPrimaryKeyConstraint(): void
    {
        $hasPrimaryKeyOnPivotId = DB::scalar(<<<'SQL'
SELECT CASE WHEN EXISTS (
    SELECT 1
    FROM sys.key_constraints AS kc
    INNER JOIN sys.index_columns AS ic
        ON kc.parent_object_id = ic.object_id
       AND kc.unique_index_id = ic.index_id
    INNER JOIN sys.columns AS col
        ON ic.object_id = col.object_id
       AND ic.column_id = col.column_id
    WHERE kc.parent_object_id = OBJECT_ID(N'pq_pivots_config')
      AND kc.type = 'PK'
      AND col.name = 'pivot_id'
) THEN 1 ELSE 0 END
SQL);

        if ((int) $hasPrimaryKeyOnPivotId === 1) {
            return;
        }

        DB::statement(<<<'SQL'
ALTER TABLE [pq_pivots_config]
    ADD CONSTRAINT [PK_pq_pivots_config] PRIMARY KEY ([pivot_id]);
SQL);
    }

    private function ensurePqPivotsConfigLastUsedPivotForeignKey(): void
    {
        if (! Schema::hasTable('pq_pivots_config_last_used')) {
            return;
        }

        $exists = DB::scalar(<<<'SQL'
SELECT CASE WHEN EXISTS (
    SELECT 1
    FROM sys.foreign_keys
    WHERE name = 'FK_pq_pivots_config_last_used_pivot'
      AND parent_object_id = OBJECT_ID(N'pq_pivots_config_last_used')
) THEN 1 ELSE 0 END
SQL);

        if ((int) $exists === 1) {
            return;
        }

        DB::statement(<<<'SQL'
ALTER TABLE [pq_pivots_config_last_used]
    ADD CONSTRAINT [FK_pq_pivots_config_last_used_pivot]
    FOREIGN KEY ([pivot_id]) REFERENCES [pq_pivots_config]([pivot_id])
    ON DELETE SET NULL;
SQL);
    }

    public function down(): void
    {
        // Reparación idempotente; no revertir en deploy.
    }
};
