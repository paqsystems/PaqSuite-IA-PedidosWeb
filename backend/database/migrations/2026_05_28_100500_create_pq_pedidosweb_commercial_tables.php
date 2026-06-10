<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Tablas comerciales legacy (pq_pedidosweb_clientes, pq_pedidosweb_vendedores,
        // pq_pedidosweb_login, etc.) provienen del script PedidosWeb del cliente.
        // No crear ni alterar estructura desde Laravel; ver modelos Eloquent alineados.
    }

    public function down(): void
    {
        //
    }
};
