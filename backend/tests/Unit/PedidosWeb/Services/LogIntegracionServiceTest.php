<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Services\PedidosWeb\LogIntegracionService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogIntegracionServiceTest extends TestCase
{
    #[Test]
    public function listarNormalizaPaginacionPorDefecto(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('pq_pedidosweb_logs_integracion')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_logs_integracion no disponible.');
        }

        $service = new LogIntegracionService();
        $resultado = $service->listar([]);

        $this->assertSame(1, $resultado['page']);
        $this->assertSame(20, $resultado['page_size']);
        $this->assertArrayHasKey('items', $resultado);
        $this->assertArrayHasKey('metadata', $resultado);
    }
}
