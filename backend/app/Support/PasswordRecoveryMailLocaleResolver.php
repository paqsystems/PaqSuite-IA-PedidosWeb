<?php

namespace App\Support;

final class PasswordRecoveryMailLocaleResolver
{
    public function resolve(?string $bodyLocale, ?string $acceptLanguageHeader): string
    {
        $normalizedBodyLocale = LocaleNormalizer::toCatalogCode($bodyLocale);

        if ($normalizedBodyLocale !== null) {
            return $normalizedBodyLocale;
        }

        return LocaleNormalizer::normalize($acceptLanguageHeader, 'es');
    }
}
