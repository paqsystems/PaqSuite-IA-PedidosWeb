<?php

namespace Tests\Feature\Api\PedidosWeb;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Schema;
use Tests\Support\SeedsIntegracionMaestrosFeatureData;
use Tests\TestCase;

/**
 * @group integracion
 */
final class IntegracionTransaccionSaldosFeatureTest extends TestCase
{
    use SeedsIntegracionMaestrosFeatureData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpIntegracionMaestrosFeature();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function testStockItemInvalidoIncrementaErroneos(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_stock')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_stock no disponible.');
        }

        $headers = $this->integrationHeaders();
        $receipt = $this->postImportacionAsync(
            '/api/v1/integracion/transacciones/stock',
            $this->importacionBatch('delta', [
                ['stock' => 1, 'comprometido' => 0],
            ]),
            $headers,
        );

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);

        $this->assertSame('completed', $resultado['estado'] ?? null);
        $this->assertSame(1, $resultado['procesados'] ?? null);
        $this->assertSame(1, $resultado['modificadosErroneos'] ?? null);
    }

    public function testHistorialVentasDeltaVacioCompletaAsync(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_historial_ventas')) {
            $this->markTestSkipped('Tabla historial no disponible.');
        }

        $headers = $this->integrationHeaders();
        $receipt = $this->postImportacionAsync(
            '/api/v1/integracion/transacciones/historial-ventas',
            $this->importacionBatch('delta', []),
            $headers,
        );

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);
        $this->assertSame('completed', $resultado['estado'] ?? null);
    }
}
