<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

use App\Services\PedidosWeb\Integracion\Transacciones\ImportationTransaccionDomainAdapter;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PaqSuite\LaravelCore\Importation\ImportationOrchestrator;

final class ProcessImportationMaestroJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly string $resource,
        public readonly string $importacionId,
        public readonly int|string|null $tenantId,
    ) {
    }

    public function handle(
        ImportationOrchestrator $orchestrator,
        ImportationMaestroDomainAdapter $adapter,
        ImportationTransaccionDomainAdapter $transactionAdapter,
        IntegracionMaestroRegistry $maestroRegistry,
        IntegracionTransaccionRegistry $transactionRegistry,
    ): void {
        $domainAdapter = $maestroRegistry->has($this->resource)
            ? $adapter
            : ($transactionRegistry->has($this->resource) ? $transactionAdapter : null);

        if ($domainAdapter === null) {
            throw new \InvalidArgumentException('integracion.recursoDesconocido');
        }

        $orchestrator->process(
            $this->resource,
            $this->importacionId,
            $this->tenantId,
            $domainAdapter,
        );
    }
}
