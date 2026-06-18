<?php

namespace App\Services\ExcelImport;

final class ExcelImportNormalizer
{
    /**
     * @return array{value: mixed, adjusted: bool}
     */
    public function normalizeCellValue(
        mixed $rawValue,
        bool $mantenerEspacios,
        bool $mantenerCaracteresEspeciales
    ): array {
        if ($rawValue === null) {
            return ['value' => null, 'adjusted' => false];
        }

        if (! is_string($rawValue)) {
            return ['value' => $rawValue, 'adjusted' => false];
        }

        $adjusted = false;
        $value = $rawValue;

        if (! $mantenerEspacios) {
            $trimmed = trim($value);
            if ($trimmed !== $value) {
                $value = $trimmed;
                $adjusted = true;
            }
        }

        if (! $mantenerCaracteresEspeciales) {
            $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? $value;
            if ($cleaned !== $value) {
                $value = $cleaned;
                $adjusted = true;
            }
        }

        return ['value' => $value, 'adjusted' => $adjusted];
    }

    public function isRowEmpty(array $values): bool
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            return false;
        }

        return true;
    }
}
