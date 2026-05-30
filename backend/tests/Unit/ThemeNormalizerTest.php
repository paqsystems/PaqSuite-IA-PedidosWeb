<?php

namespace Tests\Unit;

use App\Support\ThemeNormalizer;
use Tests\TestCase;

final class ThemeNormalizerTest extends TestCase
{
    public function testNormalizeLegacyLightAlias(): void
    {
        $this->assertSame('generic.light', ThemeNormalizer::normalize('light'));
    }

    public function testNormalizeLegacyDarkAlias(): void
    {
        $this->assertSame('generic.dark', ThemeNormalizer::normalize('dark'));
    }

    public function testInvalidThemeFallsBackToDefault(): void
    {
        $this->assertSame('generic.light', ThemeNormalizer::normalize('xx'));
    }

    public function testNullThemeFallsBackToDefault(): void
    {
        $this->assertSame('generic.light', ThemeNormalizer::normalize(null));
    }

    public function testToCatalogCodeReturnsNullForUnsupportedTheme(): void
    {
        $this->assertNull(ThemeNormalizer::toCatalogCode('material.blue.light'));
    }
}
