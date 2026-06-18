<?php

namespace Tests\Unit\Services\ExcelImport;

use App\Services\ExcelImport\ExcelColumnI18nResolver;
use Tests\TestCase;

final class ExcelColumnI18nResolverTest extends TestCase
{
    private ExcelColumnI18nResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ExcelColumnI18nResolver();
    }

    public function testHeaderLabelUsesSpanishTranslation(): void
    {
        $label = $this->resolver->headerLabel(
            'PEDIDO_INDIVIDUAL',
            'cod_cliente',
            'codigo cliente',
            'es'
        );

        $this->assertSame('codigo cliente', $label);
    }

    public function testHeaderLabelUsesEnglishTranslation(): void
    {
        $label = $this->resolver->headerLabel(
            'PEDIDO_INDIVIDUAL',
            'cod_cliente',
            'codigo cliente',
            'en'
        );

        $this->assertSame('customer code', $label);
    }

    public function testBuildColumnIndexMapMatchesEnglishHeader(): void
    {
        $campo = new \App\Models\PqExcelProcesoCampo([
            'nombre_campo_interno' => 'cod_cliente',
            'nombre_columna_excel' => 'codigo cliente',
            'es_columna_obligatoria_estructural' => true,
        ]);

        $map = $this->resolver->buildColumnIndexMap(
            'PEDIDO_INDIVIDUAL',
            ['customer code' => 1, 'item code' => 2],
            collect([$campo])
        );

        $this->assertSame(1, $map['cod_cliente']);
    }
}
