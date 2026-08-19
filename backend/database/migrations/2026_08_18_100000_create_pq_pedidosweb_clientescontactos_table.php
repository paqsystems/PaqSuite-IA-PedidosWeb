<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CC PQ #11 — contactos de cliente para API GET /clientes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pq_pedidosweb_clientescontactos')) {
            return;
        }

        Schema::create('pq_pedidosweb_clientescontactos', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('cod_client', 20);
            $table->string('cod_contacto', 50);
            $table->string('nombre', 120);
            $table->string('telefono', 50)->nullable();
            $table->string('mail', 120)->nullable();
            $table->unique(['cod_client', 'cod_contacto'], 'UQ_pw_clicont_cli_cod');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_pedidosweb_clientescontactos');
    }
};
