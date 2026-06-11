<?php

namespace Tests\Unit\Services\Pivots;

use App\Services\Pivots\PivotCampoFormatPolicy;
use PHPUnit\Framework\TestCase;

final class PivotCampoFormatPolicyTest extends TestCase
{
    public function testNumberUsesDecimalFormat(): void
    {
        $formato = PivotCampoFormatPolicy::resolveFormato(null, 'number');

        $this->assertSame(['format' => '#,##0.00'], $formato);
    }

    public function testNumberOverridesCustomFormat(): void
    {
        $formato = PivotCampoFormatPolicy::resolveFormato(['format' => '#,##0'], 'number');

        $this->assertSame(['format' => '#,##0.00'], $formato);
    }

    public function testStringPreservesFormato(): void
    {
        $custom = ['format' => '@'];

        $this->assertSame($custom, PivotCampoFormatPolicy::resolveFormato($custom, 'string'));
    }

    public function testStringWithoutFormatoReturnsNull(): void
    {
        $this->assertNull(PivotCampoFormatPolicy::resolveFormato(null, 'string'));
    }
}
