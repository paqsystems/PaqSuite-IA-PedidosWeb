<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Compat SP GEN-15/Excel: valor_logico / valor_entero / valor_string desde columnas PedidosWeb.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        if (! Schema::hasTable('PQ_PARAMETROS_GRAL')) {
            return;
        }

        if (! Schema::hasColumn('PQ_PARAMETROS_GRAL', 'valor_logico') && Schema::hasColumn('PQ_PARAMETROS_GRAL', 'Valor_Bool')) {
            DB::statement(
                "IF COL_LENGTH(N'dbo.PQ_PARAMETROS_GRAL', N'valor_logico') IS NULL
                 BEGIN
                     ALTER TABLE dbo.PQ_PARAMETROS_GRAL
                     ADD valor_logico AS Valor_Bool PERSISTED;
                 END"
            );
        }

        if (! Schema::hasColumn('PQ_PARAMETROS_GRAL', 'valor_entero') && Schema::hasColumn('PQ_PARAMETROS_GRAL', 'Valor_Int')) {
            DB::statement(
                "IF COL_LENGTH(N'dbo.PQ_PARAMETROS_GRAL', N'valor_entero') IS NULL
                 BEGIN
                     ALTER TABLE dbo.PQ_PARAMETROS_GRAL
                     ADD valor_entero AS Valor_Int PERSISTED;
                 END"
            );
        }

        if (! Schema::hasColumn('PQ_PARAMETROS_GRAL', 'valor_string') && Schema::hasColumn('PQ_PARAMETROS_GRAL', 'Valor_String')) {
            DB::statement(
                "IF COL_LENGTH(N'dbo.PQ_PARAMETROS_GRAL', N'valor_string') IS NULL
                 BEGIN
                     ALTER TABLE dbo.PQ_PARAMETROS_GRAL
                     ADD valor_string AS Valor_String PERSISTED;
                 END"
            );
        }
    }

    public function down(): void
    {
        // No revertir columnas calculadas en demo.
    }
};
