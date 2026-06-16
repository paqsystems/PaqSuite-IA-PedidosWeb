<?php

namespace Tests\Unit\Services\ExcelImport;

use App\Services\ExcelImport\ExcelImportHeaderCommentBuilder;
use App\Services\ExcelImport\ExcelTemplateService;
use Tests\TestCase;

final class ExcelTemplateServiceFileNameTest extends TestCase
{
    public function testBuildSuggestedFileNameIsFixedWithoutDate(): void
    {
        $service = new ExcelTemplateService(new ExcelImportHeaderCommentBuilder());

        $this->assertSame('ARTICULOS_ALTA_plantilla.xlsx', $service->buildSuggestedFileName('ARTICULOS_ALTA'));
        $this->assertSame('PEDIDOS_CARGA_plantilla.xlsx', $service->buildSuggestedFileName('PEDIDOS_CARGA'));
    }
}
