<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Columnas opcionales GEN-10; SP core omitido en manifiesto host (esquema legacy PedidosWeb). */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv' || ! Schema::hasTable('PQ_PARAMETROS_GRAL')) {
            return;
        }

        if (! Schema::hasColumn('PQ_PARAMETROS_GRAL', 'precision_fecha')) {
            DB::statement('ALTER TABLE dbo.PQ_PARAMETROS_GRAL ADD precision_fecha NVARCHAR(20) NULL');
        }
        if (! Schema::hasColumn('PQ_PARAMETROS_GRAL', 'meta_json')) {
            DB::statement('ALTER TABLE dbo.PQ_PARAMETROS_GRAL ADD meta_json NVARCHAR(MAX) NULL');
        }
        if (! Schema::hasColumn('PQ_PARAMETROS_GRAL', 'created_at')) {
            DB::statement('ALTER TABLE dbo.PQ_PARAMETROS_GRAL ADD created_at DATETIME2 NULL');
        }
        if (! Schema::hasColumn('PQ_PARAMETROS_GRAL', 'updated_at')) {
            DB::statement('ALTER TABLE dbo.PQ_PARAMETROS_GRAL ADD updated_at DATETIME2 NULL');
        }
    }

    public function down(): void
    {
        // Sin revert en demo.
    }
};
