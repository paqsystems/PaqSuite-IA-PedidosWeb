<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

final class IntegracionMaestroPayloadGuard
{
    private const PATTERN = '/\b(DROP|TRUNCATE|ALTER|INSERT\s+INTO\s+pq_)\b/i';

    /**
     * @param  mixed  $value
     */
    public function containsProhibitedSqlFragment(mixed $value): bool
    {
        if (is_string($value)) {
            return (bool) preg_match(self::PATTERN, $value);
        }

        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $nested) {
            if ($this->containsProhibitedSqlFragment($nested)) {
                return true;
            }
        }

        return false;
    }
}
