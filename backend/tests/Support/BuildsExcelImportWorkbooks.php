<?php

namespace Tests\Support;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait BuildsExcelImportWorkbooks
{
    /**
     * @param  list<list<mixed>>  $dataRows
     */
    protected function buildExcelImportUploadedFile(
        array $headers,
        array $dataRows,
        string $originalName = 'articulos_test.xlsx',
        string $sheetName = 'Hoja1',
    ): UploadedFile {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);

        foreach ($headers as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }

        foreach ($dataRows as $rowOffset => $rowValues) {
            foreach ($rowValues as $colIndex => $value) {
                $sheet->setCellValue([$colIndex + 1, $rowOffset + 2], $value);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'excel_import_test_');
        if ($path === false) {
            $this->fail('No se pudo crear archivo temporal para test Excel.');
        }

        $targetPath = $path.'.xlsx';
        @unlink($path);
        (new Xlsx($spreadsheet))->save($targetPath);

        return new UploadedFile(
            $targetPath,
            $originalName,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    /**
     * @return list<string>
     */
    protected function articulosAltaHeaders(): array
    {
        return ['Codigo', 'Descripcion', 'Rubro', 'Precio', 'Fecha Alta'];
    }

    protected function articulosAltaValidFile(): UploadedFile
    {
        return $this->buildExcelImportUploadedFile(
            $this->articulosAltaHeaders(),
            [
                ['ART-001', 'Articulo valido', 'GEN', 10.5, '2026-01-15'],
                ['ART-002', 'Otro articulo', 'GEN', 20, null],
            ]
        );
    }

    protected function articulosAltaStructuralErrorFile(): UploadedFile
    {
        return $this->buildExcelImportUploadedFile(
            ['Descripcion', 'Rubro', 'Precio', 'Fecha Alta'],
            [
                ['Sin codigo', 'GEN', 10, null],
            ]
        );
    }

    protected function articulosAltaRowErrorFile(): UploadedFile
    {
        return $this->buildExcelImportUploadedFile(
            $this->articulosAltaHeaders(),
            [
                ['ART-OK', 'Fila valida', 'GEN', 5, '2026-02-01'],
                ['ART-BAD', 'Fila invalida', 'GEN', 5, 'fecha-invalida'],
            ]
        );
    }
}
