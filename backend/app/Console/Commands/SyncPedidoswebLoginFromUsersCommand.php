<?php

namespace App\Console\Commands;

use App\Services\Seed\PedidoswebLoginFromUsersSyncService;
use Illuminate\Console\Command;

final class SyncPedidoswebLoginFromUsersCommand extends Command
{
    protected $signature = 'paqsuite:sync-pedidosweb-login-from-users';

    protected $description = 'Completa pq_pedidosweb_login con usuarios de users que aun no tienen fila (usuario=codigo)';

    public function handle(PedidoswebLoginFromUsersSyncService $syncService): int
    {
        $result = $syncService->syncMissing();

        $this->info("Insertados: {$result['inserted']}");
        $this->info("Alineados (usuario legacy): {$result['aligned']}");
        $this->info("Omitidos (ya existian o sin codigo): {$result['skipped']}");

        foreach ($result['conflicts'] as $conflict) {
            $this->warn($conflict);
        }

        if ($result['conflicts'] !== []) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
