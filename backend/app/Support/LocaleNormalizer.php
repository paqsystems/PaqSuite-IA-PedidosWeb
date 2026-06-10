<?php

namespace App\Support;

final class LocaleNormalizer
{
    public static function supported(): array
    {
        return config('paqsuite_locales.supported', ['es']);
    }

    public static function default(): string
    {
        return (string) config('paqsuite_locales.default', 'es');
    }

    public static function toCatalogCode(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $code = strtolower(explode('-', trim($value))[0]);

        if (! in_array($code, self::supported(), true)) {
            return null;
        }

        return $code;
    }

    public static function normalize(?string $value, ?string $fallback = null): string
    {
        $catalogCode = self::toCatalogCode($value);

        if ($catalogCode !== null) {
            return $catalogCode;
        }

        if ($fallback !== null && in_array($fallback, self::supported(), true)) {
            return $fallback;
        }

        return self::default();
    }
}
