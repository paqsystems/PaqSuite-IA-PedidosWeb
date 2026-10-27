<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

use Illuminate\Support\Facades\Bus;
use PaqSuite\LaravelCore\Importation\Contracts\ImportationDispatcher;

final class ImportationMaestroDispatcher implements ImportationDispatcher
{
    public function dispatch(
        string $resource,
        string $importacionId,
        int|string|null $tenantId,
    ): void {
        Bus::dispatch(new ProcessImportationMaestroJob($resource, $importacionId, $tenantId));
    }
}
