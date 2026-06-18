<?php

namespace Tests\Unit\Services\ExcelImport;

use App\Models\PqExcelImportacion;
use App\Services\ExcelImport\ExcelImportErrorsExportService;
use Tests\TestCase;

final class ExcelImportErrorsExportServiceTest extends TestCase
{
    public function testBuildSuggestedFileNameUsesOriginalBaseAndTimestamp(): void
    {
        $importacion = new PqExcelImportacion([
            'archivo_original_nombre' => 'mis articulos.xlsx',
        ]);

        $service = new ExcelImportErrorsExportService();
        $fileName = $service->buildSuggestedFileName($importacion);

        $this->assertMatchesRegularExpression('/^mis_articulos_errores_\d{14}\.xlsx$/', $fileName);
    }
}
