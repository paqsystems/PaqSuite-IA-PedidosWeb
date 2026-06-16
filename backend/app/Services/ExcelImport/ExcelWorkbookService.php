<?php

namespace App\Services\ExcelImport;

use App\Exceptions\ExcelImportFlowException;
use App\Support\ExcelImportErrorCodes;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class ExcelWorkbookService
{
    public function assertXlsxMagicBytes(string $path): void
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::archivoCorrupto,
                'excelImport.archivoCorrupto',
                422
            );
        }

        $header = fread($handle, 4);
        fclose($handle);

        if ($header !== "PK\x03\x04") {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::formatoInvalido,
                'excelImport.formatoInvalido',
                422
            );
        }
    }

    /**
     * @return string[]
     */
    public function listSheetNames(string $path): array
    {
        $this->assertXlsxMagicBytes($path);

        try {
            $reader = IOFactory::createReader('Xlsx');
            $names = $reader->listWorksheetNames($path);
        } catch (\Throwable) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::archivoCorrupto,
                'excelImport.archivoCorrupto',
                422
            );
        }

        return array_values($names);
    }

    public function loadWorkbook(string $path): Spreadsheet
    {
        $this->assertXlsxMagicBytes($path);

        try {
            return IOFactory::load($path);
        } catch (\Throwable) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::archivoCorrupto,
                'excelImport.archivoCorrupto',
                422
            );
        }
    }

    public function resolveWorksheet(Spreadsheet $spreadsheet, string $hojaSeleccionada): Worksheet
    {
        $sheet = $spreadsheet->getSheetByName($hojaSeleccionada);
        if ($sheet === null) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::hojaNoEncontrada,
                'excelImport.hojaNoEncontrada',
                422
            );
        }

        return $sheet;
    }

    public function estimateDataRowCount(Worksheet $sheet): int
    {
        return max(0, (int) $sheet->getHighestDataRow() - 1);
    }
}
