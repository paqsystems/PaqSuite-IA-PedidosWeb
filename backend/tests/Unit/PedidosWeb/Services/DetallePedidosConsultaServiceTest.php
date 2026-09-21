<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Models\PqPedidoswebArticulo;
use App\Models\PqPedidoswebPedidoDetalle;
use App\Services\PedidosWeb\DetallePedidosConsultaService;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

final class DetallePedidosConsultaServiceTest extends TestCase
{
    #[Test]
    public function resolveEspecialArticuloDevuelveAsteriscoCuandoEsTrue(): void
    {
        $articulo = new PqPedidoswebArticulo(['especial' => true]);
        $detalle = new PqPedidoswebPedidoDetalle;
        $detalle->setRelation('articulo', $articulo);

        $this->assertSame('*', $this->invokeResolveEspecialArticulo($detalle));
    }

    #[Test]
    public function resolveEspecialArticuloDevuelveVacioCuandoEsFalse(): void
    {
        $articulo = new PqPedidoswebArticulo(['especial' => false]);
        $detalle = new PqPedidoswebPedidoDetalle;
        $detalle->setRelation('articulo', $articulo);

        $this->assertSame('', $this->invokeResolveEspecialArticulo($detalle));
    }

    #[Test]
    public function resolveEspecialArticuloDevuelveVacioSinArticuloRelacionado(): void
    {
        $detalle = new PqPedidoswebPedidoDetalle;

        $this->assertSame('', $this->invokeResolveEspecialArticulo($detalle));
    }

    private function invokeResolveEspecialArticulo(PqPedidoswebPedidoDetalle $detalle): string
    {
        $service = $this->app->make(DetallePedidosConsultaService::class);
        $method = new ReflectionMethod(DetallePedidosConsultaService::class, 'resolveEspecialArticulo');
        $method->setAccessible(true);

        return (string) $method->invoke($service, $detalle);
    }
}
