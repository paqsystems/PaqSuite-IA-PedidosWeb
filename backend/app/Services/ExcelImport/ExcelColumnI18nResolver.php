<?php

namespace App\Services\ExcelImport;

use App\Models\PqExcelProcesoCampo;
use Illuminate\Support\Collection;

final class ExcelColumnI18nResolver
{
    /** @var list<string> */
    public const supportedLocales = ['es', 'en', 'fr', 'pt', 'it'];

    public function normalizeLocale(?string $locale): string
    {
        $locale = strtolower(trim((string) $locale));
        if ($locale === '') {
            return 'es';
        }

        $primary = explode('-', $locale)[0];

        return in_array($primary, self::supportedLocales, true) ? $primary : 'es';
    }

    public function resolveLocaleFromRequest(?string $acceptLanguage, ?string $queryLocale): string
    {
        if ($queryLocale !== null && trim($queryLocale) !== '') {
            return $this->normalizeLocale($queryLocale);
        }

        if ($acceptLanguage === null || trim($acceptLanguage) === '') {
            return 'es';
        }

        $parts = array_map('trim', explode(',', $acceptLanguage));
        foreach ($parts as $part) {
            $lang = explode(';', $part)[0];

            return $this->normalizeLocale($lang);
        }

        return 'es';
    }

    public function internalFieldToKeySuffix(string $nombreCampoInterno): string
    {
        return lcfirst(str_replace('_', '', ucwords($nombreCampoInterno, '_')));
    }

    public function headerLabel(
        string $codigoProceso,
        string $nombreCampoInterno,
        string $fallbackExcelName,
        string $locale
    ): string {
        $suffix = $this->internalFieldToKeySuffix($nombreCampoInterno);
        $key = "excel_import.column.{$codigoProceso}.{$suffix}";
        $translated = trans($key, [], $this->normalizeLocale($locale));

        return $translated !== $key ? $translated : $fallbackExcelName;
    }

    public function requiredComment(string $locale): string
    {
        $key = 'excel_import.columnComment.required';
        $translated = trans($key, [], $this->normalizeLocale($locale));

        return $translated !== $key ? $translated : 'OBLIGATORIO';
    }

    public function fieldComment(
        string $codigoProceso,
        string $nombreCampoInterno,
        string $locale
    ): ?string {
        $suffix = $this->internalFieldToKeySuffix($nombreCampoInterno);
        $key = "excel_import.columnComment.{$codigoProceso}.{$suffix}";
        $translated = trans($key, [], $this->normalizeLocale($locale));

        return $translated !== $key ? $translated : null;
    }

    /**
     * @param  Collection<int, PqExcelProcesoCampo>  $campos
     * @return list<string>
     */
    public function allHeaderVariants(string $codigoProceso, PqExcelProcesoCampo $campo): array
    {
        $variants = [trim($campo->nombre_columna_excel)];

        foreach (self::supportedLocales as $locale) {
            $variants[] = $this->headerLabel(
                $codigoProceso,
                $campo->nombre_campo_interno,
                $campo->nombre_columna_excel,
                $locale
            );
        }

        return array_values(array_unique(array_filter($variants, static fn (string $value): bool => $value !== '')));
    }

    /**
     * @param  Collection<int, PqExcelProcesoCampo>  $campos
     * @return array<string, int> nombreCampoInterno => colIndex
     */
    public function buildColumnIndexMap(string $codigoProceso, array $headerMap, Collection $campos): array
    {
        $map = [];

        foreach ($campos as $campo) {
            foreach ($this->allHeaderVariants($codigoProceso, $campo) as $header) {
                if (isset($headerMap[$header])) {
                    $map[$campo->nombre_campo_interno] = $headerMap[$header];
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * @param  Collection<int, PqExcelProcesoCampo>  $campos
     */
    public function validateStructure(string $codigoProceso, array $headerMap, Collection $campos): ?string
    {
        if (isset($headerMap['__DUPLICATE__'])) {
            return 'excelImport.errorEncabezadoDuplicado';
        }

        $columnIndexByCampo = $this->buildColumnIndexMap($codigoProceso, $headerMap, $campos);

        foreach ($campos as $campo) {
            if ($campo->es_columna_obligatoria_estructural && ! isset($columnIndexByCampo[$campo->nombre_campo_interno])) {
                return 'excelImport.errorColumnaEstructuralFaltante';
            }
        }

        return null;
    }
}
