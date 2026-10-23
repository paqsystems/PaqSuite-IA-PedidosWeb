<?php

namespace Tests\Unit\PedidosWeb\Integracion;

use App\Exceptions\IntegracionMaestroAbortException;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroOrchestrator;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroRegistry;
use Illuminate\Container\Container;
use Tests\TestCase;

final class IntegracionMaestroOrchestratorTest extends TestCase
{
    public function testTruncaErroresEnCincuenta(): void
    {
        config()->set('integracion_maestros.max_errores_respuesta', 50);
        config()->set('integracion_maestros.resources.provincias', [
            'handler' => \App\Services\PedidosWeb\Integracion\Maestros\Handlers\IntegracionMaestroStubHandler::class,
            'supports_snapshot' => true,
        ]);

        $orchestrator = app(IntegracionMaestroOrchestrator::class);

        $items = [];
        for ($i = 0; $i < 55; $i++) {
            $items[] = ['rechazar' => true, 'codProvin' => 'X'.$i];
        }

        $result = $orchestrator->process('provincias', [
            'modo' => 'delta',
            'fechaProceso' => '2026-10-21T12:00:00-03:00',
            'items' => $items,
        ]);

        $this->assertSame(55, $result->rechazados);
        $this->assertCount(50, $result->errores);
        $this->assertTrue($result->erroresTruncados);
    }

    public function testRecursoDesconocidoAborta404(): void
    {
        config()->set('integracion_maestros.resources', []);

        $orchestrator = new IntegracionMaestroOrchestrator(new IntegracionMaestroRegistry(new Container));

        $this->expectException(IntegracionMaestroAbortException::class);

        $orchestrator->process('articulos', [
            'modo' => 'delta',
            'fechaProceso' => '2026-10-21T12:00:00-03:00',
            'items' => [],
        ]);
    }
}
