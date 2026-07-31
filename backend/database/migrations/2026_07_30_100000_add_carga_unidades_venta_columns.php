<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CC PQ #10 — equivalencia_ventas en artículos + cantidad_venta en detalle.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pq_pedidosweb_articulos')
            && ! Schema::hasColumn('pq_pedidosweb_articulos', 'equivalencia_ventas')
        ) {
            Schema::table('pq_pedidosweb_articulos', function (Blueprint $table): void {
                $table->decimal('equivalencia_ventas', 18, 4)->default(1);
            });
        }

        if (Schema::hasTable('pq_pedidosweb_pedidosdetalle')
            && ! Schema::hasColumn('pq_pedidosweb_pedidosdetalle', 'cantidad_venta')
        ) {
            Schema::table('pq_pedidosweb_pedidosdetalle', function (Blueprint $table): void {
                $table->decimal('cantidad_venta', 18, 4)->nullable();
            });

            if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
                DB::statement(<<<'SQL'
UPDATE d
SET d.cantidad_venta = d.cantidad
FROM dbo.pq_pedidosweb_pedidosdetalle AS d WITH (NOLOCK)
WHERE d.cantidad_venta IS NULL
SQL);
            } else {
                DB::table('pq_pedidosweb_pedidosdetalle')
                    ->whereNull('cantidad_venta')
                    ->update(['cantidad_venta' => DB::raw('cantidad')]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pq_pedidosweb_pedidosdetalle')
            && Schema::hasColumn('pq_pedidosweb_pedidosdetalle', 'cantidad_venta')
        ) {
            Schema::table('pq_pedidosweb_pedidosdetalle', function (Blueprint $table): void {
                $table->dropColumn('cantidad_venta');
            });
        }

        if (Schema::hasTable('pq_pedidosweb_articulos')
            && Schema::hasColumn('pq_pedidosweb_articulos', 'equivalencia_ventas')
        ) {
            Schema::table('pq_pedidosweb_articulos', function (Blueprint $table): void {
                $table->dropColumn('equivalencia_ventas');
            });
        }
    }
};
