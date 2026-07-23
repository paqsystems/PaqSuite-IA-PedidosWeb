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
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_06_22_100000_extend_chat_assistant_credentials_for_multiple.php',
            '--force' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_07_03_100001_rename_pq_asistente_ia_tables_transversal.php',
            '--force' => true,
        ]);

        (new ChatAssistantProviderCatalogSeeder())->run();
    }
}
