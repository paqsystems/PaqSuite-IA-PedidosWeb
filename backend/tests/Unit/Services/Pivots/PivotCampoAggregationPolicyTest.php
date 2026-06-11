<?php

namespace Tests\Unit\Services\Pivots;

use App\Services\Pivots\PivotCampoAggregationPolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PivotCampoAggregationPolicyTest extends TestCase
{
    #[Test]
    public function stringPermiteCountMinMax(): void
    {
        $this->assertSame(['count', 'min', 'max'], PivotCampoAggregationPolicy::resolveAgregacionesPermitidas('string'));
        $this->assertSame('count', PivotCampoAggregationPolicy::resolveAgregacionDefault('string'));
    }

    #[Test]
    public function numberPermiteTodasLasAgregacionesNumericas(): void
    {
        $this->assertSame(
            ['sum', 'avg', 'min', 'max', 'count'],
            PivotCampoAggregationPolicy::resolveAgregacionesPermitidas('number')
        );
        $this->assertSame('sum', PivotCampoAggregationPolicy::resolveAgregacionDefault('number'));
        $this->assertSame('avg', PivotCampoAggregationPolicy::resolveAgregacionDefault('number', 'avg'));
    }

    #[Test]
    public function normalizeRolesPermitidosAgregaValor(): void
    {
        $this->assertSame(
            ['fila', 'columna', 'valor'],
            PivotCampoAggregationPolicy::normalizeRolesPermitidos(['fila', 'columna'])
        );
    }
}
