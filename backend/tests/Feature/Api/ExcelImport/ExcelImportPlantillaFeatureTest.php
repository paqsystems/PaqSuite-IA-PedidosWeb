<?php

namespace Tests\Feature\Api\ExcelImport;

use App\Models\PqExcelProceso;
use Laravel\Sanctum\Sanctum;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\ExcelImportFeatureTestCase;

final class ExcelImportPlantillaFeatureTest extends ExcelImportFeatureTestCase
{
    public function testMetadataRequiresAuth(): void
    {
        $this->getJson('/api/v1/excel-import/procesos/ARTICULOS_ALTA', $this->tenantHeaders())
            ->assertUnauthorized();
    }

    public function testMetadataReturnsProceso(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $this->getJson('/api/v1/excel-import/procesos/ARTICULOS_ALTA', $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.codigoProceso', 'ARTICULOS_ALTA')
            ->assertJsonPath('resultado.generaPlantilla', true);
    }

    public function testPlantillaDownloadReturnsXlsxWithHeadersAndObligatorioComment(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $response = $this->get('/api/v1/excel-import/procesos/ARTICULOS_ALTA/plantilla', array_merge(
            $this->tenantHeaders(),
            ['Accept-Language' => 'es']
        ));

        $response->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $response->headers->get('content-type')
        );
        $this->assertStringContainsString(
            'ARTICULOS_ALTA_plantilla.xlsx',
            (string) $response->headers->get('content-disposition')
        );

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($tmp, $response->getContent());
        $sheet = IOFactory::load($tmp)->getActiveSheet();
        @unlink($tmp);

        $this->assertSame('Codigo', $sheet->getCell('A1')->getValue());
        $this->assertSame('Descripcion', $sheet->getCell('B1')->getValue());
        $comment = $sheet->getComment('A1')->getText()->getPlainText();
        $this->assertStringContainsString('OBLIGATORIO', $comment);
        $this->assertStringContainsString('Debe venir como texto', $comment);
    }

    public function testPlantillaNotAvailableWhenGeneraPlantillaDisabled(): void
    {
        PqExcelProceso::query()
            ->where('codigo_proceso', 'ARTICULOS_ALTA')
            ->update(['genera_plantilla' => false]);

        Sanctum::actingAs($this->supervisorUser());

        $this->getJson('/api/v1/excel-import/procesos/ARTICULOS_ALTA/plantilla', $this->tenantHeaders())
            ->assertNotFound()
            ->assertJsonPath('error', 4008);
    }

    public function testEpicDisabledReturns404(): void
    {
        config()->set('paqsuite_mvp.excelImportEnabled', false);
        Sanctum::actingAs($this->supervisorUser());

        $this->getJson('/api/v1/excel-import/procesos/ARTICULOS_ALTA', $this->tenantHeaders())
            ->assertNotFound()
            ->assertJsonPath('error', 4010);
    }
}
