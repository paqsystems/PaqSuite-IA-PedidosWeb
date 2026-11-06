<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Columnas alias para SP GEN-06/07 (pq_sp_admin_menus_arbol_enabled, list enabled rama codigo).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv' || ! Schema::hasTable('pq_menus')) {
            return;
        }

        $adds = [
            'activo' => 'enabled',
            'parent_id' => 'idparent',
            'titulo' => 'text',
            'ruta' => 'routeName',
            'process_type' => 'tipo_proceso',
        ];

        foreach ($adds as $alias => $source) {
            if (Schema::hasColumn('pq_menus', $alias) || ! Schema::hasColumn('pq_menus', $source)) {
                continue;
            }

            DB::statement(
                "IF COL_LENGTH(N'dbo.pq_menus', N'{$alias}') IS NULL
                 BEGIN
                     ALTER TABLE dbo.pq_menus ADD {$alias} AS [{$source}] PERSISTED;
                 END"
            );
        }
    }

    public function down(): void
    {
        // Sin revert en demo.
    }
};
