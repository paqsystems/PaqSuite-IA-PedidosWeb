<?php

namespace App\Support;

/**
 * Normaliza tipo_valor desde filas de PQ_PARAMETROS_GRAL.
 * Contrato compartido con PaqSuite-IA-Tango (ParametrosGralTipoValor).
 */
final class ParametrosGralTipoValor
{
    /** @var list<string> */
    public const VALID = ['S', 'T', 'I', 'D', 'B', 'N'];

    public static function fromRow(object $row): string
    {
        $candidatos = [
            $row->tipo_valor ?? null,
            $row->TIPO_VALOR ?? null,
            $row->Tipo_Valor ?? null,
            $row->Tipo_valor ?? null,
        ];

        foreach ($candidatos as $candidato) {
            if ($candidato === null || $candidato === '') {
                continue;
            }

            $normalizado = strtoupper(trim((string) $candidato));
            if ($normalizado === '') {
                continue;
            }

            $tipo = $normalizado[0];

            return in_array($tipo, self::VALID, true) ? $tipo : 'S';
        }

        return 'S';
    }

    public static function columnaPorTipo(string $tipoValor): string
    {
        return match (strtoupper(substr(trim($tipoValor), 0, 1))) {
            'T' => 'Valor_Text',
            'I' => 'Valor_Int',
            'D' => 'Valor_DateTime',
            'B' => 'Valor_Bool',
            'N' => 'Valor_Decimal',
            default => 'Valor_String',
        };
    }
}
