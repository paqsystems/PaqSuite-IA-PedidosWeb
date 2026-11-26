<?php

namespace Tests\Unit\Support\PedidosWeb;

use App\Support\PedidosWeb\IntegracionExportFechaRange;
use Tests\TestCase;

final class IntegracionExportFechaRangeTest extends TestCase
{
    public function testDiaCompletoArt(): void
    {
        $bounds = IntegracionExportFechaRange::resolveBounds('2026-06-15', '2026-06-15');

        $this->assertSame('2026-06-15 00:00:00', $bounds['fechaDesde']?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-15 23:59:59', $bounds['fechaHasta']?->format('Y-m-d H:i:s'));
        $this->assertSame('America/Argentina/Buenos_Aires', $bounds['fechaDesde']?->timezone->getName());
    }
}