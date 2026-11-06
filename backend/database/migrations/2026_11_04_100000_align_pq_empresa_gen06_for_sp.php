<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-06 / GEN-34: pq_sp_admin_empresa_* exige columnas id, nombre, activo en pq_empresa.
 * BD demo PedidosWeb puede tener solo esquema legacy (IDEmpresa, NombreEmpresa, Habilita).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        if (! Schema::hasTable('pq_empresa') && ! Schema::hasTable('PQ_Empresa')) {
            return;
        }

        $table = Schema::hasTable('pq_empresa') ? 'pq_empresa' : 'PQ_Empresa';

        if (! Schema::hasColumn($table, 'IDEmpresa')) {
            return;
        }

        if (! Schema::hasColumn($table, 'id')) {
            DB::statement(
                "IF COL_LENGTH(N'dbo.{$table}', N'id') IS NULL
                 BEGIN
                     ALTER TABLE dbo.[{$table}]
                     ADD id AS IDEmpresa PERSISTED;
                 END"
            );
        }

        if (! Schema::hasColumn($table, 'nombre')) {
            DB::statement(
                "IF COL_LENGTH(N'dbo.{$table}', N'nombre') IS NULL
                 BEGIN
                     ALTER TABLE dbo.[{$table}]
                     ADD nombre AS NombreEmpresa PERSISTED;
                 END"
            );
        }

        if (! Schema::hasColumn($table, 'activo')) {
            DB::statement(
                "IF COL_LENGTH(N'dbo.{$table}', N'activo') IS NULL
                 BEGIN
                     ALTER TABLE dbo.[{$table}]
                     ADD activo AS CAST(CASE WHEN Habilita = 1 THEN 1 ELSE 0 END AS BIT) PERSISTED;
                 END"
            );
        }

        if (! Schema::hasColumn($table, 'erp_empresa_id')) {
            DB::statement(
                "IF COL_LENGTH(N'dbo.{$table}', N'erp_empresa_id') IS NULL
                 BEGIN
                     ALTER TABLE dbo.[{$table}] ADD erp_empresa_id BIGINT NULL;
                 END"
            );
        }

        if (! Schema::hasColumn($table, 'nombre_bd')) {
            DB::statement(
                "IF COL_LENGTH(N'dbo.{$table}', N'nombre_bd') IS NULL
                 BEGIN
                     ALTER TABLE dbo.[{$table}] ADD nombre_bd NVARCHAR(128) NULL;
                 END"
            );

            DB::statement(
                "UPDATE dbo.[{$table}]
                 SET nombre_bd = NombreBD
                 WHERE nombre_bd IS NULL AND NombreBD IS NOT NULL"
            );
        }
    }

    public function down(): void
    {
        // Columnas calculadas / backfill: no revertir en demo.
    }
};
