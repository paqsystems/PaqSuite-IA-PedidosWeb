<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-11 SP espera user_id e is_system; host usa created_by_user_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv' || ! Schema::hasTable('pq_grid_layouts')) {
            return;
        }

        if (! Schema::hasColumn('pq_grid_layouts', 'is_system')) {
            DB::statement(
                'ALTER TABLE dbo.pq_grid_layouts ADD is_system BIT NOT NULL CONSTRAINT DF_pq_grid_layouts_is_system DEFAULT (0)'
            );
        }

        if (! Schema::hasColumn('pq_grid_layouts', 'user_id') && Schema::hasColumn('pq_grid_layouts', 'created_by_user_id')) {
            DB::statement(
                'ALTER TABLE dbo.pq_grid_layouts ADD user_id AS [created_by_user_id] PERSISTED'
            );
        }
    }

    public function down(): void
    {
        // Sin revert en demo.
    }
};
