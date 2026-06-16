<?php

namespace App\Services\ExcelImport;

use App\Models\PqExcelImportacion;
use App\Models\PqExcelImportacionFila;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class ExcelImportErrorsExportService
{
    public function buildSuggestedFileName(PqExcelImportacion $importacion): string
    {
        $original = (string) $importacion->archivo_original_nombre;
        $base = pathinfo($original, PATHINFO_FILENAME);
        if ($base === '') {
            $base = 'importacion';
        }

        $safeBase = preg_replace('/[^A-Za-z0-9_-]+/', '_', $base) ?: 'importacion';

        return sprintf('%s_errores_%s.xlsx', $safeBase, now()->format('YmdHis'));
    }

    public function generateSpreadsheet(PqExcelImportacion $importacion): Spreadsheet
    {
        $proceso = $importacion->proceso;
        $campos = $proceso?->campos()
            ->where('activo', true)
            ->orderBy('orden_campo')
            ->get() ?? collect();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = $campos->pluck('nombre_columna_excel')->all();
        $headers[] = 'Errores';
        $headers[] = 'NumeroFilaExcel';

        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }

        $filas = PqExcelImportacionFila::query()
            ->where('id_importacion', $importacion->id)
            ->where('tiene_error', true)
            ->orderBy('numero_fila_excel')
            ->get();

        $rowIndex = 2;
        foreach ($filas as $fila) {
            $datos = json_decode((string) $fila->datos_normalizados_json, true);
            if (! is_array($datos)) {
                $datos = [];
            }

            $colIndex = 1;
            foreach ($campos as $campo) {
                $value = $datos[$campo->nombre_campo_interno] ?? null;
                $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $value);
                $colIndex++;
            }

            $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, (string) $fila->error_importacion);
            $colIndex++;
            $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, (int) $fila->numero_fila_excel);
            $rowIndex++;
        }

        return $spreadsheet;
    }

    public function writeToString(Spreadsheet $spreadsheet): string
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return is_string($content) ? $content : '';
    }
}
