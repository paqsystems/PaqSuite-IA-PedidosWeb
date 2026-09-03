<?php

namespace Tests\Unit\PedidosWeb\Models;

use App\Models\PqPedidoswebClienteDireccionEntrega;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PqPedidoswebClienteDireccionEntregaHabitualTest extends TestCase
{
    #[Test]
    #[DataProvider('habitualTruthyProvider')]
    public function isHabitualFlagAceptaValoresVerdaderos(mixed $value): void
    {
        $this->assertTrue(PqPedidoswebClienteDireccionEntrega::isHabitualFlag($value));
    }

    #[Test]
    #[DataProvider('habitualFalsyProvider')]
    public function isHabitualFlagAceptaValoresFalsos(mixed $value): void
    {
        $this->assertFalse(PqPedidoswebClienteDireccionEntrega::isHabitualFlag($value));
    }

    #[Test]
    public function normalizeHabitualFlagPersisteSN(): void
    {
        $this->assertSame('S', PqPedidoswebClienteDireccionEntrega::normalizeHabitualFlag(true));
        $this->assertSame('N', PqPedidoswebClienteDireccionEntrega::normalizeHabitualFlag(false));
        $this->assertSame('S', PqPedidoswebClienteDireccionEntrega::normalizeHabitualFlag('1'));
        $this->assertSame('N', PqPedidoswebClienteDireccionEntrega::normalizeHabitualFlag('0'));
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function habitualTruthyProvider(): array
    {
        return [
            'bool true' => [true],
            'int 1' => [1],
            'char S' => ['S'],
            'char s' => ['s'],
            'char 1' => ['1'],
            'char Y' => ['Y'],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function habitualFalsyProvider(): array
    {
        return [
            'bool false' => [false],
            'int 0' => [0],
            'char N' => ['N'],
            'char 0' => ['0'],
            'empty' => [''],
            'null' => [null],
        ];
    }
}
