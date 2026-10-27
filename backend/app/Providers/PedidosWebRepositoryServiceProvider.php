<?php

namespace App\Providers;

use App\Contracts\PedidosWeb\ArticuloRepositoryInterface;
use App\Contracts\PedidosWeb\ClienteRepositoryInterface;
use App\Contracts\PedidosWeb\ConsultaRepositoryInterface;
use App\Contracts\PedidosWeb\PedidoDetalleRepositoryInterface;
use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Repositories\PedidosWeb\ArticuloRepository;
use App\Repositories\PedidosWeb\ClienteRepository;
use App\Repositories\PedidosWeb\ConsultaRepository;
use App\Repositories\PedidosWeb\PedidoDetalleRepository;
use App\Repositories\PedidosWeb\PedidoRepository;
use Illuminate\Support\ServiceProvider;
use App\Services\PedidosWeb\Integracion\Maestros\ImportationMaestroDispatcher;
use PaqSuite\LaravelCore\Importation\Contracts\ImportationDispatcher;
use PaqSuite\LaravelCore\Importation\Contracts\ImportationRepository;
use PaqSuite\LaravelCore\Importation\ImportationOrchestrator;
use PaqSuite\LaravelCore\Importation\StoredProcedureImportationRepository;

final class PedidosWebRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PedidoRepositoryInterface::class, PedidoRepository::class);
        $this->app->bind(PedidoDetalleRepositoryInterface::class, PedidoDetalleRepository::class);
        $this->app->bind(ClienteRepositoryInterface::class, ClienteRepository::class);
        $this->app->bind(ArticuloRepositoryInterface::class, ArticuloRepository::class);
        $this->app->bind(ConsultaRepositoryInterface::class, ConsultaRepository::class);
        $this->app->singleton(ImportationRepository::class, StoredProcedureImportationRepository::class);
        $this->app->singleton(ImportationDispatcher::class, ImportationMaestroDispatcher::class);
        $this->app->singleton(ImportationOrchestrator::class, function ($app): ImportationOrchestrator {
            return new ImportationOrchestrator(
                $app->make(ImportationRepository::class),
                $app->make(ImportationDispatcher::class),
            );
        });
    }
}
