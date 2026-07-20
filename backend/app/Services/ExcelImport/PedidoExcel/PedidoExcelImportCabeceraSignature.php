<?php

namespace App\Services\ExcelImport\PedidoExcel;

final class PedidoExcelImportCabeceraSignature
{
    /** @var list<string> */
    public const cabeceraFields = [
        'cod_perfil',
        'cod_condvta',
        'cod_transpor',
        'id_de',
        'cod_lista',
        'nivel',
        'bonif1',
        'bonif2',
        'bonif3',
        'expreso',
        'expreso_dire',
        'fecha_entrega',
        'observaciones',
        'leyenda1',
        'leyenda2',
        'leyenda3',
        'leyenda4',
        'leyenda5',
    ];

    /**
     * @param  array<string, mixed>  $datos
     */
    public static function fromDatos(array $datos): string
    {
        $parts = [];
        foreach (self::cabeceraFields as $field) {
            $parts[] = $field.':'.json_encode(self::normalizeScalar($datos[$field] ?? null));
        }

        return implode('|', $parts);
    }

    public static function normalizeScalar(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    public static function resolveNivelForGroupKey(mixed $nivel): int
    {
        if ($nivel === null || $nivel === '') {
            return 0;
        }

        return (int) $nivel;
    }
}
