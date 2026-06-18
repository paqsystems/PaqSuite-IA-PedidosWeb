<?php

namespace App\Services\ExcelImport;

use App\Models\PqExcelProcesoCampo;

final class ExcelImportHeaderCommentBuilder
{
    public function __construct(
        private readonly ExcelColumnI18nResolver $columnI18nResolver,
    ) {}

    public function build(
        string $codigoProceso,
        bool $esObligatoriaEstructural,
        ?string $observaciones,
        string $nombreCampoInterno,
        string $locale = 'es'
    ): ?string {
        $observaciones = $observaciones !== null ? trim($observaciones) : '';
        $lineas = [];

        if ($esObligatoriaEstructural) {
            $lineas[] = $this->columnI18nResolver->requiredComment($locale);
        }

        $hint = $this->columnI18nResolver->fieldComment($codigoProceso, $nombreCampoInterno, $locale);
        if ($hint !== null && $hint !== '') {
            $lineas[] = $hint;
        } elseif ($observaciones !== '') {
            $lineas[] = $observaciones;
        }

        if ($lineas === []) {
            return null;
        }

        return implode("\n", $lineas);
    }

    /** @deprecated use build() with locale — kept for tests without i18n proceso */
    public function buildLegacy(bool $esObligatoriaEstructural, ?string $observaciones): ?string
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
