<?php

namespace Tests\Unit\Support;

use App\Support\ConsultaFechaProcesoFormatter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ConsultaFechaProcesoFormatterTest extends TestCase
{
    #[Test]
    public function formatTruncatesSecondsAndTimezoneSuffix(): void
    {
        $formatted = ConsultaFechaProcesoFormatter::format('2026-06-04 15:30:45');

        $this->assertSame('2026-06-04T15:30', $formatted);
    }

    #[Test]
    public function nowReturnsMinutePrecision(): void
    {
        $formatted = ConsultaFechaProcesoFormatter::now();

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $formatted);
    }
}
