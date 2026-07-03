<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Contracts\PedidosWeb\ArticuloRepositoryInterface;
use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Exceptions\PedidosWebBusinessException;
use App\Models\PqPedidoswebListaPreciosArticulo;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\PqPedidoswebPedidoDetalle;
use App\Services\PedidosWeb\CalculoTotalesService;
use App\Services\PedidosWeb\ComprobanteCopiaService;
use App\Services\PedidosWeb\PedidosWebParameterService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ComprobanteCopiaServiceTest extends TestCase
{
    #[Test]
    public function copiarBorradorPrecargaCabeceraYDetalle(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-ORIG', 0, 100);
        $service = $this->buildService($cabecera, actualizarPrecioCopia: false);

        $borrador = $service->copiarBorrador('PED-ORIG', 'presupuesto');

        $this->assertSame('CLI001', $borrador['cabecera']['cod_cliente']);
        $this->assertSame('presupuesto', $borrador['tipoComprobante']);
        $this->assertSame('PED-ORIG', $borrador['codComprobanteOrigen']);
        $this->assertCount(1, $borrador['renglones']);
        $this->assertSame('ART001', $borrador['renglones'][0]['cod_articulo']);
        $this->assertSame(100.0, $borrador['renglones'][0]['precio']);
    }

    #[Test]
    public function copiarBorradorLanzaNotFoundSiOrigenInexistente(): void
    {
        $repository = $this->createMock(PedidoRepositoryInterface::class);
        $repository->method('findWithDetalle')->willReturn(null);

        $service = new ComprobanteCopiaService(
            $repository,
            new PedidosWebParameterService(),
            new CalculoTotalesService(),
            $this->createMock(ArticuloRepositoryInterface::class),
        );

        $this->expectException(PedidosWebBusinessException::class);
        $service->copiarBorrador('INEXISTENTE', 'pedido');
    }

    #[Test]
    public function copiarBorradorRechazaPrecioCeroOrigenCuandoParametrosRestrictivos(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-CERO', 0, 0);
        $service = $this->buildService($cabecera, actualizarPrecioCopia: false);

        $this->expectException(PedidosWebBusinessException::class);
        $this->expectExceptionMessage('business.precioCeroNoPermitido');

        $service->copiarBorrador('PED-CERO', 'pedido');
    }

    #[Test]
    public function copiarBorradorPermitePrecioCeroOrigenCuandoParametrosPermisivos(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-CERO', 0, 0);
        $service = $this->buildService(
            $cabecera,
            actualizarPrecioCopia: false,
            articuloPrecioCero: true,
        );

        $borrador = $service->copiarBorrador('PED-CERO', 'pedido');

        $this->assertSame(0.0, $borrador['renglones'][0]['precio']);
    }

    #[Test]
    public function copiarBorradorActualizaPreciosDesdeListaCuandoParametroActivo(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-LISTA', 5, 50);
        $precioLista = new PqPedidoswebListaPreciosArticulo();
        $precioLista->precio = 175.5;

        $articuloRepository = $this->createMock(ArticuloRepositoryInterface::class);
        $articuloRepository
            ->method('findPrecioLista')
            ->with(5, 'ART001')
            ->willReturn($precioLista);

        $service = $this->buildService(
            $cabecera,
            actualizarPrecioCopia: true,
            articuloRepository: $articuloRepository,
        );

        $borrador = $service->copiarBorrador('PED-LISTA', 'pedido');

        $this->assertSame(175.5, $borrador['renglones'][0]['precio']);
        $this->assertArrayHasKey('importe_neto', $borrador['renglones'][0]);
    }

    #[Test]
    public function copiarBorradorRechazaPrecioListaCeroCuandoParametrosRestrictivos(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-LISTA', 5, 50);

        $articuloRepository = $this->createMock(ArticuloRepositoryInterface::class);
        $articuloRepository
            ->method('findPrecioLista')
            ->with(5, 'ART001')
            ->willReturn(null);

        $service = $this->buildService(
            $cabecera,
            actualizarPrecioCopia: true,
            articuloRepository: $articuloRepository,
        );

        $this->expectException(PedidosWebBusinessException::class);
        $this->expectExceptionMessage('business.precioCeroNoPermitido');

        $service->copiarBorrador('PED-LISTA', 'pedido');
    }

    #[Test]
    public function copiarBorradorConListaInvalidaValidaPreciosOrigen(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-SIN-LISTA', 0, 0);
        $service = $this->buildService($cabecera, actualizarPrecioCopia: true);

        $this->expectException(PedidosWebBusinessException::class);
        $this->expectExceptionMessage('business.precioCeroNoPermitido');

        $service->copiarBorrador('PED-SIN-LISTA', 'pedido');
    }

    #[Test]
    public function copiarBorradorRecalculaImportesConBonificacionCabecera(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-BONIF', 5, 50);
        $cabecera->descuento = 10;

        $precioLista = new PqPedidoswebListaPreciosArticulo();
        $precioLista->precio = 100;

        $articuloRepository = $this->createMock(ArticuloRepositoryInterface::class);
        $articuloRepository
            ->method('findPrecioLista')
            ->with(5, 'ART001')
            ->willReturn($precioLista);

        $service = $this->buildService(
            $cabecera,
            actualizarPrecioCopia: true,
            articuloRepository: $articuloRepository,
        );

        $borrador = $service->copiarBorrador('PED-BONIF', 'pedido');

        $this->assertSame(100.0, $borrador['renglones'][0]['precio']);
        $this->assertSame(256.5, $borrador['renglones'][0]['importe_neto']);
        $this->assertSame(53.87, $borrador['renglones'][0]['iva']);
    }

    #[Test]
    public function copiarBorradorSinPrecioEnListaPermiteSiArticulosSinPrecio(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-SIN-PRECIO', 5, 80);

        $articuloRepository = $this->createMock(ArticuloRepositoryInterface::class);
        $articuloRepository
            ->method('findPrecioLista')
            ->with(5, 'ART001')
            ->willReturn(null);

        $service = $this->buildService(
            $cabecera,
            actualizarPrecioCopia: true,
            articulosSinPrecio: true,
            articuloRepository: $articuloRepository,
        );

        $borrador = $service->copiarBorrador('PED-SIN-PRECIO', 'pedido');

        $this->assertSame(0.0, $borrador['renglones'][0]['precio']);
    }

    #[Test]
    public function copiarBorradorPrecioCeroEnListaPermiteSiArticuloPrecioCero(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-PRECIO-CERO', 5, 80);

        $precioLista = new PqPedidoswebListaPreciosArticulo();
        $precioLista->precio = 0;

        $articuloRepository = $this->createMock(ArticuloRepositoryInterface::class);
        $articuloRepository
            ->method('findPrecioLista')
            ->with(5, 'ART001')
            ->willReturn($precioLista);

        $service = $this->buildService(
            $cabecera,
            actualizarPrecioCopia: true,
            articuloPrecioCero: true,
            articuloRepository: $articuloRepository,
        );

        $borrador = $service->copiarBorrador('PED-PRECIO-CERO', 'pedido');

        $this->assertSame(0.0, $borrador['renglones'][0]['precio']);
    }

    #[Test]
    public function copiarBorradorRechazaPrecioCeroEnListaCuandoSoloSinPrecioPermitido(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-PRECIO-CERO-LISTA', 5, 80);

        $precioLista = new PqPedidoswebListaPreciosArticulo();
        $precioLista->precio = 0;

        $articuloRepository = $this->createMock(ArticuloRepositoryInterface::class);
        $articuloRepository
            ->method('findPrecioLista')
            ->with(5, 'ART001')
            ->willReturn($precioLista);

        $service = $this->buildService(
            $cabecera,
            actualizarPrecioCopia: true,
            articulosSinPrecio: true,
            articuloRepository: $articuloRepository,
        );

        $this->expectException(PedidosWebBusinessException::class);
        $this->expectExceptionMessage('business.precioCeroNoPermitido');

        $service->copiarBorrador('PED-PRECIO-CERO-LISTA', 'pedido');
    }

    #[Test]
    public function copiarBorradorRechazaSinPrecioEnListaCuandoSoloPrecioCeroPermitido(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-SIN-PRECIO-LISTA', 5, 80);

        $articuloRepository = $this->createMock(ArticuloRepositoryInterface::class);
        $articuloRepository
            ->method('findPrecioLista')
            ->with(5, 'ART001')
            ->willReturn(null);

        $service = $this->buildService(
            $cabecera,
            actualizarPrecioCopia: true,
            articuloPrecioCero: true,
            articuloRepository: $articuloRepository,
        );

        $this->expectException(PedidosWebBusinessException::class);
        $this->expectExceptionMessage('business.precioCeroNoPermitido');

        $service->copiarBorrador('PED-SIN-PRECIO-LISTA', 'pedido');
    }

    #[Test]
    public function copiarBorradorNoExponeMetadatoPrecioListaAusente(): void
    {
        $cabecera = $this->buildCabeceraOrigen('PED-META', 5, 50);
        $precioLista = new PqPedidoswebListaPreciosArticulo();
        $precioLista->precio = 120;

        $articuloRepository = $this->createMock(ArticuloRepositoryInterface::class);
        $articuloRepository
            ->method('findPrecioLista')
            ->with(5, 'ART001')
            ->willReturn($precioLista);

        $service = $this->buildService(
            $cabecera,
            actualizarPrecioCopia: true,
            articuloRepository: $articuloRepository,
        );

        $borrador = $service->copiarBorrador('PED-META', 'pedido');

        $this->assertArrayNotHasKey('precioListaAusente', $borrador['renglones'][0]);
    }

    private function buildCabeceraOrigen(string $codPedido, int $listaPrecios, float $precioDetalle): PqPedidoswebPedidoCabecera
    {
        $cabecera = new PqPedidoswebPedidoCabecera();
        $cabecera->cod_pedido = $codPedido;
        $cabecera->cod_cliente = 'CLI001';
        $cabecera->nivel = 0;
        $cabecera->observaciones = 'Obs';
        $cabecera->incluye_iva = false;
        $cabecera->moneda = 1;
        $cabecera->descuento = 0;
        $cabecera->lista_precios = $listaPrecios > 0 ? $listaPrecios : null;

        $detalle = new PqPedidoswebPedidoDetalle();
        $detalle->renglon = 1;
        $detalle->cod_articulo = 'ART001';
        $detalle->descripcion_articulo = 'Artículo';
        $detalle->cantidad = 3;
        $detalle->porc_bonif = 5;
        $detalle->precio = $precioDetalle;
        $detalle->porc_iva = 21;

        $cabecera->setRelation('detalles', new Collection([$detalle]));

        return $cabecera;
    }

    private function buildService(
        PqPedidoswebPedidoCabecera $cabecera,
        bool $actualizarPrecioCopia,
        bool $articuloPrecioCero = false,
        bool $articulosSinPrecio = false,
        ?ArticuloRepositoryInterface $articuloRepository = null,
    ): ComprobanteCopiaService {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.ActualizarPrecioCopia', $actualizarPrecioCopia ? 1 : 0);
        config()->set('paqsuite_pedidosweb.defaults.ArticulosPrecioCero', $articuloPrecioCero ? 1 : 0);
        config()->set('paqsuite_pedidosweb.defaults.ArticulosSinPrecio', $articulosSinPrecio ? 1 : 0);

        $repository = $this->createMock(PedidoRepositoryInterface::class);
        $repository
            ->method('findWithDetalle')
            ->with($cabecera->cod_pedido)
            ->willReturn($cabecera);

        return new ComprobanteCopiaService(
            $repository,
            new PedidosWebParameterService(),
            new CalculoTotalesService(),
            $articuloRepository ?? $this->createMock(ArticuloRepositoryInterface::class),
        );
    }
}
