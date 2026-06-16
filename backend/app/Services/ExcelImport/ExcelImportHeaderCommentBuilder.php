<?php

namespace App\Services\ExcelImport;

final class ExcelImportHeaderCommentBuilder
{
    public function build(bool $esObligatoriaEstructural, ?string $observaciones): ?string
    {
        $observaciones = $observaciones !== null ? trim($observaciones) : '';
        $lineas = [];

        if ($esObligatoriaEstructural) {
            $lineas[] = 'OBLIGATORIO';
        }

        if ($observaciones !== '') {
            $lineas[] = $observaciones;
        }

        if ($lineas === []) {
            return null;
        }

        return implode("\n", $lineas);
    }
}
