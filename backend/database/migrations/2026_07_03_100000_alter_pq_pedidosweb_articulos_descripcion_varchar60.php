<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ampliar descripción de artículo ERP: VARCHAR(50) → VARCHAR(60).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_articulos') || ! Schema::hasColumn('pq_pedidosweb_articulos', 'descripcion')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement(<<<'SQL'
ALTER TABLE [pq_pedidosweb_articulos]
    ALTER COLUMN [descripcion] VARCHAR(60) NULL;
SQL);

            return;
        }

        Schema::table('pq_pedidosweb_articulos', function (Blueprint $table): void {
            $table->string('descripcion', 60)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_articulos') || ! Schema::hasColumn('pq_pedidosweb_articulos', 'descripcion')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement(<<<'SQL'
ALTER TABLE [pq_pedidosweb_articulos]
    ALTER COLUMN [descripcion] VARCHAR(50) NULL;
SQL);

            return;
        }

        Schema::table('pq_pedidosweb_articulos', function (Blueprint $table): void {
            $table->string('descripcion', 50)->nullable()->change();
        });
    }
};
