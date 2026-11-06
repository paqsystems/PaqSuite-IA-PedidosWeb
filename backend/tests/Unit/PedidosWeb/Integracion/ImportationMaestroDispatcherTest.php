<?php

namespace Tests\Unit\PedidosWeb\Integracion;

use App\Services\PedidosWeb\Integracion\Maestros\ImportationMaestroDispatcher;
use App\Services\PedidosWeb\Integracion\Maestros\ProcessImportationMaestroJob;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

final class ImportationMaestroDispatcherTest extends TestCase
{
    public function testDispatchesImportationProcessingJob(): void
    {
        Bus::fake();

        app(ImportationMaestroDispatcher::class)->dispatch(
            'articulos',
            'POSTMAN-IMP-QUEUE-001',
            'quento',
        );

        Bus::assertDispatched(
            ProcessImportationMaestroJob::class,
            static fn (ProcessImportationMaestroJob $job): bool =>
                $job->resource === 'articulos'
                && $job->importacionId === 'POSTMAN-IMP-QUEUE-001'
                && $job->tenantId === 'quento',
        );
    }
}
