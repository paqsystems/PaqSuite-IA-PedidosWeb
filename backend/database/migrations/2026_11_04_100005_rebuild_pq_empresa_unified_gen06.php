<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sustituye pq_empresa legacy (IDEmpresa + columnas calculadas) por tabla GEN-06 real.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        if (Schema::hasTable('pq_empresa') && $this->hasPersistedIdColumn('pq_empresa')) {
            return;
        }

        $legacyName = 'PQ_Empresa_legacy_gen06';
        if (Schema::hasTable('pq_empresa') || Schema::hasTable('PQ_Empresa')) {
            if (! Schema::hasTable($legacyName)) {
                $source = Schema::hasTable('PQ_Empresa') ? 'PQ_Empresa' : 'pq_empresa';
                DB::statement("EXEC sp_rename N'dbo.{$source}', N'{$legacyName}';");
            }
        }

        if (! Schema::hasTable('pq_empresa')) {
            DB::statement(<<<'SQL'
CREATE TABLE dbo.pq_empresa (
    id INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    nombre NVARCHAR(255) NOT NULL,
    activo BIT NOT NULL CONSTRAINT DF_pq_empresa_activo DEFAULT (1),
    theme NVARCHAR(64) NULL,
    erp_empresa_id BIGINT NULL,
    nombre_bd NVARCHAR(128) NULL,
    created_at DATETIME2 NULL,
    updated_at DATETIME2 NULL
)
SQL);
        }

        if (! Schema::hasTable($legacyName)) {
            return;
        }

        DB::unprepared(<<<'SQL'
SET IDENTITY_INSERT dbo.pq_empresa ON;
INSERT INTO dbo.pq_empresa (id, nombre, activo, theme, erp_empresa_id, nombre_bd, created_at, updated_at)
SELECT
    e.IDEmpresa,
    e.NombreEmpresa,
    CAST(CASE WHEN e.Habilita = 1 THEN 1 ELSE 0 END AS BIT),
    e.theme,
    e.erp_empresa_id,
    COALESCE(e.nombre_bd, e.NombreBD),
    SYSUTCDATETIME(),
    SYSUTCDATETIME()
FROM dbo.PQ_Empresa_legacy_gen06 e WITH (NOLOCK)
WHERE NOT EXISTS (SELECT 1 FROM dbo.pq_empresa p WITH (NOLOCK) WHERE p.id = e.IDEmpresa);
SET IDENTITY_INSERT dbo.pq_empresa OFF;
SQL);
    }

    private function hasPersistedIdColumn(string $table): bool
    {
        $rows = DB::select(
            "SELECT c.name, cc.definition
             FROM sys.columns c
             LEFT JOIN sys.computed_columns cc ON cc.object_id = c.object_id AND cc.column_id = c.column_id
             WHERE c.object_id = OBJECT_ID(N'dbo.{$table}') AND c.name = N'id'"
        );

        if ($rows === []) {
            return false;
        }

        return ($rows[0]->definition ?? null) === null;
    }

    public function down(): void
    {
        // No revertir rebuild en demo.
    }
};
