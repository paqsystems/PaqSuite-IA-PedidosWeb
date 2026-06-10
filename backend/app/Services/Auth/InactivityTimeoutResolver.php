<?php

namespace App\Services\Auth;

final class InactivityTimeoutResolver
{
    private const DEFAULT_MINUTES = 10;

    public function resolveMinutes(): int
    {
        $configuredValue = config('paqsuite_auth.inactivityTimeoutMinutes', self::DEFAULT_MINUTES);

        if (! is_numeric($configuredValue)) {
            return self::DEFAULT_MINUTES;
        }

        $minutes = (int) $configuredValue;

        return $minutes > 0 ? $minutes : self::DEFAULT_MINUTES;
    }
}
