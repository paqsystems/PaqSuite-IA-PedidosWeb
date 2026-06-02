<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Contracts\PedidosWeb\PedidoDetalleRepositoryInterface;
use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Exceptions\PedidosWebBusinessException;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Services\PedidosWeb\CalculoTotalesService;
use App\Services\PedidosWeb\ComprobanteCopiaService;
use App\Services\PedidosWeb\ComprobanteMailService;
use App\Services\PedidosWeb\LogIntegracionService;
use App\Services\PedidosWeb\PedidoService;
use App\Services\PedidosWeb\PedidosWebParameterService;
use App\Services\PedidosWeb\PresupuestoCierreService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PedidoServiceTest extends TestCase
{
    #[Test]
    public function eliminarPedidoPermiteBorrarEstadoCero(): void
    {
        $pedido = new PqPedidoswebPedidoCabecera();
        $pedido->estado = 0;

        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->method('findByCodPedido')->willReturn($pedido);
        $pedidoRepository->expects($this->once())
            ->method('deleteFisicoCabecera')
            ->with('PED-1');

        $detalleRepository = $this->createMock(PedidoDetalleRepositoryInterface::class);
        $detalleRepository->expects($this->once())
            ->method('deleteByCodPedido')
            ->with('PED-1');

        $service = $this->buildService($pedidoRepository, $detalleRepository, false);
        $service->eliminarPedido('PED-1');
    }

    #[Test]
    public function eliminarPedidoRechazaSiEstadoNoEsCero(): void
    {
        $pedido = new PqPedidoswebPedidoCabecera();
        $pedido->estado = 1;

        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->method('findByCodPedido')->willReturn($pedido);
        $pedidoRepository->expects($this->never())->method('deleteFisicoCabecera');

        $detalleRepository = $this->createMock(PedidoDetalleRepositoryInterface::class);
        $detalleRepository->expects($this->never())->method('deleteByCodPedido');

        $service = $this->buildService($pedidoRepository, $detalleRepository, false);

        $this->expectException(PedidosWebBusinessException::class);
        $service->eliminarPedido('PED-2');
    }

    #[Test]
    public function eliminarPedidoRechazaCuandoParametroNoEliminaEstaActivo(): void
    {
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $detalleRepository = $this->createMock(PedidoDetalleRepositoryInterface::class);
        $detalleRepository->expects($this->never())->method('deleteByCodPedido');
        $pedidoRepository->expects($this->never())->method('deleteFisicoCabecera');

        $service = $this->buildService($pedidoRepository, $detalleRepository, true);

        $this->expectException(PedidosWebBusinessException::class);
        $service->eliminarPedido('PED-3');
    }

    private function buildService(
        PedidoRepositoryInterface $pedidoRepository,
        PedidoDetalleRepositoryInterface $detalleRepository,
        bool $noEliminaPedido
    ): PedidoService {
        config()->set('paqsuite_pedidosweb.defaults.NOeliminaPedido', $noEliminaPedido ? 1 : 0);
        config()->set('paqsuite_pedidosweb.defaults.NOmodificaPedido', 0);
        config()->set('paqsuite_pedidosweb.defaults.MinutosWeb', 30);

        $parameterService = new PedidosWebParameterService();
        $copiaService = new ComprobanteCopiaService($pedidoRepository);
        $cierreService = new PresupuestoCierreService($pedidoRepository, $parameterService);
        $mailService = new ComprobanteMailService($parameterService, new LogIntegracionService());

        return new PedidoService(
            $pedidoRepository,
            $detalleRepository,
            $parameterService,
            new CalculoTotalesService(),
            $copiaService,
            $cierreService,
            $mailService
        );
    }
}
