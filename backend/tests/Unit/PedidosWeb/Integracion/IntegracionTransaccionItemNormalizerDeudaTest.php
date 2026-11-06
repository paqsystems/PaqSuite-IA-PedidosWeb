<?php

namespace Tests\Unit\PedidosWeb\Integracion;

use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionItemNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class IntegracionTransaccionItemNormalizerDeudaTest extends TestCase
{
    #[DataProvider('deudaFechaProvider')]
    public function testNormalizeDeudaPreservaFechasIso(array $input, string $expectedEmis, string $expectedVto): void
    {
        $normalized = IntegracionTransaccionItemNormalizer::normalize('deuda', $input);

        $this->assertSame($expectedEmis, $normalized['fechaEmis'] ?? null);
        $this->assertSame($expectedVto, $normalized['fechaVto'] ?? null);
    }

    public static function deudaFechaProvider(): array
    {
        return [
            'iso con T' => [
                [
                    'fechaEmis' => '2026-03-01T10:00:00',
                    'fechaVto' => '2026-04-01T00:00:00',
                ],
                '2026-03-01T10:00:00',
                '2026-04-01T00:00:00',
            ],
            'fecha corta sin cambio' => [
                [
                    'fechaEmis' => '2026-03-01',
                    'fechaVto' => '2026-04-01',
                ],
                '2026-03-01',
                '2026-04-01',
            ],
        ];
    }
}
