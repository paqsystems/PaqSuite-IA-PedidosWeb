<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Columna canónica ERP: bonificación de renglón en detalle de pedido.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = 'pq_pedidosweb_pedidosdetalle';

        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, 'bonificacion')) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->decimal('bonificacion', 6, 2)->nullable();
            });
        }

        if (
            Schema::hasColumn($table, 'bonificacion')
            && Schema::hasColumn($table, 'porc_bonif')
            && Schema::getConnection()->getDriverName() === 'sqlsrv'
        ) {
            DB::statement(<<<'SQL'
UPDATE d
SET d.bonificacion = d.porc_bonif
FROM dbo.pq_pedidosweb_pedidosdetalle AS d WITH (NOLOCK)
WHERE d.bonificacion IS NULL AND d.porc_bonif IS NOT NULL
SQL);
        }
    }

    public function down(): void
    {
        $table = 'pq_pedidosweb_pedidosdetalle';

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'bonificacion')) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('bonificacion');
            });
        }
    }
};
