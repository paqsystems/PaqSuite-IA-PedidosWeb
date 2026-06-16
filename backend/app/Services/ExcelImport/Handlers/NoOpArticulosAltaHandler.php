<?php

namespace App\Services\ExcelImport\Handlers;

use App\Services\ExcelImport\Contracts\ExcelImportHandlerInterface;
use App\Services\ExcelImport\Dto\ExcelImportLotContext;
use App\Services\ExcelImport\Dto\ExcelRowError;

final class NoOpArticulosAltaHandler implements ExcelImportHandlerInterface
{
    public function validateBusinessRow(array $normalizedRow, ExcelImportLotContext $ctx): array
    {
        return [];
    }

    public function processRow(array $normalizedRow, ExcelImportLotContext $ctx): void
    {
        // Stub D1 — sin persistencia de negocio en v1 piloto.
    }
}
