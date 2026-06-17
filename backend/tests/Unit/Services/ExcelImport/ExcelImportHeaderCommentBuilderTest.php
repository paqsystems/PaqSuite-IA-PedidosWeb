<?php

namespace Tests\Unit\Services\ExcelImport;

use App\Services\ExcelImport\ExcelColumnI18nResolver;
use App\Services\ExcelImport\ExcelImportHeaderCommentBuilder;
use Tests\TestCase;

final class ExcelImportHeaderCommentBuilderTest extends TestCase
{
    private ExcelImportHeaderCommentBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new ExcelImportHeaderCommentBuilder(new ExcelColumnI18nResolver());
    }

    public function testSinObligatorioNiObservaciones(): void
    {
        $this->assertNull($this->builder->buildLegacy(false, null));
        $this->assertNull($this->builder->buildLegacy(false, '   '));
    }

    public function testSoloObligatorio(): void
    {
        $this->assertSame('OBLIGATORIO', $this->builder->buildLegacy(true, null));
    }

    public function testSoloObservaciones(): void
    {
        $this->assertSame('Debe venir como texto', $this->builder->buildLegacy(false, 'Debe venir como texto'));
    }

    public function testObligatorioYObservaciones(): void
    {
        $this->assertSame(
            "OBLIGATORIO\nDebe venir como texto",
            $this->builder->buildLegacy(true, 'Debe venir como texto')
        );
    }
}
