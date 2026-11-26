<?php

namespace App\Services\PedidosWeb\Integracion\Export;

final class EstadoRangosParser
{
    private const ESTADO_MIN = -1;

    private const ESTADO_MAX = 99;

    private const PAIR_PATTERN = '/^-?\d+:-?\d+$/';

    /**
     * Unifica query repetido y CSV en tokens `desde:hasta`.
     *
     * @return list<string>
     */
    public static function normalize(mixed $raw): array
    {
        if ($raw === null || $raw === '' || $raw === []) {
            return [];
        }

        $items = is_array($raw) ? $raw : [$raw];
        $tokens = [];

        foreach ($items as $item) {
            foreach (explode(',', (string) $item) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $tokens[] = $part;
                }
            }
        }

        return $tokens;
    }

    /**
     * @param  list<string>  $tokens
     * @return list<array{desde: int, hasta: int}>
     */
    public static function parsePairs(array $tokens): array
    {
        $pairs = [];
        $seen = [];

        foreach ($tokens as $token) {
            if (! preg_match(self::PAIR_PATTERN, $token)) {
                throw new \InvalidArgumentException('integracion.export.rangoInvalido');
            }

            [$desdeRaw, $hastaRaw] = explode(':', $token, 2);
            $desde = (int) $desdeRaw;
            $hasta = (int) $hastaRaw;

            if ($desde < self::ESTADO_MIN || $desde > self::ESTADO_MAX
                || $hasta < self::ESTADO_MIN || $hasta > self::ESTADO_MAX
                || $desde > $hasta) {
                throw new \InvalidArgumentException('integracion.export.rangoInvalido');
            }

            $key = $desde.':'.$hasta;
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $pairs[] = ['desde' => $desde, 'hasta' => $hasta];
        }

        return $pairs;
    }
}