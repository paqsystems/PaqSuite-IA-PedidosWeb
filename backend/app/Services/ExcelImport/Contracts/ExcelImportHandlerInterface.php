<?php

namespace App\Services\ExcelImport\Contracts;

use App\Services\ExcelImport\Dto\ExcelImportLotContext;
use App\Services\ExcelImport\Dto\ExcelRowError;

interface ExcelImportHandlerInterface
{
    /**
     * @param  array<string, mixed>  $normalizedRow
     * @return ExcelRowError[]
     */
    public function validateBusinessRow(array $normalizedRow, ExcelImportLotContext $ctx): array;

    /**
     * @param  array<string, mixed>  $normalizedRow
     * @return array<string, mixed> fila enriquecida para persistir en staging
     */
    public function processRow(array $normalizedRow, ExcelImportLotContext $ctx): array;
}
