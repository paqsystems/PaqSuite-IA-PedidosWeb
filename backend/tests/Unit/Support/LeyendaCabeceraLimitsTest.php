<?php

namespace Tests\Unit\Support;

use App\Support\LeyendaCabeceraLimits;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LeyendaCabeceraLimitsTest extends TestCase
{
    #[Test]
    public function recortarLeyendaCabeceraConservaNullYVacio(): void
    {
        $this->assertNull(LeyendaCabeceraLimits::recortarLeyendaCabecera(null));
        $this->assertNull(LeyendaCabeceraLimits::recortarLeyendaCabecera(''));
        $this->assertNull(LeyendaCabeceraLimits::recortarLeyendaCabecera('   '));
    }

    #[Test]
    public function recortarLeyendaCabeceraConservaHastaSesenta(): void
    {
        $exacto = str_repeat('a', 60);

        $this->assertSame($exacto, LeyendaCabeceraLimits::recortarLeyendaCabecera($exacto));
        $this->assertSame('Entrega', LeyendaCabeceraLimits::recortarLeyendaCabecera('Entrega'));
    }

    #[Test]
    public function recortarLeyendaCabeceraCortaASesenta(): void
    {
        $largo = str_repeat('b', 61);

        $this->assertSame(str_repeat('b', 60), LeyendaCabeceraLimits::recortarLeyendaCabecera($largo));
        $this->assertSame(60, mb_strlen((string) LeyendaCabeceraLimits::recortarLeyendaCabecera($largo)));
    }

    #[Test]
    public function recortarLeyendaCabeceraCuentaCaracteresUnicode(): void
    {
        $texto = str_repeat('ñ', 61);

        $recortado = LeyendaCabeceraLimits::recortarLeyendaCabecera($texto);

        $this->assertSame(str_repeat('ñ', 60), $recortado);
        $this->assertSame(60, mb_strlen((string) $recortado));
    }

    #[Test]
    public function recortarLeyendasEnMapaAplicaSnakeYCamel(): void
    {
        $resultado = LeyendaCabeceraLimits::recortarLeyendasEnMapa([
            'leyenda_1' => str_repeat('x', 61),
            'leyenda2' => str_repeat('y', 61),
            'observaciones' => str_repeat('z', 80),
        ]);

        $this->assertSame(str_repeat('x', 60), $resultado['leyenda_1']);
        $this->assertSame(str_repeat('y', 60), $resultado['leyenda2']);
        $this->assertSame(str_repeat('z', 80), $resultado['observaciones']);
    }
}
