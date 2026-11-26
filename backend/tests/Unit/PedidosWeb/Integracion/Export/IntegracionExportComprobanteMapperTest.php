<?php

namespace Tests\Unit\PedidosWeb\Integracion\Export;

use App\Services\PedidosWeb\Integracion\Export\IntegracionExportComprobanteMapper;
use Tests\TestCase;

final class IntegracionExportComprobanteMapperTest extends TestCase
{
    public function testEnsamblaCabeceraRenglonesYCierre(): void
    {
        $mapper = new IntegracionExportComprobanteMapper();

        $cabecera = (object) [
            'cod_pedido' => 'PED-001',
            'cod_cliente' => 'CLI1',
            'estado' => 98,
            'fecha' => '2026-06-01 10:00:00',
            'moneda' => 1,
            'total' => 100,
            'total_iva' => 21,
            'incluye_iva' => 0,
            'cliente_nombre' => 'Cliente SA',
            'cliente_fantasia' => '',
            'tal_pedido_tango' => 1,
            'nro_pedido_tango' => '123',
            'cod_usuario_web' => 'int.user',
            'cierre_tipo_cierre' => 'negativo',
            'cierre_id_motivo' => 5,
            'cierre_motivo_descripcion' => 'Rechazo',
            'cierre_fecha_cierre' => '2026-06-02 12:00:00',
            'cierre_observacion' => 'obs',
        ];

        $renglon = (object) [
            'cod_pedido' => 'PED-001',
            'renglon' => 1,
            'cod_articulo' => 'ART1',
            'descripcion_articulo' => 'Desc',
            'cantidad' => 2,
            'cantidad_venta' => 2,
            'porc_bonif' => 0,
            'precio' => 50,
            'precio_neto' => 45,
            'precio_bruto' => 45,
            'importe_neto' => 90,
            'iva' => 18.9,
            'importe_total' => 108.9,
            'articulo_especial' => 1,
        ];

        $items = $mapper->assembleItems([$cabecera], [$renglon]);

        $this->assertCount(1, $items);
        $payload = $items[0]->toArray();
        $this->assertSame('PED-001', $payload['cabecera']['codPedido']);
        $this->assertSame('int.user', $payload['cabecera']['codUsuarioWeb']);
        $this->assertArrayNotHasKey('guidSufijo', $payload['cabecera']);
        $this->assertSame('*', $payload['renglones'][0]['especial']);
        $this->assertSame('negativo', $payload['cierre']['tipoCierre'] ?? null);
    }

    public function testCierreNullCuandoEstadoNoEs98(): void
    {
        $mapper = new IntegracionExportComprobanteMapper();
        $cabecera = (object) [
            'cod_pedido' => 'P1',
            'cod_cliente' => 'C',
            'estado' => 0,
            'fecha' => '2026-06-01 10:00:00',
            'moneda' => 1,
            'total' => 1,
            'total_iva' => 0,
            'incluye_iva' => 0,
            'cliente_nombre' => 'N',
            'cliente_fantasia' => '',
            'tal_pedido_tango' => 0,
            'nro_pedido_tango' => '',
            'cod_usuario_web' => 'u',
        ];

        $items = $mapper->assembleItems([$cabecera], []);
        $this->assertNull($items[0]->toArray()['cierre']);
    }
}