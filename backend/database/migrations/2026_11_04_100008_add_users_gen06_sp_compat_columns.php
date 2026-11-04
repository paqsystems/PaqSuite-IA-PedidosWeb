<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alias GEN-06 sobre users PedidosWeb (codigo → usuario, name_user → name).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv' || ! Schema::hasTable('users')) {
            return;
        }

        $adds = [
            'usuario' => 'codigo',
            'name' => 'name_user',
        ];

        foreach ($adds as $alias => $source) {
            if (Schema::hasColumn('users', $alias) || ! Schema::hasColumn('users', $source)) {
                continue;
            }

            DB::statement(
                "IF COL_LENGTH(N'dbo.users', N'{$alias}') IS NULL
                 BEGIN
                     ALTER TABLE dbo.users ADD {$alias} AS [{$source}] PERSISTED;
                 END"
            );
        }
    }

    public function down(): void
    {
        // Sin revert en demo.
    }
};
