<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv' || ! Schema::hasTable('pq_grid_layout_last_used')) {
            return;
        }

        if (! Schema::hasColumn('pq_grid_layout_last_used', 'created_at')) {
            DB::statement(
                'ALTER TABLE dbo.pq_grid_layout_last_used ADD created_at DATETIME2 NULL'
            );
            DB::statement(
                'UPDATE dbo.pq_grid_layout_last_used SET created_at = COALESCE(updated_at, SYSUTCDATETIME()) WHERE created_at IS NULL'
            );
        }
    }

    public function down(): void
    {
        // Sin revert en demo.
    }
};
