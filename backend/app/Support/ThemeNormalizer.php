<?php

namespace App\Support;

final class ThemeNormalizer
{
    /**
     * @return list<string>
     */
    public static function supported(): array
    {
        return config('paqsuite_themes.supported', ['generic.light']);
    }

    public static function default(): string
    {
        return (string) config('paqsuite_themes.default', 'generic.light');
    }

    public static function toCatalogCode(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = trim($value);
        $aliases = config('paqsuite_themes.legacyAliases', []);

        if (is_array($aliases) && isset($aliases[$normalized])) {
            $normalized = (string) $aliases[$normalized];
        }

        if (! in_array($normalized, self::supported(), true)) {
            return null;
        }

        return $normalized;
    }

    public static function normalize(?string $value, ?string $fallback = null): string
    {
        $catalogCode = self::toCatalogCode($value);

        if ($catalogCode !== null) {
            return $catalogCode;
        }

        if ($fallback !== null) {
            $fallbackCode = self::toCatalogCode($fallback);

            if ($fallbackCode !== null) {
                return $fallbackCode;
            }
        }

        return self::default();
    }
}
