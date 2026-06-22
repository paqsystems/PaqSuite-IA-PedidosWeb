<?php

namespace Tests\Support;

use Database\Seeders\ChatAssistant\ChatAssistantProviderCatalogSeeder;
use Illuminate\Support\Facades\Artisan;

trait SeedsChatAssistantProviderCatalog
{
    protected function seedChatAssistantProviderCatalog(): void
    {
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_06_21_100000_create_pq_pedidosweb_asistente_ia_proveedores_table.php',
            '--force' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_06_21_110000_create_pq_pedidosweb_asistente_ia_credenciales_table.php',
            '--force' => true,
        ]);

        (new ChatAssistantProviderCatalogSeeder())->run();
    }
}
