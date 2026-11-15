<?php

declare(strict_types=1);

namespace Tests\Unit\Gen\Tasks;

use Carbon\Carbon;
use DateTimeImmutable;
use PaqSuite\LaravelCore\Tasks\Backup\BackupObjectName;
use PHPUnit\Framework\TestCase;

/** CC PQ #22 — sufijos de nombre de backup (3 BOOL) vía forDatabase. */
final class Gen13Cc22BackupObjectNameTest extends TestCase
{
    public function testForDatabaseWithAllThreeFlags(): void
    {
        $startedAt = new DateTimeImmutable('2026-06-15 14:30:45');
        $name = BackupObjectName::forDatabase('Ankas_del_sur-mono', true, $startedAt, true, true);

        // Orden SDK: -s{w} -m{mm} _Ymd_His + .bak (2026-06-15 = lunes → s01)
        $this->assertSame('Ankas_del_sur-mono-s01-m06_20260615_143045.bak', $name);
    }

    public function testForDatabaseOnlyDateTimeWhenOthersFalse(): void
    {
        $startedAt = new DateTimeImmutable('2026-01-07 09:00:00');
        $name = BackupObjectName::forDatabase('db', true, $startedAt, false, false);

        $this->assertSame('db_20260107_090000.bak', $name);
    }

    public function testForDatabaseWeekdayOnly(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-14 12:00:00', 'UTC'));
        $name = BackupObjectName::forDatabase('x', false, null, true, false);
        $this->assertSame('x-s00.bak', $name);
        Carbon::setTestNow();
    }

    public function testForDatabaseNoSuffixes(): void
    {
        $name = BackupObjectName::forDatabase('plain', false, null, false, false);
        $this->assertSame('plain.bak', $name);
    }
}