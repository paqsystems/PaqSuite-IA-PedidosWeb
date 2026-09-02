<?php

namespace Tests\Unit\Services\ExcelImport\PedidoMasivo;

use App\Services\ExcelImport\PedidoMasivo\PedidoMasivoClienteVendedorResolver;
use App\Services\ExcelImport\PedidoMasivo\PedidoMasivoGroupAssembler;
use App\Services\PedidosWeb\CabeceraInicialService;
use App\Services\PedidosWeb\PedidosWebParameterService;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

final class PedidoMasivoGroupAssemblerLeyendasTest extends TestCase
{
    #[Test]
    public function buildCabeceraFromRowRecortaLeyendasASesenta(): void
    {
        $assembler = new PedidoMasivoGroupAssembler(
            $this->createMock(PedidoMasivoClienteVendedorResolver::class),
            new CabeceraInicialService(
                $this->createMock(PedidosWebVisibilityGuard::class),
                new PedidosWebParameterService(),
            ),
        );

        $method = new ReflectionMethod(PedidoMasivoGroupAssembler::class, 'buildCabeceraFromRow');
        $method->setAccessible(true);

        $cabecera = $method->invoke(
            $assembler,
            [
                'cod_cliente' => 'CLI-MAS',
                'leyenda1' => str_repeat('m', 61),
                'leyenda2' => str_repeat('n', 60),
            ],
            [
                'cod_cliente' => 'CLI-MAS',
                'razon_soci' => 'Cliente masivo',
                'cod_vended' => 'VEN01',
                'vendedor_nombre' => 'Vendedor',
                'cod_condvta' => 1,
                'cod_transpor' => 'T1',
                'id_de' => 1,
                'nivel' => 0,
                'lista_precios' => 1,
                'lista_precios_descripcion' => 'Lista',
                'moneda' => 1,
                'incluye_iva' => false,
                'bonif_1' => 0,
                'bonif_2' => 0,
                'bonif_3' => 0,
                'observaciones' => '',
                'cod_perfil' => 'MVP',
            ],
        );

        $this->assertSame(str_repeat('m', 60), $cabecera['leyenda_1']);
        $this->assertSame(60, mb_strlen((string) $cabecera['leyenda_1']));
        $this->assertSame(str_repeat('n', 60), $cabecera['leyenda_2']);
    }
}
