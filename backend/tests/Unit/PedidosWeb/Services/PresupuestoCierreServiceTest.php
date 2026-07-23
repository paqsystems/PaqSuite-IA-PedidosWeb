<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Exceptions\PedidosWebBusinessException;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\User;
use App\Services\PedidosWeb\PedidosWebParameterService;
use App\Services\PedidosWeb\PedidosWebSchemaBootstrap;
use App\Services\PedidosWeb\PresupuestoCierreService;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PresupuestoCierreServiceTest extends TestCase
{
    #[Test]
    public function cerrarRechazoLanzaNotFoundSiPresupuestoInexistente(): void
    {
        $repository = $this->createMock(PedidoRepositoryInterface::class);
        $repository->method('findByCodPedido')->willReturn(null);

        $service = new PresupuestoCierreService(
            $repository,
            new PedidosWebParameterService(),
            $this->createPermissiveVisibilityGuard(),
            new PedidosWebSchemaBootstrap(),
        );

        $user = new User();
        $user->codigo = 'supervisor.mvp';

        $this->expectException(PedidosWebBusinessException::class);
        $service->cerrarRechazo('PRE-404', 1, null, $user);
    }

    #[Test]
    public function cerrarRechazoRechazaSiEstadoNoEs99(): void
    {
        $presupuesto = new PqPedidoswebPedidoCabecera();
        $presupuesto->estado = 98;

        $repository = $this->createMock(PedidoRepositoryInterface::class);
        $repository->method('findByCodPedido')->willReturn($presupuesto);
        $repository->expects($this->never())->method('updateEstado');

        config()->set('paqsuite_pedidosweb.readFromErp', false);

        $service = new PresupuestoCierreService(
            $repository,
            new PedidosWebParameterService(),
            $this->createPermissiveVisibilityGuard(),
            new PedidosWebSchemaBootstrap(),
        );

        $user = new User();
        $user->codigo = 'supervisor.mvp';

        try {
            $service->cerrarRechazo('PRE-98', 1, 'obs', $user);
            $this->fail('Se esperaba PedidosWebBusinessException');
        } catch (PedidosWebBusinessException $exception) {
            $this->assertSame('business.presupuestoNotEditable', $exception->respuestaKey());
        }
    }

    private function createPermissiveVisibilityGuard(): PedidosWebVisibilityGuard
    {
        $guard = $this->createMock(PedidosWebVisibilityGuard::class);
        $guard->method('ensureComprobanteVisible')->willReturn(new PqPedidoswebPedidoCabecera());

        return $guard;
    }
}
