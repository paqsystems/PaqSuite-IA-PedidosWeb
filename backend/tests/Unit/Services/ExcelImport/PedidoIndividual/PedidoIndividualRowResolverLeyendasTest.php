<?php

namespace Tests\Unit\Services\ExcelImport\PedidoIndividual;

use App\Contracts\PedidosWeb\ArticuloRepositoryInterface;
use App\Models\PqPedidoswebArticulo;
use App\Models\User;
use App\Services\Auth\CommercialProfileResolver;
use App\Services\ExcelImport\PedidoIndividual\PedidoIndividualRowResolver;
use App\Services\PedidosWeb\CabeceraInicialService;
use App\Services\PedidosWeb\PedidosWebParameterService;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Tests\TestCase;

final class PedidoIndividualRowResolverLeyendasTest extends TestCase
{
    #[Test]
    public function enrichRowRecortaLeyendasASesenta(): void
    {
        $articulo = new PqPedidoswebArticulo();
        $articulo->codigo = 'ART-LEY';
        $articulo->descripcion = 'Articulo leyenda';
        $articulo->porc_iva = 21;
        $articulo->equivalencia_ventas = 1;
        $articulo->bonificacion = 0;

        $articuloRepository = $this->createMock(ArticuloRepositoryInterface::class);
        $articuloRepository->method('findByCodigo')->willReturn($articulo);
        $articuloRepository->method('findPrecioLista')->willReturn(null);
        $articuloRepository->method('findDescuentoCantidad')->willReturn(null);

        config()->set('paqsuite_pedidosweb.readFromErp', false);

        $resolver = new PedidoIndividualRowResolver(
            new CabeceraInicialService(
                $this->createMock(PedidosWebVisibilityGuard::class),
                new PedidosWebParameterService(),
            ),
            $articuloRepository,
            new PedidosWebParameterService(),
            $this->createMock(PedidosWebVisibilityGuard::class),
            new CommercialProfileResolver(),
        );

        $cache = new ReflectionProperty(PedidoIndividualRowResolver::class, 'cabeceraCacheByCliente');
        $cache->setAccessible(true);
        $cache->setValue($resolver, [
            'CLI-LEY' => [
                'cabecera' => [
                    'cod_cliente' => 'CLI-LEY',
                    'lista_precios' => 1,
                    'cod_condvta' => 1,
                    'cod_transpor' => 'T1',
                    'id_de' => 1,
                    'nivel' => 0,
                    'bonif_1' => 0,
                    'bonif_2' => 0,
                    'bonif_3' => 0,
                    'observaciones' => '',
                    'cod_perfil' => 'MVP',
                ],
                'catalogos' => [],
            ],
        ]);

        $user = new User();
        $user->codigo = 'supervisor.mvp';

        $resolved = $resolver->enrichRow([
            'cod_cliente' => 'CLI-LEY',
            'cod_articulo' => 'ART-LEY',
            'cantidad' => 1,
            'precio_lista' => 10,
            'leyenda1' => str_repeat('a', 61),
            'leyenda2' => str_repeat('b', 60),
        ], $user);

        $this->assertSame(str_repeat('a', 60), $resolved['leyenda1']);
        $this->assertSame(60, mb_strlen((string) $resolved['leyenda1']));
        $this->assertSame(str_repeat('b', 60), $resolved['leyenda2']);
    }
}
