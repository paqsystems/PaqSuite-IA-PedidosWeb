<?php

namespace App\Services\Seed;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PedidoswebReferenceBootstrap
{
    public function ensureMvpReferences(): void
    {
        if (Schema::hasTable('pq_pedidosweb_listaprecios')
            && DB::table('pq_pedidosweb_listaprecios')->where('cod_lista', 1)->doesntExist()) {
            DB::table('pq_pedidosweb_listaprecios')->insert([
                'cod_lista' => 1,
                'incluye_iva' => false,
                'moneda' => 1,
                'descripcion' => 'Lista MVP seed',
                'decimales' => 2,
            ]);
        }

        if (Schema::hasTable('pq_pedidosweb_condventa')
            && DB::table('pq_pedidosweb_condventa')->where('codigo', 1)->doesntExist()) {
            DB::table('pq_pedidosweb_condventa')->insert([
                'codigo' => 1,
                'descripcion' => 'Condicion MVP seed',
            ]);
        }
    }
}
