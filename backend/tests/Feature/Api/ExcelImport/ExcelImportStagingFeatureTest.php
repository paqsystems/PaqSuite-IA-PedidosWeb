<?php

namespace Tests\Feature\Api\ExcelImport;

use App\Models\PqExcelProceso;
use Laravel\Sanctum\Sanctum;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\ExcelImportFeatureTestCase;

final class ExcelImportStagingFeatureTest extends ExcelImportFeatureTestCase
{
    public function testCreateLotValidFileReturnsListaParaProcesar(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $resultado = $this->createLotFromFile($this->articulosAltaValidFile());

        $this->assertSame('lista_para_procesar', $resultado['estadoImportacion']);
        $this->assertSame(2, $resultado['cantidadFilasValidas']);
        $this->assertSame(0, $resultado['cantidadFilasConError']);
        $this->assertNotEmpty($resultado['guidImportacion']);
    }

    public function testCreateLotStructuralErrorSetsConErrorEstructura(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $resultado = $this->createLotFromFile($this->articulosAltaStructuralErrorFile());

        $this->assertSame('con_error_estructura', $resultado['estadoImportacion']);
        $this->assertSame(0, $resultado['cantidadFilasValidas']);
    }

    public function testCreateLotWithRowErrorCountsMixedRows(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $resultado = $this->createLotFromFile($this->articulosAltaRowErrorFile());

        $this->assertSame('lista_para_procesar', $resultado['estadoImportacion']);
        $this->assertSame(1, $resultado['cantidadFilasValidas']);
        $this->assertSame(1, $resultado['cantidadFilasConError']);
    }

    public function testFilasSoloConErrorFiltersRows(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $lote = $this->createLotFromFile($this->articulosAltaRowErrorFile());
        $guid = (string) $lote['guidImportacion'];

        $this->getJson(
            '/api/v1/excel-import/lotes/'.$guid.'/filas?soloConError=true&pageSize=50',
            $this->tenantHeaders()
        )
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.total', 1)
            ->assertJsonPath('resultado.items.0.tieneError', true);
    }

    public function testFilasValidasReturnsOnlyValidPayload(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $lote = $this->createLotFromFile($this->articulosAltaRowErrorFile());
        $guid = (string) $lote['guidImportacion'];

        $response = $this->getJson(
            '/api/v1/excel-import/lotes/'.$guid.'/filas/validas',
            $this->tenantHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.total', 1)
            ->assertJsonPath('resultado.items.0.datos.codigo', 'ART-OK');
    }

    public function testExportErroresReturnsXlsxWithErrorRowsOnly(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $lote = $this->createLotFromFile($this->articulosAltaRowErrorFile());
        $guid = (string) $lote['guidImportacion'];

        $response = $this->get(
            '/api/v1/excel-import/lotes/'.$guid.'/export-errores',
            $this->tenantHeaders()
        );

        $response->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $response->headers->get('content-type')
        );
        $this->assertStringContainsString(
            '_errores_',
            (string) $response->headers->get('content-disposition')
        );

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($tmp, $response->getContent());
        $sheet = IOFactory::load($tmp)->getActiveSheet();
        @unlink($tmp);

        $this->assertSame('Codigo', $sheet->getCell('A1')->getValue());
        $this->assertSame('Errores', $sheet->getCell('F1')->getValue());
        $this->assertSame('NumeroFilaExcel', $sheet->getCell('G1')->getValue());
        $this->assertSame('ART-BAD', $sheet->getCell('A2')->getValue());
        $this->assertNull($sheet->getCell('A3')->getValue());
    }

    public function testProcessBlockedWhenPermiteSoloValidar(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $lote = $this->createLotFromFile($this->articulosAltaValidFile());
        $guid = (string) $lote['guidImportacion'];

        $this->postJson('/api/v1/excel-import/lotes/'.$guid.'/procesar', [], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'excelImport.processNotAllowed');
    }

    public function testProcessSuccessWhenProcessingEnabled(): void
    {
        PqExcelProceso::query()
            ->where('codigo_proceso', 'ARTICULOS_ALTA')
            ->update([
                'permite_solo_validar' => false,
                'permite_procesamiento_parcial' => false,
            ]);

        Sanctum::actingAs($this->supervisorUser());

        $lote = $this->createLotFromFile($this->articulosAltaValidFile());
        $guid = (string) $lote['guidImportacion'];

        $this->postJson('/api/v1/excel-import/lotes/'.$guid.'/procesar', [], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.estadoImportacion', 'procesada')
            ->assertJsonPath('resultado.cantidadFilasProcesadas', 2);
    }

    public function testHistorialListsCreatedLot(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $lote = $this->createLotFromFile($this->articulosAltaValidFile());
        $guid = (string) $lote['guidImportacion'];

        $this->getJson(
            '/api/v1/excel-import/historial?codigoProceso=ARTICULOS_ALTA&pageSize=50',
            $this->tenantHeaders()
        )
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonFragment(['guidImportacion' => $guid]);
    }
}
