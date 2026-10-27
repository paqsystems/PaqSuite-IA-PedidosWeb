<?php

namespace Tests\Unit\PedidosWeb\Integracion;

use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionItemNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class IntegracionTransaccionItemNormalizerTest extends TestCase
{
    #[DataProvider('historialLegacyProvider')]
    public function testNormalizeHistorialVentasMapeaAliasLegacy(array $input, array $expectedKeys): void
    {
        $normalized = IntegracionTransaccionItemNormalizer::normalize('historial-ventas', $input);

        foreach ($expectedKeys as $key => $value) {
            $this->assertSame($value, $normalized[$key] ?? null);
        }
    }

    public static function historialLegacyProvider(): array
    {
        return [
            'alias tComp nComp' => [
                [
                    'idGva53' => 1,
                    'codCliente' => 'C1',
                    'tComp' => 'FC',
                    'nComp' => '0001',
                    'fechaEmi' => '2026-01-01',
                ],
                [
                    'tipo' => 'FC',
                    'numero' => '0001',
                    'fechaEmision' => '2026-01-01',
                ],
            ],
        ];
    }
}
