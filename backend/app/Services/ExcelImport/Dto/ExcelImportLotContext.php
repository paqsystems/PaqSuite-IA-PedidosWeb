<?php

namespace App\Services\ExcelImport\Dto;

final class ExcelImportLotContext
{
    public function __construct(
        public readonly int $idImportacion,
        public readonly string $guidImportacion,
        public readonly int $idProceso,
        public readonly string $codigoProceso,
        public readonly string $usuarioEjecucion,
    ) {}
}
