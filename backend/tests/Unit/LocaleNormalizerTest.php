<?php

namespace Tests\Unit;

use App\Support\LocaleNormalizer;
use Tests\TestCase;

final class LocaleNormalizerTest extends TestCase
{
    public function testNormalizesBcp47ToCatalogCode(): void
    {
        $this->assertSame('es', LocaleNormalizer::normalize('es-AR'));
        $this->assertSame('en', LocaleNormalizer::normalize('en-US'));
    }

    public function testInvalidLocaleFallsBackToDefault(): void
    {
        $this->assertSame('es', LocaleNormalizer::normalize('xx'));
        $this->assertSame('es', LocaleNormalizer::normalize(null));
    }

    public function testToCatalogCodeReturnsNullForUnsupported(): void
    {
        $this->assertNull(LocaleNormalizer::toCatalogCode('xx'));
        $this->assertSame('it', LocaleNormalizer::toCatalogCode('it'));
    }
}
