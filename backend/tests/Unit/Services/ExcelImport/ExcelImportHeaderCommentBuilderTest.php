<?php

namespace Tests\Unit\Services\ExcelImport;

use App\Services\ExcelImport\ExcelImportHeaderCommentBuilder;
use PHPUnit\Framework\TestCase;

final class ExcelImportHeaderCommentBuilderTest extends TestCase
{
    private ExcelImportHeaderCommentBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new ExcelImportHeaderCommentBuilder();
    }

    public function testSinObligatorioNiObservaciones(): void
    {
        $this->assertNull($this->builder->build(false, null));
        $this->assertNull($this->builder->build(false, '   '));
    }

    public function testSoloObligatorio(): void
    {
        $this->assertSame('OBLIGATORIO', $this->builder->build(true, null));
    }

    public function testSoloObservaciones(): void
    {
        $this->assertSame('Debe venir como texto', $this->builder->build(false, 'Debe venir como texto'));
    }

    public function testObligatorioYObservaciones(): void
    {
        $this->assertSame(
            "OBLIGATORIO\nDebe venir como texto",
            $this->builder->build(true, 'Debe venir como texto')
        );
    }
}
