<?php

namespace Tests\Unit\Support;

use App\Support\ConsultaPaginacion;
use PHPUnit\Framework\TestCase;

final class ConsultaPaginacionTest extends TestCase
{
    public function testResolvePageSizeDefaultsAndCaps(): void
    {
        $this->assertSame(20, ConsultaPaginacion::resolvePageSize(null));
        $this->assertSame(20, ConsultaPaginacion::resolvePageSize(0));
        $this->assertSame(1, ConsultaPaginacion::resolvePageSize(1));
        $this->assertSame(1000, ConsultaPaginacion::resolvePageSize(1000));
        $this->assertSame(1000, ConsultaPaginacion::resolvePageSize(5000));
    }

    public function testResolvePageMinimumOne(): void
    {
        $this->assertSame(1, ConsultaPaginacion::resolvePage(null));
        $this->assertSame(1, ConsultaPaginacion::resolvePage(0));
        $this->assertSame(3, ConsultaPaginacion::resolvePage(3));
    }
}
