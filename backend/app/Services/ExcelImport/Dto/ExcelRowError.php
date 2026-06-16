<?php

namespace App\Services\ExcelImport\Dto;

final class ExcelRowError
{
    public function __construct(
        public readonly string $tipoError,
        public readonly string $mensajeError,
        public readonly ?string $codigoError = null,
        public readonly ?string $nombreCampoInterno = null,
        public readonly ?string $nombreColumnaExcel = null,
    ) {}
}
