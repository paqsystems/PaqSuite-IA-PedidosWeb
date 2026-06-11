<?php

namespace App\Services\Pivots;

use App\Exceptions\PivotFlowException;
use App\Support\PivotErrorCodes;

final class PivotStructureValidator
{
    /**
     * @param  array<string, mixed>  $structure
     * @param  array<string, mixed>  $restricciones
     */
    public function validate(array $structure, array $restricciones): void
    {
        $filas = is_array($structure['filas'] ?? null) ? $structure['filas'] : [];
        $columnas = is_array($structure['columnas'] ?? null) ? $structure['columnas'] : [];
        $valores = is_array($structure['valores'] ?? null) ? $structure['valores'] : [];

        $maxFilas = (int) ($restricciones['maximoFilas'] ?? 10);
        $maxColumnas = (int) ($restricciones['maximoColumnas'] ?? 10);
        $maxMetricas = (int) ($restricciones['maximoMetricas'] ?? 15);

        if (count($filas) > $maxFilas || count($columnas) > $maxColumnas || count($valores) > $maxMetricas) {
            throw new PivotFlowException(
                PivotErrorCodes::structureInvalid,
                'pivot.structureInvalid',
                422
            );
        }
    }
}
