<?php

namespace App\Services\Seed;

use App\Models\PqMenu;

final class MenuPrerequisiteValidator
{
    public function assertMenuSeedReady(): void
    {
        $requiredProcedimientos = collect(config('paqsuite_mvp.menuItems', []))
            ->pluck('procedimiento')
            ->filter()
            ->values()
            ->all();

        $enabledCount = PqMenu::query()
            ->whereIn('procedimiento', $requiredProcedimientos)
            ->where('enabled', true)
            ->count();

        $requiredCount = count($requiredProcedimientos);

        if ($enabledCount < $requiredCount) {
            throw new \RuntimeException(
                "Prerequisito no cumplido: pq_menus tiene {$enabledCount} items MVP habilitados; se requieren {$requiredCount}. Ejecute php artisan paqsuite:seed-menus-mvp primero."
            );
        }
    }
}
