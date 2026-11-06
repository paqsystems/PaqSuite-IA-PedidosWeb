<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv' || ! Schema::hasTable('pq_menus')) {
            return;
        }

        if (! Schema::hasColumn('pq_menus', 'codigo') && Schema::hasColumn('pq_menus', 'procedimiento')) {
            DB::statement(
                'ALTER TABLE dbo.pq_menus ADD codigo AS [procedimiento] PERSISTED'
            );
        }
    }

    public function down(): void
    {
        // Sin revert en demo.
    }
};
