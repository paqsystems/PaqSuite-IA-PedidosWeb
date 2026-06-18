<?php

namespace App\Services\ExcelImport\Contracts;

use App\Services\ExcelImport\Dto\ExcelImportLotContext;

interface ExcelImportLotAwareHandler extends ExcelImportHandlerInterface
{
    /**
     * Validacion cross-fila post-parse (mismo cliente, cabecera coherente).
     *
     * @param  list<array{
     *   numeroFilaExcel: int,
     *   estadoFila: string,
     *   filaAjustada: bool,
     *   tieneError: bool,
     *   errorImportacion: ?string,
     *   errores: \App\Services\ExcelImport\Dto\ExcelRowError[],
     *   datosOriginales: array<string, mixed>,
     *   datosNormalizados: array<string, mixed>
     * }>  $parsedRows
     * @return list<array<string, mixed>>
     */
    public function validateBusinessLot(array $parsedRows, ExcelImportLotContext $ctx): array;
}
