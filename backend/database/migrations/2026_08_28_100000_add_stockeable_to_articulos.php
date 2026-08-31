<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CC PQ #12 — stockeable en artículos.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pq_pedidosweb_articulos')
            && ! Schema::hasColumn('pq_pedidosweb_articulos', 'stockeable')
        ) {
            Schema::table('pq_pedidosweb_articulos', function (Blueprint $table): void {
                $table->boolean('stockeable')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pq_pedidosweb_articulos')
            && Schema::hasColumn('pq_pedidosweb_articulos', 'stockeable')
        ) {
            Schema::table('pq_pedidosweb_articulos', function (Blueprint $table): void {
                $table->dropColumn('stockeable');
            });
        }
    }
};
