<?php

namespace Tests\Unit\PedidosWeb\Integracion;

use App\Exceptions\IntegracionMaestroAbortException;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionHandlerInterface;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionItemResult;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionLoteContext;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionOrchestrator;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionRegistry;
use Illuminate\Container\Container;
use Tests\TestCase;

final class IntegracionTransaccionTestHandler implements IntegracionTransaccionHandlerInterface
{
    public static int $calls = 0;

    public function __construct(array $definition = []) {}

    public function supportsSnapshot(): bool
    {
        return false;
    }

    public function keyForItem(array $item): ?string
    {
        return isset($item['codigo']) ? (string) $item['codigo'] : null;
    }

    public function processItem(
        array $item,
        IntegracionTransaccionLoteContext $context,
    ): IntegracionTransaccionItemResult {
        self::$calls++;

        return IntegracionTransaccionItemResult::applied($this->keyForItem($item));
    }

    public function finalizeSnapshot(
        IntegracionTransaccionLoteContext $context,
        array $processedKeys,
    ): array {
        return [];
    }
}

final class IntegracionTransaccionOrchestratorTest extends TestCase
{
    public function testOmiteDuplicadoAntesDeInvocarElSp(): void
    {
        IntegracionTransaccionTestHandler::$calls = 0;
        config()->set('integracion_transacciones.resources.prueba', [
            'handler' => IntegracionTransaccionTestHandler::class,
            'supports_snapshot' => false,
        ]);

        $orchestrator = app(IntegracionTransaccionOrchestrator::class);
        $result = $orchestrator->process('prueba', [
            'modo' => 'delta',
            'fechaProceso' => '2026-10-22T12:00:00-03:00',
            'items' => [
                ['codigo' => 'A', 'valor' => 1],
                ['codigo' => 'A', 'valor' => 2],
            ],
        ]);

        $this->assertSame(1, $result->aplicados);
        $this->assertSame(1, $result->rechazados);
        $this->assertSame('integracion.transaccion.duplicado', $result->errores[0]['codigo']);
        $this->assertSame(1, IntegracionTransaccionTestHandler::$calls);
    }

    public function testSnapshotVacioAborta(): void
    {
        $registry = new IntegracionTransaccionRegistry(new Container);
        $orchestrator = new IntegracionTransaccionOrchestrator($registry);

        $this->expectException(IntegracionMaestroAbortException::class);

        $orchestrator->process('stock', [
            'modo' => 'snapshot',
            'fechaProceso' => '2026-10-22T12:00:00-03:00',
            'items' => [],
        ]);
    }
}
