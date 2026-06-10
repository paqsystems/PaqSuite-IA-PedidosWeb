<?php

namespace Tests\Feature;

use App\Models\PqMenu;
use Tests\TestCase;

final class SeedMenusMvpTest extends TestCase
{
    public function testSeedMenusCreatesConfiguredEnabledItems(): void
    {
        $this->artisan('paqsuite:seed-menus-mvp')
            ->assertExitCode(0);

        $procedimientos = collect(config('paqsuite_mvp.menuItems'))->pluck('procedimiento');

        $this->assertSame(
            $procedimientos->count(),
            PqMenu::query()->whereIn('procedimiento', $procedimientos)->where('enabled', true)->count()
        );
    }

    public function testSeedMenusIsIdempotent(): void
    {
        $this->artisan('paqsuite:seed-menus-mvp')->assertExitCode(0);
        $this->artisan('paqsuite:seed-menus-mvp')->assertExitCode(0);

        $procedimientos = collect(config('paqsuite_mvp.menuItems'))->pluck('procedimiento');

        $this->assertSame(
            $procedimientos->count(),
            PqMenu::query()->whereIn('procedimiento', $procedimientos)->count()
        );
    }
}
