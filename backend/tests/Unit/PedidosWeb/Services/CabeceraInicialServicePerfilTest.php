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
}
