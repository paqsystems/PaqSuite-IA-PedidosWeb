<?php

namespace App\Support\PedidosWeb;

use Illuminate\Support\Carbon;

final class IntegracionExportFechaRange
{
    private const TIMEZONE = 'America/Argentina/Buenos_Aires';

    /**
     * @return array{fechaDesde: ?Carbon, fechaHasta: ?Carbon}
     */
    public static function resolveBounds(?string $fechaDesde, ?string $fechaHasta): array
    {
        $desde = filled($fechaDesde)
            ? Carbon::parse((string) $fechaDesde, self::TIMEZONE)->startOfDay()
            : null;
        $hasta = filled($fechaHasta)
            ? Carbon::parse((string) $fechaHasta, self::TIMEZONE)->endOfDay()
            : null;

        return [
            'fechaDesde' => $desde,
            'fechaHasta' => $hasta,
        ];
    }
}