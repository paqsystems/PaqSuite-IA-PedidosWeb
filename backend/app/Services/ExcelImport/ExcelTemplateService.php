<?php

namespace App\Services\ExcelImport;

use App\Exceptions\ExcelImportFlowException;
use App\Models\PqExcelProceso;
use App\Models\PqExcelProcesoCampo;
use App\Support\ExcelImportErrorCodes;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class ExcelTemplateService
{
    private const headerArgb = 'FF4472C4';

    /** Filas con formato de columna en plantilla (alineado a validación booleana). */
    private const dataRowEnd = 1000;

    public function __construct(
        private readonly ExcelImportHeaderCommentBuilder $headerCommentBuilder,
        private readonly ExcelColumnI18nResolver $columnI18nResolver,
    ) {}

    public function findActiveProceso(string $codigoProceso): PqExcelProceso
    {
        $proceso = PqExcelProceso::query()
            ->where('codigo_proceso', $codigoProceso)
            ->where('activo', true)
            ->first();

        if ($proceso === null) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::procesoNotFound,
                'excelImport.procesoNotFound',
                404
            );
        }

        return $proceso;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildProcesoMetadata(PqExcelProceso $proceso): array
    {
        return [
            'codigoProceso' => $proceso->codigo_proceso,
            'nombreProceso' => $proceso->nombre_proceso,
            'generaPlantilla' => (bool) $proceso->genera_plantilla,
            'permiteProcesamientoParcial' => (bool) $proceso->permite_procesamiento_parcial,
            'permiteSoloValidar' => (bool) $proceso->permite_solo_validar,
            'mantenerEspaciosEnBlancoDefault' => (bool) $proceso->mantener_espacios_en_blanco_default,
            'mantenerCaracteresEspecialesDefault' => (bool) $proceso->mantener_caracteres_especiales_default,
            'procedimientoHost' => $proceso->procedimiento_host,
        ];
    }

    public function buildSuggestedFileName(string $codigoProceso): string
    {
        $safeCodigo = preg_replace('/[^A-Za-z0-9_-]+/', '_', $codigoProceso) ?: 'proceso';

        return sprintf('%s_plantilla.xlsx', $safeCodigo);
    }

    public function generateSpreadsheet(PqExcelProceso $proceso, string $locale = 'es'): Spreadsheet
    {
        $locale = $this->columnI18nResolver->normalizeLocale($locale);
        if (! $proceso->genera_plantilla) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::plantillaNoDisponible,
                'excelImport.plantillaNoDisponible',
                404
            );
        }

        $campos = $proceso->campos()
            ->where('activo', true)
            ->orderBy('orden_campo')
            ->get();

        if ($campos->isEmpty()) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::sinColumnasActivas,
                'excelImport.sinColumnasActivas',
                422
            );
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($campos as $index => $campo) {
            $columnIndex = $index + 1;
            $columnLetter = $this->columnLetter($columnIndex);
            $cellRef = $columnLetter.'1';

            $sheet->setCellValue(
                $cellRef,
                $this->columnI18nResolver->headerLabel(
                    (string) $proceso->codigo_proceso,
                    (string) $campo->nombre_campo_interno,
                    (string) $campo->nombre_columna_excel,
                    $locale
                )
            );
            $this->applyHeaderStyle($sheet, $cellRef);
            $this->applyHeaderComment($sheet, $cellRef, $campo, (string) $proceso->codigo_proceso, $locale);
            $this->applyColumnFormat($sheet, $columnLetter, $campo, (string) $proceso->formato_booleano_plantilla);
        }

        return $spreadsheet;
    }

    public function writeSpreadsheetToString(Spreadsheet $spreadsheet): string
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    private function applyHeaderStyle(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $cellRef): void
    {
        $style = $sheet->getStyle($cellRef);
        $style->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $style->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB(self::headerArgb);
    }

    private function applyHeaderComment(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        string $cellRef,
        PqExcelProcesoCampo $campo,
        string $codigoProceso,
        string $locale
    ): void {
        $texto = $this->headerCommentBuilder->build(
            $codigoProceso,
            (bool) $campo->es_columna_obligatoria_estructural,
            $campo->observaciones,
            (string) $campo->nombre_campo_interno,
            $locale
        );

        if ($texto === null) {
            return;
        }

        $comment = $sheet->getComment($cellRef);
        $comment->getText()->createTextRun($texto);
        $comment->setWidth('200pt');
        $comment->setHeight('80pt');
    }

    private function applyColumnFormat(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        string $columnLetter,
        PqExcelProcesoCampo $campo,
        string $formatoBooleanoPlantilla
    ): void {
        $range = sprintf('%s2:%s%d', $columnLetter, $columnLetter, self::dataRowEnd);
        $style = $sheet->getStyle($range);

        switch ($campo->tipo_dato) {
            case 'texto':
            case 'codigo':
                $style->getNumberFormat()->setFormatCode('@');
                break;
            case 'entero':
                $style->getNumberFormat()->setFormatCode('0');
                break;
            case 'decimal':
                $decimales = max(0, (int) ($campo->cantidad_decimales ?? 2));
                $style->getNumberFormat()->setFormatCode('0.'.str_repeat('0', $decimales));
                break;
            case 'fecha':
                $style->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                break;
            case 'booleano':
                $style->getNumberFormat()->setFormatCode('@');
                $this->applyBooleanListValidation($sheet, $columnLetter, $formatoBooleanoPlantilla);
                break;
        }
    }

    private function applyBooleanListValidation(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        string $columnLetter,
        string $formatoBooleanoPlantilla
    ): void {
        $valores = match ($formatoBooleanoPlantilla) {
            'N_S' => ['N', 'S'],
            'VERDADERO_FALSO' => ['VERDADERO', 'FALSO'],
            default => ['0', '1'],
        };

        $formula = '"'.implode(',', $valores).'"';

        for ($row = 2; $row <= 1000; $row++) {
            $cell = $sheet->getCell($columnLetter.$row);
            $validation = $cell->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1($formula);
        }
    }

    private function columnLetter(int $columnIndex): string
    {
        $letter = '';
        while ($columnIndex > 0) {
            $modulo = ($columnIndex - 1) % 26;
            $letter = chr(65 + $modulo).$letter;
            $columnIndex = intdiv($columnIndex - 1, 26);
        }

        return $letter;
    }
}
