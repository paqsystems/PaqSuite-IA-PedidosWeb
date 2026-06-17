<?php

namespace Tests\Unit\Services\ExcelImport;

use App\Models\PqExcelProceso;
use App\Services\ExcelImport\ExcelColumnI18nResolver;
use App\Services\ExcelImport\ExcelImportHeaderCommentBuilder;
use App\Services\ExcelImport\ExcelTemplateService;
use Database\Seeders\ExcelImport\PedidosWebExcelImportCatalogSeeder;
use Tests\TestCase;

final class ExcelTemplateServiceGenerationTest extends TestCase
{
    public function testGeneratePedidoIndividualSpreadsheetDoesNotExhaustMemory(): void
    {
        if (config('database.default') !== 'sqlsrv') {
            $this->markTestSkipped('Requiere SQL Server con catálogo PEDIDO_INDIVIDUAL.');
        }

        $this->seed(PedidosWebExcelImportCatalogSeeder::class);

        $proceso = PqExcelProceso::query()
            ->where('codigo_proceso', 'PEDIDO_INDIVIDUAL')
            ->where('activo', true)
            ->first();

        if ($proceso === null) {
            $this->markTestSkipped('Proceso PEDIDO_INDIVIDUAL no disponible.');
        }

        $service = new ExcelTemplateService(
            new ExcelImportHeaderCommentBuilder(new ExcelColumnI18nResolver()),
            new ExcelColumnI18nResolver(),
        );

        $spreadsheet = $service->generateSpreadsheet($proceso, 'es');
        $bytes = strlen($service->writeSpreadsheetToString($spreadsheet));

        $this->assertGreaterThan(1000, $bytes);
        $this->assertSame('codigo cliente', $spreadsheet->getActiveSheet()->getCell('A1')->getValue());
        $this->assertSame('leyenda 5', $spreadsheet->getActiveSheet()->getCell('W1')->getValue());
    }
}
