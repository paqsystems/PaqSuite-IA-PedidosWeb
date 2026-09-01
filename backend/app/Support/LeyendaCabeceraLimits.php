<?php

namespace App\Support;

final class LeyendaCabeceraLimits
{
    public const MAX_CARACTERES = 60;

    public static function recortarLeyendaCabecera(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);
        if ($texto === '') {
            return null;
        }

        if (mb_strlen($texto) <= self::MAX_CARACTERES) {
            return $texto;
        }

        return mb_substr($texto, 0, self::MAX_CARACTERES);
    }

    /**
     * Recorta `leyenda_1`…`leyenda_5` y `leyenda1`…`leyenda5` si están presentes.
     *
     * @param  array<string, mixed>  $campos
     * @return array<string, mixed>
     */
    public static function recortarLeyendasEnMapa(array $campos): array
    {
        for ($numero = 1; $numero <= 5; $numero++) {
            $snake = 'leyenda_'.$numero;
            $camel = 'leyenda'.$numero;

            if (array_key_exists($snake, $campos)) {
                $campos[$snake] = self::recortarLeyendaCabecera($campos[$snake]);
            }

            if (array_key_exists($camel, $campos)) {
                $campos[$camel] = self::recortarLeyendaCabecera($campos[$camel]);
            }
        }

        return $campos;
    }
}
