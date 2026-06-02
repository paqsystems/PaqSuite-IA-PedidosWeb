<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Exceptions\PedidosWebBusinessException;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\PqPedidoswebPedidoDetalle;
use App\Services\PedidosWeb\ComprobanteCopiaService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ComprobanteCopiaServiceTest extends TestCase
{
    #[Test]
    public function copiarBorradorPrecargaCabeceraYDetalle(): void
    {
        $cabecera = new PqPedidoswebPedidoCabecera();
        $cabecera->cod_pedido = 'PED-ORIG';
        $cabecera->cod_cliente = 'CLI001';
        $cabecera->nivel = 0;
        $cabecera->observaciones = 'Obs';
        $cabecera->incluye_iva = false;
        $cabecera->moneda = 1;
        $cabecera->descuento = 0;

        $detalle = new PqPedidoswebPedidoDetalle();
        $detalle->renglon = 1;
        $detalle->cod_articulo = 'ART001';
        $detalle->descripcion_articulo = 'Artículo';
        $detalle->cantidad = 3;
        $detalle->porc_bonif = 5;
        $detalle->precio = 100;
        $detalle->porc_iva = 21;

        $cabecera->setRelation('detalles', new Collection([$detalle]));

        $repository = $this->createMock(PedidoRepositoryInterface::class);
        $repository->method('findWithDetalle')->with('PED-ORIG')->willReturn($cabecera);

        $service = new ComprobanteCopiaService($repository);
        $borrador = $service->copiarBorrador('PED-ORIG', 'presupuesto');

        $this->assertSame('CLI001', $borrador['cabecera']['cod_cliente']);
        $this->assertSame('presupuesto', $borrador['tipoComprobante']);
        $this->assertSame('PED-ORIG', $borrador['codComprobanteOrigen']);
        $this->assertCount(1, $borrador['renglones']);
        $this->assertSame('ART001', $borrador['renglones'][0]['cod_articulo']);
    }

    #[Test]
    public function copiarBorradorLanzaNotFoundSiOrigenInexistente(): void
    {
        $repository = $this->createMock(PedidoRepositoryInterface::class);
        $repository->method('findWithDetalle')->willReturn(null);

        $service = new ComprobanteCopiaService($repository);

        $this->expectException(PedidosWebBusinessException::class);
        $service->copiarBorrador('INEXISTENTE', 'pedido');
    }
}
