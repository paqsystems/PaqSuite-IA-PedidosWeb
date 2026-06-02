<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Services\PedidosWeb\PedidosWebParameterService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PedidosWebParameterServiceTest extends TestCase
{
    #[Test]
    public function usaDefaultsConfigCuandoLecturaErpEstaDeshabilitada(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.MinutosWeb', 22);
        config()->set('paqsuite_pedidosweb.defaults.Mail_DireccionRemitente', 'erp@paqsuite.local');

        $service = new PedidosWebParameterService();

        $this->assertSame(22, $service->getMinutosWeb());
        $this->assertSame('erp@paqsuite.local', $service->getMailDireccionRemitente());
    }
}
