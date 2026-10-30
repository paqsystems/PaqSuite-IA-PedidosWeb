<?php

namespace Tests\Feature\Api\PedidosWeb;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
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

        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();
        $receipt = $this->postTransaccion('stock', 'delta', [
            ['stock' => 1, 'comprometido' => 0],
        ], $headers);

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);

        $this->assertSame('completed', $resultado['estado'] ?? null);
        $this->assertSame(1, $resultado['procesados'] ?? null);
        $this->assertSame(1, $resultado['modificadosErroneos'] ?? null);
    }

    public function testStockCampoProhibidoComprometidoWeb(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_stock')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_stock no disponible.');
        }

        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();
        $receipt = $this->postTransaccion('stock', 'delta', [[
            'codArticulo' => 'ART-TEST',
            'stock' => 10,
            'comprometido' => 0,
            'comprometidoWeb' => 1,
        ]], $headers);

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);
        $this->assertSame(1, $resultado['modificadosErroneos'] ?? null);
        $this->assertSame(
            'integracion.transaccion.campoNoPermitido',
            $resultado['errores'][0]['codigo'] ?? null,
        );
    }

    public function testStockDuplicadoEnMismoLoteRechazaSegundo(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_stock') || ! Schema::hasTable('pq_pedidosweb_articulos')) {
            $this->markTestSkipped('Tablas stock/artículos no disponibles.');
        }

        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();
        $codigo = 'TX-DUP-'.substr(uniqid('', true), -6);

        $this->postMaestro('articulos', 'delta', [
            ['codigo' => $codigo, 'descripcion' => 'Para test duplicado'],
        ], $headers);

        $item = ['codArticulo' => $codigo, 'stock' => 1, 'comprometido' => 0];
        $receipt = $this->postTransaccion('stock', 'delta', [$item, $item], $headers);

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);
        $this->assertSame(2, $resultado['procesados'] ?? null);
        $this->assertSame(1, $resultado['modificados'] ?? null);
        $this->assertSame(1, $resultado['modificadosErroneos'] ?? null);
        $codigosError = array_column($resultado['errores'] ?? [], 'codigo');
        $this->assertContains('integracion.transaccion.duplicado', $codigosError);
    }

    public function testStockDeltaValidoTrasAltaArticuloMaestro(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_stock') || ! Schema::hasTable('pq_pedidosweb_articulos')) {
            $this->markTestSkipped('Tablas stock/artículos no disponibles.');
        }

        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();
        $codigo = trim((string) DB::table('pq_pedidosweb_articulos')->orderByDesc('codigo')->value('codigo'));
        if ($codigo === '') {
            $codigo = 'TX-STOCK-'.substr(uniqid('', true), -8);
            $maestroReceipt = $this->postMaestro('articulos', 'delta', [
                ['codigo' => $codigo, 'descripcion' => 'Artículo transacción E'],
            ], $headers);
            $maestroResultado = $this->getImportacionResultado((string) $maestroReceipt['importacionId'], $headers);
            $this->assertSame('completed', $maestroResultado['estado'] ?? null);
            $this->assertGreaterThanOrEqual(
                1,
                ($maestroResultado['agregados'] ?? 0) + ($maestroResultado['modificados'] ?? 0),
            );
        }

        $receipt = $this->postTransaccion('stock', 'delta', [[
            'codArticulo' => $codigo,
            'stock' => 42,
            'comprometido' => 3,
        ]], $headers);

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);
        $this->assertSame('completed', $resultado['estado'] ?? null);
        $this->assertSame(0, $resultado['modificadosErroneos'] ?? null);
        $this->assertGreaterThanOrEqual(1, $resultado['modificados'] ?? 0);

        $this->assertDatabaseHas('pq_pedidosweb_stock', [
            'cod_articulo' => $codigo,
        ]);
    }

    public function testStockFkFaltanteRechazaItem(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_stock')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_stock no disponible.');
        }

        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();
        $receipt = $this->postTransaccion('stock', 'delta', [[
            'codArticulo' => 'ARTICULO-INEXISTENTE-'.uniqid(),
            'stock' => 1,
            'comprometido' => 0,
        ]], $headers);

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);
        $this->assertSame(1, $resultado['modificadosErroneos'] ?? null);
        $this->assertSame(
            'integracion.transaccion.fkFaltante',
            $resultado['errores'][0]['codigo'] ?? null,
        );
    }

    public function testDeudaItemInvalidoFaltanCampos(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_deuda')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_deuda no disponible.');
        }

        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();
        $receipt = $this->postTransaccion('deuda', 'delta', [
            ['codCliente' => 'C1'],
        ], $headers);

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);
        $this->assertSame(1, $resultado['modificadosErroneos'] ?? null);
    }

    public function testChequesItemInvalidoFaltanCampos(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_cheques')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_cheques no disponible.');
        }

        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();
        $receipt = $this->postTransaccion('cheques', 'delta', [
            ['interno' => '1'],
        ], $headers);

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);
        $this->assertSame(1, $resultado['modificadosErroneos'] ?? null);
    }

    public function testHistorialVentasDeltaVacioCompletaAsync(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_ventadetallada')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_ventadetallada no disponible.');
        }

        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();
        $receipt = $this->postTransaccion('historial-ventas', 'delta', [], $headers);

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);
        $this->assertSame('completed', $resultado['estado'] ?? null);
    }

    public function testHistorialVentasDeltaConAliasLegacyRechazaSinCliente(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_ventadetallada')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_ventadetallada no disponible.');
        }

        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();
        $receipt = $this->postTransaccion('historial-ventas', 'delta', [[
            'idGva53' => 999001,
            'tComp' => 'FC',
            'nComp' => '0001',
            'fechaEmi' => '2026-01-15',
        ]], $headers);

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);
        $this->assertSame(1, $resultado['modificadosErroneos'] ?? null);
    }
}
