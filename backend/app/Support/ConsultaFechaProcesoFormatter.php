<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class ConsultaFechaProcesoFormatter
{
    public static function now(): string
    {
        return now()->startOfMinute()->format('Y-m-d\TH:i');
    }

    public static function format(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->startOfMinute()->format('Y-m-d\TH:i');
    }
}
