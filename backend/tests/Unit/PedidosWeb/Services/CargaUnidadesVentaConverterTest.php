<?php

declare(strict_types=1);

namespace Tests\Unit\PedidosWeb\Services;

use App\Services\PedidosWeb\CargaUnidadesVentaConverter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CargaUnidadesVentaConverterTest extends TestCase
{
    #[Test]
    public function resolveEquivalenciaVentasTrataCeroYNullComoUno(): void
    {
        $this->assertSame(1.0, CargaUnidadesVentaConverter::resolveEquivalenciaVentas(null));
        $this->assertSame(1.0, CargaUnidadesVentaConverter::resolveEquivalenciaVentas(0));
        $this->assertSame(2.5, CargaUnidadesVentaConverter::resolveEquivalenciaVentas(2.5));
    }

    #[Test]
    public function fromCantidadUsuarioModoStockDerivaCantidadVenta(): void
    {
        $pair = CargaUnidadesVentaConverter::fromCantidadUsuario(10, 2, false);

        $this->assertSame(10.0, $pair['cantidad']);
        $this->assertSame(5.0, $pair['cantidad_venta']);
    }

    #[Test]
    public function fromCantidadUsuarioModoVentaDerivaCantidad(): void
    {
        $pair = CargaUnidadesVentaConverter::fromCantidadUsuario(4, 2.5, true);

        $this->assertSame(4.0, $pair['cantidad_venta']);
        $this->assertSame(10.0, $pair['cantidad']);
    }

    #[Test]
    public function cantidadVisibleParaUsuarioRespectaParametro(): void
    {
        $this->assertSame(10.0, CargaUnidadesVentaConverter::cantidadVisibleParaUsuario(10, 4, false));
        $this->assertSame(4.0, CargaUnidadesVentaConverter::cantidadVisibleParaUsuario(10, 4, true));
    }

    #[Test]
    public function ensurePairConservaAmbosCamposCuandoVienenCompletos(): void
    {
        $pair = CargaUnidadesVentaConverter::ensurePair([
            'cantidad' => 10,
            'cantidad_venta' => 4,
        ], 2, true);

        $this->assertSame(10.0, $pair['cantidad']);
        $this->assertSame(4.0, $pair['cantidad_venta']);
    }

    #[Test]
    public function ensurePairAceptaCantidadVentaCamelCase(): void
    {
        $pair = CargaUnidadesVentaConverter::ensurePair([
            'cantidad' => 10,
            'cantidadVenta' => 5,
        ]);

        $this->assertSame(10.0, $pair['cantidad']);
        $this->assertSame(5.0, $pair['cantidad_venta']);
    }

    #[Test]
    public function ensurePairDerivaSegunCampoPresente(): void
    {
        $soloCantidad = CargaUnidadesVentaConverter::ensurePair(
            ['cantidad' => 6],
            2,
            true,
        );
        $this->assertSame(6.0, $soloCantidad['cantidad']);
        $this->assertSame(3.0, $soloCantidad['cantidad_venta']);

        $soloCantidadVenta = CargaUnidadesVentaConverter::ensurePair(
            ['cantidad_venta' => 3],
            2,
            false,
        );
        $this->assertSame(6.0, $soloCantidadVenta['cantidad']);
        $this->assertSame(3.0, $soloCantidadVenta['cantidad_venta']);
    }
}
