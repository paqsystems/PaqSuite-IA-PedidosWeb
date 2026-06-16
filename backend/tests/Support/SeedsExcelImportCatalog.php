<?php

namespace Tests\Support;

use Database\Seeders\ExcelImport\ExcelImportCatalogPilotSeeder;

trait SeedsExcelImportCatalog
{
    protected function seedExcelImportCatalog(): void
    {
        $this->seed(ExcelImportCatalogPilotSeeder::class);
    }
}
