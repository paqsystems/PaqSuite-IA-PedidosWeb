<?php

namespace Tests\Fixtures\PedidosWeb;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class IntegracionExportComprobantesFixture
{
    public const CLIENTE_A = 'CLIMVP001';

    public const CLIENTE_B = 'CLIMVP002';

    public static function seedMultiEstado(string $codCliente = self::CLIENTE_A): array
    {
        $now = CarbonImmutable::now()->format('Ymd H:i:s');
        $codigos = [];

        foreach ([-1, 0, 1, 98, 99] as $estado) {
            $codPedido = 'EXP'.str_pad((string) abs($estado), 2, '0', STR_PAD_LEFT).substr(uniqid('', true), -6);
            $codigos[$estado] = $codPedido;
            $clienteRow = $estado === 99 ? self::CLIENTE_B : $codCliente;

            DB::table('pq_pedidosweb_pedidoscabecera')->updateOrInsert(
                ['cod_pedido' => $codPedido],
                [
                    'cod_cliente' => $clienteRow,
                    'fecha' => $now,
                    'nivel' => 0,
                    'observaciones' => 'Fixture export integración',
                    'incluye_iva' => false,
                    'moneda' => 1,
                    'estado' => $estado,
                    'tal_pedido_tango' => 1,
                    'nro_pedido_tango' => 'T'.substr($codPedido, -8),
                    'cod_usuario_web' => 'integracion.erp.mvp',
                    'fecha_modif' => $now,
                    'total' => 150,
                    'total_iva' => 0,
                    'descuento' => 0,
                    'bonif_1' => 0,
                    'bonif_2' => 0,
                    'bonif_3' => 0,
                    'cod_perfil' => 'MVP',
                    'cod_vended' => 'VENACOT01',
                    'cod_condvta' => 1,
                    'cod_transpor' => 'MVP',
                    'lista_precios' => 1,
                    'nro_visible' => 1,
                ],
            );

            if ($estado === 0) {
                foreach ([1, 2] as $renglon) {
                    DB::table('pq_pedidosweb_pedidosdetalle')->updateOrInsert(
                        ['cod_pedido' => $codPedido, 'renglon' => $renglon],
                        [
                            'cod_articulo' => 'ART-HP-00'.$renglon,
                            'descripcion_articulo' => 'Artículo fixture export '.$renglon,
                            'cantidad' => 1,
                            'cantidad_venta' => 1,
                            'porc_bonif' => 0,
                            'precio' => 75,
                            'precio_neto' => 75,
                            'precio_bruto' => 75,
                            'importe_neto' => 75,
                            'iva' => 0,
                            'importe_total' => 75,
                        ],
                    );
                }
            }

            if ($estado === 98) {
                $idMotivo = DB::table('pq_pedidosweb_motivos_cierre')->value('id_motivo');
                if ($idMotivo !== null) {
                    DB::table('pq_pedidosweb_presupuestos_cierres')->updateOrInsert(
                        ['cod_presupuesto' => $codPedido],
                        [
                            'tipo_cierre' => 'negativo',
                            'id_motivo' => $idMotivo,
                            'fecha_cierre' => $now,
                            'cod_usuario_web' => 'integracion.erp.mvp',
                            'observacion' => 'Cierre fixture',
                        ],
                    );
                }
            }
        }

        return $codigos;
    }
}