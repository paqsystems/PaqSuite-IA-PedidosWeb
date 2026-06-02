<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Exceptions\AuthFlowException;
use App\Models\User;
use App\Services\PedidosWeb\ConsultaListadoService;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ConsultaListadoServiceTest extends TestCase
{
    #[Test]
    public function deudaRejectsCodClienteOutsideVisibleUniverse(): void
    {
        $user = new User(['id' => 1]);
        $visibilityGuard = $this->createMock(PedidosWebVisibilityGuard::class);
        $visibilityGuard
            ->expects($this->once())
            ->method('ensureCodClienteVisible')
            ->with($user, 'CLI-AJENO')
            ->willThrowException(new AuthFlowException('404', 'resource.notFound', 404));

        $this->app->instance(PedidosWebVisibilityGuard::class, $visibilityGuard);

        $service = $this->app->make(ConsultaListadoService::class);

        $this->expectException(AuthFlowException::class);

        $service->deuda($user, ['cod_cliente' => 'CLI-AJENO']);
    }
}
