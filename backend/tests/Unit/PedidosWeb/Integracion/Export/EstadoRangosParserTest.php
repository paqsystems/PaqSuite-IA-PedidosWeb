<?php

namespace Tests\Unit\PedidosWeb\Integracion\Export;

use App\Services\PedidosWeb\Integracion\Export\EstadoRangosParser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class EstadoRangosParserTest extends TestCase
{
    public function testNormalizeRepetidoYCsv(): void
    {
        $tokens = EstadoRangosParser::normalize(['0:0,1:2', '98:99']);

        $this->assertSame(['0:0', '1:2', '98:99'], $tokens);
    }

    public function testParsePairsDedup(): void
    {
        $pairs = EstadoRangosParser::parsePairs(['0:0', '0:0', '-1:-1']);

        $this->assertCount(2, $pairs);
        $this->assertSame(['desde' => 0, 'hasta' => 0], $pairs[0]);
        $this->assertSame(['desde' => -1, 'hasta' => -1], $pairs[1]);
    }

    #[DataProvider('paresInvalidosProvider')]
    public function testParsePairsInvalidos(string $token): void
    {
        $this->expectException(\InvalidArgumentException::class);
        EstadoRangosParser::parsePairs([$token]);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function paresInvalidosProvider(): array
    {
        return [
            'desde mayor' => ['1:0'],
            'formato' => ['abc'],
            'fuera de rango' => ['0:100'],
        ];
    }
}