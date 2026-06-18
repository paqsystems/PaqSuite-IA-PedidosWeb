<?php

namespace Tests\Unit\Services\ExcelImport;

use App\Services\ExcelImport\ExcelImportNormalizer;
use Tests\TestCase;

final class ExcelImportNormalizerTest extends TestCase
{
    private ExcelImportNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new ExcelImportNormalizer();
    }

    public function testTrimsWhenSpacesNotMaintained(): void
    {
        $result = $this->normalizer->normalizeCellValue('  hola  ', false, true);

        $this->assertSame('hola', $result['value']);
        $this->assertTrue($result['adjusted']);
    }

    public function testDetectsEmptyRow(): void
    {
        $this->assertTrue($this->normalizer->isRowEmpty([null, '', '   ']));
        $this->assertFalse($this->normalizer->isRowEmpty([null, 'x']));
    }
}
