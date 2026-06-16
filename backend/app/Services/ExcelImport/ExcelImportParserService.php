<?php

namespace App\Services\ExcelImport;

use App\Models\PqExcelProceso;
use App\Models\PqExcelProcesoCampo;
use App\Services\ExcelImport\Contracts\ExcelImportHandlerInterface;
use App\Services\ExcelImport\Dto\ExcelImportLotContext;
use App\Services\ExcelImport\Dto\ExcelRowError;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class ExcelImportParserService
{
    public function __construct(
        private readonly ExcelImportNormalizer $normalizer,
    ) {}

    /**
     * @param  Collection<int, PqExcelProcesoCampo>  $campos
     * @return array{
     *   structuralError: ?string,
     *   rows: list<array{
     *     numeroFilaExcel: int,
     *     estadoFila: string,
     *     filaAjustada: bool,
     *     tieneError: bool,
     *     errorImportacion: ?string,
     *     errores: ExcelRowError[],
     *     datosOriginales: array<string, mixed>,
     *     datosNormalizados: array<string, mixed>
     *   }>,
     *   leidas: int,
     *   descartadas: int,
     *   validas: int,
     *   conError: int
     * }
     */
    public function parseSheet(
        Worksheet $sheet,
        PqExcelProceso $proceso,
        Collection $campos,
        bool $mantenerEspacios,
        bool $mantenerCaracteres,
        ExcelImportHandlerInterface $handler,
        ExcelImportLotContext $ctx
    ): array {
        $headerMap = $this->readHeaderMap($sheet);
        $structuralError = $this->validateStructure($headerMap, $campos);
        if ($structuralError !== null) {
            return [
                'structuralError' => $structuralError,
                'rows' => [],
                'leidas' => 0,
                'descartadas' => 0,
                'validas' => 0,
                'conError' => 0,
            ];
        }

        $columnIndexByCampo = $this->buildColumnIndexMap($headerMap, $campos);
        $highestRow = (int) $sheet->getHighestDataRow();
        $rows = [];
        $leidas = 0;
        $descartadas = 0;
        $validas = 0;
        $conError = 0;

        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
            $rawValues = [];
            $hasAnyValue = false;

            foreach ($columnIndexByCampo as $campoInterno => $colIndex) {
                $cell = $sheet->getCellByColumnAndRow($colIndex, $rowIndex);
                $calculated = $cell->getCalculatedValue();
                $rawValues[$campoInterno] = $calculated;
                if ($calculated !== null && (! is_string($calculated) || trim($calculated) !== '')) {
                    $hasAnyValue = true;
                }
            }

            if (! $hasAnyValue || $this->normalizer->isRowEmpty(array_values($rawValues))) {
                $descartadas++;

                continue;
            }

            $leidas++;
            $parsed = $this->parseDataRow(
                $rawValues,
                $campos,
                $mantenerEspacios,
                $mantenerCaracteres,
                $handler,
                $ctx,
                $rowIndex
            );

            if ($parsed['tieneError']) {
                $conError++;
            } else {
                $validas++;
            }

            $rows[] = $parsed;
        }

        return [
            'structuralError' => null,
            'rows' => $rows,
            'leidas' => $leidas,
            'descartadas' => $descartadas,
            'validas' => $validas,
            'conError' => $conError,
        ];
    }

    /**
     * @return array<string, int> nombreColumnaExcel => colIndex (1-based)
     */
    private function readHeaderMap(Worksheet $sheet): array
    {
        $map = [];
        $highestColumn = $sheet->getHighestDataColumn();
        $highestIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        for ($col = 1; $col <= $highestIndex; $col++) {
            $value = $sheet->getCellByColumnAndRow($col, 1)->getCalculatedValue();
            if ($value === null) {
                continue;
            }

            $header = is_string($value) ? $value : (string) $value;
            if (trim($header) === '') {
                continue;
            }

            if (isset($map[$header])) {
                return ['__DUPLICATE__' => 0];
            }

            $map[$header] = $col;
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $headerMap
     * @param  Collection<int, PqExcelProcesoCampo>  $campos
     */
    private function validateStructure(array $headerMap, Collection $campos): ?string
    {
        if (isset($headerMap['__DUPLICATE__'])) {
            return 'excelImport.errorEncabezadoDuplicado';
        }

        foreach ($campos as $campo) {
            if ($campo->es_columna_obligatoria_estructural && ! isset($headerMap[$campo->nombre_columna_excel])) {
                return 'excelImport.errorColumnaEstructuralFaltante';
            }
        }

        return null;
    }

    /**
     * @param  array<string, int>  $headerMap
     * @param  Collection<int, PqExcelProcesoCampo>  $campos
     * @return array<string, int> nombreCampoInterno => colIndex
     */
    private function buildColumnIndexMap(array $headerMap, Collection $campos): array
    {
        $map = [];
        foreach ($campos as $campo) {
            $col = $headerMap[$campo->nombre_columna_excel] ?? null;
            if ($col !== null) {
                $map[$campo->nombre_campo_interno] = $col;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $rawValues
     * @param  Collection<int, PqExcelProcesoCampo>  $campos
     * @return array{
     *   numeroFilaExcel: int,
     *   estadoFila: string,
     *   filaAjustada: bool,
     *   tieneError: bool,
     *   errorImportacion: ?string,
     *   errores: ExcelRowError[],
     *   datosOriginales: array<string, mixed>,
     *   datosNormalizados: array<string, mixed>
     * }
     */
    private function parseDataRow(
        array $rawValues,
        Collection $campos,
        bool $mantenerEspacios,
        bool $mantenerCaracteres,
        ExcelImportHandlerInterface $handler,
        ExcelImportLotContext $ctx,
        int $rowIndex
    ): array {
        $datosOriginales = [];
        $datosNormalizados = [];
        $errores = [];
        $filaAjustada = false;

        foreach ($campos as $campo) {
            $interno = $campo->nombre_campo_interno;
            $raw = $rawValues[$interno] ?? null;
            $datosOriginales[$interno] = $raw;

            $normalized = $this->normalizer->normalizeCellValue($raw, $mantenerEspacios, $mantenerCaracteres);
            if ($normalized['adjusted']) {
                $filaAjustada = true;
            }

            $typed = $this->castValue($normalized['value'], $campo, $errores);
            $datosNormalizados[$interno] = $typed;
        }

        $businessErrors = $handler->validateBusinessRow($datosNormalizados, $ctx);
        foreach ($businessErrors as $businessError) {
            $errores[] = $businessError;
        }

        $tieneError = count($errores) > 0;
        $errorImportacion = $tieneError
            ? implode('; ', array_map(static fn (ExcelRowError $e) => $e->mensajeError, $errores))
            : null;

        return [
            'numeroFilaExcel' => $rowIndex,
            'estadoFila' => $tieneError ? 'con_error' : 'valida',
            'filaAjustada' => $filaAjustada,
            'tieneError' => $tieneError,
            'errorImportacion' => $errorImportacion,
            'errores' => $errores,
            'datosOriginales' => $datosOriginales,
            'datosNormalizados' => $datosNormalizados,
        ];
    }

    /**
     * @param  ExcelRowError[]  $errores
     */
    private function castValue(mixed $value, PqExcelProcesoCampo $campo, array &$errores): mixed
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            if ($campo->es_columna_obligatoria_estructural) {
                $errores[] = new ExcelRowError(
                    'formato',
                    sprintf('%s: valor requerido', $campo->nombre_columna_excel),
                    null,
                    $campo->nombre_campo_interno,
                    $campo->nombre_columna_excel
                );
            }

            return null;
        }

        return match ($campo->tipo_dato) {
            'texto', 'codigo' => $this->castTexto($value, $campo, $errores),
            'entero' => $this->castEntero($value, $campo, $errores),
            'decimal' => $this->castDecimal($value, $campo, $errores),
            'fecha' => $this->castFecha($value, $campo, $errores),
            'booleano' => $this->castBooleano($value, $campo, $errores),
            default => $value,
        };
    }

    /** @param ExcelRowError[] $errores */
    private function castTexto(mixed $value, PqExcelProcesoCampo $campo, array &$errores): ?string
    {
        $text = is_string($value) ? $value : (string) $value;
        if ($campo->largo_maximo !== null && mb_strlen($text) > (int) $campo->largo_maximo) {
            $errores[] = new ExcelRowError(
                'formato',
                sprintf('%s: largo maximo excedido', $campo->nombre_columna_excel),
                null,
                $campo->nombre_campo_interno,
                $campo->nombre_columna_excel
            );
        }

        return $text;
    }

    /** @param ExcelRowError[] $errores */
    private function castEntero(mixed $value, PqExcelProcesoCampo $campo, array &$errores): ?int
    {
        if (is_numeric($value) && (int) $value == $value) {
            return (int) $value;
        }

        $errores[] = new ExcelRowError(
            'formato',
            sprintf('%s: valor entero invalido', $campo->nombre_columna_excel),
            null,
            $campo->nombre_campo_interno,
            $campo->nombre_columna_excel
        );

        return null;
    }

    /** @param ExcelRowError[] $errores */
    private function castDecimal(mixed $value, PqExcelProcesoCampo $campo, array &$errores): ?float
    {
        if (! is_numeric($value)) {
            $errores[] = new ExcelRowError(
                'formato',
                sprintf('%s: valor decimal invalido', $campo->nombre_columna_excel),
                null,
                $campo->nombre_campo_interno,
                $campo->nombre_columna_excel
            );

            return null;
        }

        return (float) $value;
    }

    /** @param ExcelRowError[] $errores */
    private function castFecha(mixed $value, PqExcelProcesoCampo $campo, array &$errores): ?string
    {
        try {
            if (is_numeric($value)) {
                $date = ExcelDate::excelToDateTimeObject((float) $value);

                return Carbon::instance($date)->format('Y-m-d');
            }

            $parsed = Carbon::parse((string) $value);

            return $parsed->format('Y-m-d');
        } catch (\Throwable) {
            $errores[] = new ExcelRowError(
                'formato',
                sprintf('%s: fecha invalida', $campo->nombre_columna_excel),
                null,
                $campo->nombre_campo_interno,
                $campo->nombre_columna_excel
            );

            return null;
        }
    }

    /** @param ExcelRowError[] $errores */
    private function castBooleano(mixed $value, PqExcelProcesoCampo $campo, array &$errores): ?string
    {
        $text = is_string($value) ? strtoupper(trim($value)) : (string) $value;
        $valid = ['0', '1', 'N', 'S', 'VERDADERO', 'FALSO'];
        if (! in_array($text, $valid, true)) {
            $errores[] = new ExcelRowError(
                'formato',
                sprintf('%s: valor booleano invalido', $campo->nombre_columna_excel),
                null,
                $campo->nombre_campo_interno,
                $campo->nombre_columna_excel
            );
        }

        return $text;
    }
}
