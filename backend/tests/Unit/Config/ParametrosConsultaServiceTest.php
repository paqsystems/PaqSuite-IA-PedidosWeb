<?php

namespace Tests\Unit\Config;

use App\Services\Config\ParametrosConsultaService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ParametrosConsultaServiceTest extends TestCase
{
    #[Test]
    public function listarPorProgramaReturnsEmptyWhenTableMissing(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        $service = new ParametrosConsultaService();
        $resultado = $service->listarPorPrograma('PedidosWeb');

        $this->assertSame([], $resultado['items']);
        $this->assertSame('PedidosWeb', $resultado['programa']);
        $this->assertSame(0, $resultado['total']);
    }
}
