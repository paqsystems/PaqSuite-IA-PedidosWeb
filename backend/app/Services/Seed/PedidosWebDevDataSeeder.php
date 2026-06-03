<?php

namespace App\Services\Seed;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class PedidosWebDevDataSeeder
{
    public function seedMvpDevData(): void
    {
        $now = CarbonImmutable::now();
        $sqlDateTime = $now->format('Ymd H:i:s');
        $umaFecha = $now->toDateTimeString();

        $this->seedReferencias();
        $this->seedEscalas();
        $this->seedMotivosCierre();
        $this->seedVendedoresYClientesVisibilidad();
        $this->seedArticulosYStock($umaFecha);
        $this->seedListasPreciosArticulos();
        $this->seedComprobantesVisibilidad($sqlDateTime);
        $this->seedStockPedidoWeb($sqlDateTime);
        $this->seedConsultasErp($sqlDateTime);
    }

    private function seedReferencias(): void
    {
        DB::table('pq_pedidosweb_listaprecios')->updateOrInsert(
            ['cod_lista' => 1],
            [
                'incluye_iva' => false,
                'moneda' => 1,
                'descripcion' => 'Lista MVP desarrollo',
                'decimales' => 2,
            ]
        );

        DB::table('pq_pedidosweb_condventa')->updateOrInsert(
            ['codigo' => 1],
            ['descripcion' => 'Contado MVP']
        );

        DB::table('pq_pedidosweb_transportes')->updateOrInsert(
            ['codigo' => 'MVP'],
            ['descripcion' => 'Transporte MVP']
        );

        DB::table('pq_pedidosweb_perfil')->updateOrInsert(
            ['cod_perfil' => 'MVP'],
            ['descripcion' => 'Perfil pedido MVP']
        );

        DB::table('pq_pedidosweb_provincias')->updateOrInsert(
            ['cod_provin' => '01'],
            ['nombre_pro' => 'Buenos Aires']
        );

        DB::table('pq_pedidosweb_tratativas_resultados')->updateOrInsert(
            ['descripcion' => 'Contacto exitoso'],
            ['activo' => true]
        );
    }

    private function seedVendedoresYClientesVisibilidad(): void
    {
        foreach (
            [
                ['cod_vended' => 'VENACOT01', 'nombre' => 'Vendedor Acotado MVP', 'supervisor' => false, 'cod_login' => 'VENACOT01'],
                ['cod_vended' => 'VENSUP01', 'nombre' => 'Supervisor MVP', 'supervisor' => true, 'cod_login' => 'VENSUP01'],
                ['cod_vended' => 'VENSINM01', 'nombre' => 'Vendedor Sin Menu MVP', 'supervisor' => false, 'cod_login' => 'VENSINM01'],
            ] as $vendedor
        ) {
            DB::table('pq_pedidosweb_vendedores')->updateOrInsert(
                ['cod_vended' => $vendedor['cod_vended']],
                [
                    'nombre' => $vendedor['nombre'],
                    'supervisor' => $vendedor['supervisor'],
                    'cod_login' => $vendedor['cod_login'],
                    'e_mail' => strtolower($vendedor['cod_vended']).'@paqsuite.local',
                ]
            );
        }

        foreach (
            [
                ['cod_client' => 'CLI-VEN-A', 'nombre' => 'Cliente Vendedor A', 'cod_vended' => 'VENACOT01'],
                ['cod_client' => 'CLI-VEN-B', 'nombre' => 'Cliente Vendedor B', 'cod_vended' => 'VENSINM01'],
            ] as $cliente
        ) {
            DB::table('pq_pedidosweb_clientes')->updateOrInsert(
                ['cod_client' => $cliente['cod_client']],
                [
                    'nombre' => $cliente['nombre'],
                    'fantasia' => $cliente['nombre'],
                    'cod_vended' => $cliente['cod_vended'],
                    'lista_precios' => 1,
                    'cod_condvta' => 1,
                    'cod_transpor' => 'MVP',
                    'bonificacion' => 0,
                    'nivel' => 0,
                    'e_mail' => strtolower($cliente['cod_client']).'@paqsuite.local',
                ]
            );
        }
    }

    private function seedEscalas(): void
    {
        DB::table('pq_pedidosweb_escalas_cabecera')->updateOrInsert(
            ['cod_escala' => 'L'],
            [
                'descrip_es' => 'Litros',
                'nro_escala' => 1,
            ]
        );

        foreach (
            [
                ['cod_valor' => '100', 'desc_valor' => '1 L'],
                ['cod_valor' => '500', 'desc_valor' => '0.5 L'],
            ] as $valor
        ) {
            DB::table('pq_pedidosweb_escalas_detalle')->updateOrInsert(
                ['cod_escala' => 'L', 'cod_valor' => $valor['cod_valor']],
                ['desc_valor' => $valor['desc_valor']]
            );
        }
    }

    private function seedMotivosCierre(): void
    {
        foreach (
            [
                ['tipo_cierre' => 'positivo', 'descripcion' => 'Cierre exitoso MVP'],
                ['tipo_cierre' => 'negativo', 'descripcion' => 'Rechazo feature test'],
            ] as $motivo
        ) {
            $existing = DB::table('pq_pedidosweb_motivos_cierre')
                ->where('descripcion', $motivo['descripcion'])
                ->first();

            if ($existing === null) {
                DB::table('pq_pedidosweb_motivos_cierre')->insert([
                    'tipo_cierre' => $motivo['tipo_cierre'],
                    'descripcion' => $motivo['descripcion'],
                    'activo' => true,
                ]);
            }
        }
    }

    private function seedArticulosYStock(string $umaFecha): void
    {
        $articulos = [
            ['codigo' => 'ART-HP-001', 'descripcion' => 'Articulo HP principal', 'base' => ''],
            ['codigo' => 'ART-HP-SEED', 'descripcion' => 'Articulo HP seed tests', 'base' => ''],
            ['codigo' => 'ART-A', 'descripcion' => 'Articulo simple stock', 'base' => ''],
            ['codigo' => 'ART-SIN-BASE', 'descripcion' => 'Articulo sin base', 'base' => ''],
            ['codigo' => 'ART-P1', 'descripcion' => 'Presentacion base 01 - P1', 'base' => 'BASE01'],
            ['codigo' => 'ART-P2', 'descripcion' => 'Presentacion base 01 - P2', 'base' => 'BASE01'],
        ];

        foreach ($articulos as $articulo) {
            DB::table('pq_pedidosweb_articulos')->updateOrInsert(
                ['codigo' => $articulo['codigo']],
                [
                    'descripcion' => $articulo['descripcion'],
                    'bonificacion' => 0,
                    'usa_esc' => false,
                    'base' => $articulo['base'],
                    'valor1' => 100,
                    'valor2' => 0,
                    'porc_iva' => 21,
                ]
            );
        }

        $stocks = [
            ['cod_articulo' => 'ART-HP-001', 'stock' => 500, 'comprometido' => 25],
            ['cod_articulo' => 'ART-HP-SEED', 'stock' => 200, 'comprometido' => 10],
            ['cod_articulo' => 'ART-A', 'stock' => 100, 'comprometido' => 10],
            ['cod_articulo' => 'ART-SIN-BASE', 'stock' => 40, 'comprometido' => 4],
            ['cod_articulo' => 'ART-P1', 'stock' => 50, 'comprometido' => 5],
            ['cod_articulo' => 'ART-P2', 'stock' => 100, 'comprometido' => 15],
        ];

        foreach ($stocks as $stock) {
            DB::table('pq_pedidosweb_stock')->updateOrInsert(
                ['cod_articulo' => $stock['cod_articulo']],
                [
                    'stock' => $stock['stock'],
                    'comprometido' => $stock['comprometido'],
                    'uma_fecha' => $umaFecha,
                ]
            );
        }
    }

    private function seedListasPreciosArticulos(): void
    {
        foreach (['ART-HP-001', 'ART-HP-SEED', 'ART-A', 'ART-P1', 'ART-P2'] as $codigo) {
            DB::table('pq_pedidosweb_listaprecios_articulos')->updateOrInsert(
                ['cod_lista' => 1, 'cod_articulo' => $codigo],
                ['precio' => 100]
            );
        }
    }

    private function seedComprobantesVisibilidad(string $sqlDateTime): void
    {
        $this->upsertComprobante('PED-CLI-1', 'CLIMVP001', null, 0, 110.00, $sqlDateTime);
        $this->upsertComprobante('PED-VEN-A-99', 'CLI-VEN-A', 'VENACOT01', 99, 200.00, $sqlDateTime);
        $this->upsertComprobante('PED-VEN-A-0', 'CLI-VEN-A', 'VENACOT01', 0, 300.00, $sqlDateTime);
        $this->upsertComprobante('PED-VEN-A-1', 'CLI-VEN-A', 'VENACOT01', 1, 150.00, $sqlDateTime);
        $this->upsertComprobante('PED-VEN-B-0', 'CLI-VEN-B', 'VENSINM01', 0, 999.00, $sqlDateTime);

        $this->upsertDetalle('PED-CLI-1', 1, 'ART-HP-001', 1, 110);
        $this->upsertDetalle('PED-VEN-A-0', 1, 'ART-HP-001', 2, 150);
        $this->upsertDetalle('PED-VEN-B-0', 1, 'ART-HP-SEED', 3, 333);
    }

    private function seedStockPedidoWeb(string $sqlDateTime): void
    {
        DB::table('pq_pedidosweb_pedidoscabecera')->updateOrInsert(
            ['cod_pedido' => 'PED-STK-01'],
            [
                'cod_cliente' => 'CLIMVP001',
                'fecha' => $sqlDateTime,
                'nivel' => 0,
                'observaciones' => 'Pedido ingresado para comprometido web stock',
                'incluye_iva' => false,
                'moneda' => 1,
                'estado' => 0,
                'tal_pedido_tango' => 1,
                'nro_pedido_tango' => 'PED-STK-01',
                'cod_usuario_web' => 'supervisor.mvp',
                'fecha_modif' => $sqlDateTime,
                'total' => 1000,
                'total_iva' => 210,
                'descuento' => 0,
                'bonif_1' => 0,
                'bonif_2' => 0,
                'bonif_3' => 0,
                'cod_perfil' => 'MVP',
                'cod_vended' => 'VENACOT01',
                'cod_condvta' => 1,
                'cod_transpor' => 'MVP',
                'lista_precios' => 1,
            ]
        );

        foreach (
            [
                ['renglon' => 1, 'cod_articulo' => 'ART-A', 'cantidad' => 5],
                ['renglon' => 2, 'cod_articulo' => 'ART-P1', 'cantidad' => 3],
                ['renglon' => 3, 'cod_articulo' => 'ART-P2', 'cantidad' => 5],
            ] as $detalle
        ) {
            $this->upsertDetalle('PED-STK-01', $detalle['renglon'], $detalle['cod_articulo'], $detalle['cantidad'], 100);
        }
    }

    private function seedConsultasErp(string $sqlDateTime): void
    {
        $fechaProceso = CarbonImmutable::now()->toDateTimeString();
        $deudaColumns = app(\App\Services\PedidosWeb\PedidosWebSchemaBootstrap::class)->deudaColumnMap();

        DB::table('pq_pedidosweb_clientes')->updateOrInsert(
            ['cod_client' => 'CLIMVP001'],
            ['razon_soci' => 'Cliente MVP SA']
        );

        DB::table('pq_pedidosweb_deuda')->updateOrInsert(
            [
                'cod_cliente' => 'CLIMVP001',
                $deudaColumns['tipo'] => 'FAC',
                $deudaColumns['numero'] => '0001-00001234',
                'fecha_vto' => $sqlDateTime,
            ],
            [
                $deudaColumns['fecha'] => $sqlDateTime,
                'fecha_proceso' => $fechaProceso,
                'saldo' => 1250.50,
            ]
        );

        DB::table('pq_pedidosweb_cheques')->updateOrInsert(
            ['interno' => 'CHQ-001', 'numero' => '12345678'],
            [
                'cod_client' => 'CLIMVP001',
                'banco' => 'Banco MVP',
                'importe' => 5000,
                'fecha' => $sqlDateTime,
                'origen' => 'Cartera',
                'estado' => 'En cartera',
                'fecha_proceso' => $fechaProceso,
            ]
        );

        DB::table('pq_pedidosweb_ventadetallada')->insert([
            'cod_cli' => 'CLIMVP001',
            'razon_soci' => 'Cliente MVP SA',
            'n_remito' => 'REM-0001',
            't_comp' => 'FAC',
            'n_comp' => 'A-00001234',
            'fecha_emi' => $sqlDateTime,
            'cond_vta' => 1,
            'porc_desc' => 0,
            'cotiz' => 1,
            'moneda' => 'ARS',
            'total_comp' => 1210,
            'cod_transp' => 'TRP01',
            'nom_transp' => 'Transporte MVP',
            'cod_articu' => 'ART-HP-001',
            'descripcio' => 'Venta historica MVP',
            'cod_dep' => 'DEP01',
            'um' => 'UN',
            'cantidad' => 10,
            'precio' => 100,
            'tot_s_imp' => 1000,
            'n_comp_rem' => null,
            'cant_rem' => null,
            'fecha_rem' => null,
            'fecha_proceso' => $fechaProceso,
        ]);

        DB::table('pq_pedidosweb_logs_integracion')->insert([
            'fecha' => $fechaProceso,
            'tipo' => 'SYNC',
            'severidad' => 'INFO',
            'origen' => 'bootstrap-dev',
            'mensaje' => 'Seed desarrollo PedidosWeb aplicado',
            'payload' => null,
            'procesado' => true,
        ]);
    }

    private function upsertComprobante(
        string $codPedido,
        string $codCliente,
        ?string $codVendedor,
        int $estado,
        float $total,
        string $sqlDateTime
    ): void {
        DB::table('pq_pedidosweb_pedidoscabecera')->updateOrInsert(
            ['cod_pedido' => $codPedido],
            [
                'cod_cliente' => $codCliente,
                'fecha' => $sqlDateTime,
                'nivel' => 0,
                'observaciones' => 'Seed visibilidad / dashboard MVP',
                'incluye_iva' => false,
                'moneda' => 1,
                'estado' => $estado,
                'tal_pedido_tango' => 1,
                'nro_pedido_tango' => substr($codPedido, 0, 20),
                'cod_usuario_web' => 'supervisor.mvp',
                'fecha_modif' => $sqlDateTime,
                'total' => $total,
                'total_iva' => round($total * 0.21, 2),
                'descuento' => 0,
                'bonif_1' => 0,
                'bonif_2' => 0,
                'bonif_3' => 0,
                'cod_perfil' => 'MVP',
                'cod_vended' => $codVendedor,
                'cod_condvta' => 1,
                'cod_transpor' => 'MVP',
                'lista_precios' => 1,
            ]
        );
    }

    private function upsertDetalle(
        string $codPedido,
        int $renglon,
        string $codArticulo,
        float $cantidad,
        float $precio
    ): void {
        $importe = round($precio * $cantidad, 2);

        DB::table('pq_pedidosweb_pedidosdetalle')->updateOrInsert(
            ['cod_pedido' => $codPedido, 'renglon' => $renglon],
            [
                'cod_articulo' => $codArticulo,
                'cantidad' => $cantidad,
                'porc_bonif' => 0,
                'precio' => $precio,
                'precio_neto' => $precio,
                'precio_bruto' => $precio,
                'porc_iva' => 21,
                'iva' => round($importe * 0.21, 2),
                'importe_total' => round($importe * 1.21, 2),
            ]
        );
    }
}
