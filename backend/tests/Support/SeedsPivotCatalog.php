<?php

namespace Tests\Support;

use Database\Seeders\Pivots\PivotCatalogPilotSeeder;
use Illuminate\Support\Facades\Artisan;

trait SeedsPivotCatalog
{
    protected function seedPivotCatalog(): void
    {
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_06_11_100000_create_pq_pivots_catalog_tables.php',
            '--force' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_06_11_110000_create_pq_pivots_config_tables.php',
            '--force' => true,
        ]);

        (new PivotCatalogPilotSeeder())->run();
    }
}
