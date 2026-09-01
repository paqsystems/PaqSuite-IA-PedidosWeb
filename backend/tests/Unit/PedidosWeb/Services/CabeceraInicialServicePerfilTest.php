<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Services\PedidosWeb\CabeceraInicialService;
use App\Services\PedidosWeb\PedidosWebParameterService;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

final class CabeceraInicialServicePerfilTest extends TestCase
{
    #[Test]
    public function resolveCodPerfilInicialRetornaNullCuandoParametroEsCero(): void
    {
        $service = new CabeceraInicialService(
            $this->createMock(PedidosWebVisibilityGuard::class),
            new PedidosWebParameterService(),
        );

        $method = new ReflectionMethod(CabeceraInicialService::class, 'resolveCodPerfilInicial');
        $method->setAccessible(true);

        $result = $method->invoke($service, '0', [
            ['cod_perfil' => '1', 'descripcion' => 'Perfil 1'],
        ]);

        $this->assertNull($result);
    }

    #[Test]
    public function resolveCodPerfilInicialRetornaCodigoCuandoExisteEnCatalogo(): void
    {
        $service = new CabeceraInicialService(
            $this->createMock(PedidosWebVisibilityGuard::class),
            new PedidosWebParameterService(),
        );

        $method = new ReflectionMethod(CabeceraInicialService::class, 'resolveCodPerfilInicial');
        $method->setAccessible(true);

        $result = $method->invoke($service, '1', [
            ['cod_perfil' => '1', 'descripcion' => 'Perfil 1'],
        ]);

        $this->assertSame('1', $result);
    }

    #[Test]
    public function getCodPerfilPedidosRetornaVacioCuandoValorIntEsCero(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.CodPerfilPedidos', 0);

        $parameterService = new PedidosWebParameterService();

        $this->assertSame('', $parameterService->getCodPerfilPedidos());
    }

    #[Test]
    public function resolveLeyendaClienteRecortaASesenta(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.ClienteLeyenda1', 1);

        $service = new CabeceraInicialService(
            $this->createMock(PedidosWebVisibilityGuard::class),
            new PedidosWebParameterService(),
        );

        $cliente = new \App\Models\PqPedidoswebCliente();
        $cliente->leyenda_1 = str_repeat('c', 61);

        $method = new ReflectionMethod(CabeceraInicialService::class, 'resolveLeyendaCliente');
        $method->setAccessible(true);

        $result = $method->invoke($service, $cliente, 1);

        $this->assertSame(str_repeat('c', 60), $result);
        $this->assertSame(60, mb_strlen((string) $result));
    }
}
