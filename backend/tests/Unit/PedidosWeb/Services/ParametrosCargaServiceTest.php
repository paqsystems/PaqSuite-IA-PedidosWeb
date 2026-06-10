<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Models\User;
use App\Services\Auth\CommercialProfileResolver;
use App\Services\PedidosWeb\ParametrosCargaService;
use App\Services\PedidosWeb\PedidosWebParameterService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ParametrosCargaServiceTest extends TestCase
{
    #[Test]
    public function forUserExponeFlagsModificaSegunPerfilComercial(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.ModificaPrecioV', 1);
        config()->set('paqsuite_pedidosweb.defaults.ModificaBonArtV', 0);
        config()->set('paqsuite_pedidosweb.defaults.NOeliminaPedido', 1);
        config()->set('paqsuite_pedidosweb.defaults.NOmodificaPedido', 0);
        config()->set('paqsuite_pedidosweb.defaults.CodMotivoCierreExitoso', 7);
        config()->set('paqsuite_pedidosweb.defaults.CargaRecurrente', 1);

        $service = new ParametrosCargaService(
            new CommercialProfileResolver(),
            new PedidosWebParameterService()
        );

        $resultado = $service->forUser($this->buildUser());

        $this->assertSame('vendedor', $resultado['functionalProfile']);
        $this->assertFalse($resultado['modificaBonArt']);
        $this->assertTrue($resultado['modificaPrecio']);
        $this->assertTrue($resultado['noEliminaPedido']);
        $this->assertFalse($resultado['noModificaPedido']);
        $this->assertSame(7, $resultado['codMotivoCierreExitoso']);
        $this->assertTrue($resultado['cargaRecurrente']);
    }

    private function buildUser(): User
    {
        $user = new User();
        $user->codigo = 'vendedor.mvp';

        return $user;
    }
}
