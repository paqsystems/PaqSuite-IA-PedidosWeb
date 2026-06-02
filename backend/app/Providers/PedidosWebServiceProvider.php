<?php

namespace App\Providers;

use App\Services\PedidosWeb\CalculoTotalesService;
use App\Services\PedidosWeb\ComprobanteCopiaService;
use App\Services\PedidosWeb\ComprobanteMailService;
use App\Services\PedidosWeb\ConsultaListadoService;
use App\Services\PedidosWeb\DashboardOperativoService;
use App\Services\PedidosWeb\LogIntegracionService;
use App\Services\PedidosWeb\ParametrosCargaService;
use App\Services\PedidosWeb\PedidoService;
use App\Services\PedidosWeb\PedidosWebParameterService;
use App\Services\PedidosWeb\PresupuestoCierreService;
use App\Services\PedidosWeb\TratativaService;
use Illuminate\Support\ServiceProvider;

final class PedidosWebServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PedidosWebParameterService::class);
        $this->app->singleton(ParametrosCargaService::class);
        $this->app->singleton(CalculoTotalesService::class);
        $this->app->singleton(PresupuestoCierreService::class);
        $this->app->singleton(ComprobanteCopiaService::class);
        $this->app->singleton(ComprobanteMailService::class);
        $this->app->singleton(PedidoService::class);
        $this->app->singleton(ConsultaListadoService::class);
        $this->app->singleton(LogIntegracionService::class);
        $this->app->singleton(TratativaService::class);
        $this->app->singleton(DashboardOperativoService::class);
    }
}
