<?php

namespace Tests\Unit\PedidosWeb\Repositories;

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
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PedidosWebRepositoryBindingTest extends TestCase
{
    #[Test]
    public function containerResuelveBindingsDeRepositories(): void
    {
        $this->assertInstanceOf(PedidoRepository::class, $this->app->make(PedidoRepositoryInterface::class));
        $this->assertInstanceOf(PedidoDetalleRepository::class, $this->app->make(PedidoDetalleRepositoryInterface::class));
        $this->assertInstanceOf(ClienteRepository::class, $this->app->make(ClienteRepositoryInterface::class));
        $this->assertInstanceOf(ArticuloRepository::class, $this->app->make(ArticuloRepositoryInterface::class));
        $this->assertInstanceOf(ConsultaRepository::class, $this->app->make(ConsultaRepositoryInterface::class));
    }
}
