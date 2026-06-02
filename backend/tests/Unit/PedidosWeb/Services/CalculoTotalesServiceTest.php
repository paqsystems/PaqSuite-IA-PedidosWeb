<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Services\PedidosWeb\CalculoTotalesService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CalculoTotalesServiceTest extends TestCase
{
    #[Test]
    public function calcularAplicaBonificacionEIvaPorRenglon(): void
    {
        $service = new CalculoTotalesService();

        $result = $service->calcular([
            [
                'cod_articulo' => 'ART001',
                'cantidad' => 2,
                'precio' => 100,
                'porc_bonif' => 10,
                'porc_iva' => 21,
            ],
        ]);

        $this->assertSame(180.0, $result['total']);
        $this->assertSame(37.8, $result['totalIva']);
        $this->assertSame(90.0, $result['renglones'][0]['precio_neto']);
        $this->assertSame(180.0, $result['renglones'][0]['importe_neto']);
        $this->assertSame(217.8, $result['renglones'][0]['importe_total']);
    }

    #[Test]
    public function calcularSumaVariosRenglones(): void
    {
        $service = new CalculoTotalesService();

        $result = $service->calcular([
            ['cod_articulo' => 'A', 'cantidad' => 1, 'precio' => 50, 'porc_bonif' => 0, 'porc_iva' => 21],
            ['cod_articulo' => 'B', 'cantidad' => 2, 'precio' => 25, 'porc_bonif' => 0, 'porc_iva' => 10.5],
        ]);

        $this->assertSame(100.0, $result['total']);
        $this->assertSame(15.75, $result['totalIva']);
    }
}
