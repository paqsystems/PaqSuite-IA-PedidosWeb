<?php

namespace Tests\Unit\PedidosWeb\Integracion;

use App\Services\PedidosWeb\Integracion\Maestros\Handlers\CatalogoMaestroHandler;
use App\Services\PedidosWeb\Integracion\Maestros\Repositories\MaestroSpRepository;
use ReflectionMethod;
use Tests\TestCase;

final class CatalogoMaestroHandlerTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalizeItem(array $definition, array $item): array
    {
        $handler = new CatalogoMaestroHandler(new MaestroSpRepository(), $definition);
        $method = new ReflectionMethod(CatalogoMaestroHandler::class, 'normalize');
        $method->setAccessible(true);

        /** @var array<string, mixed> $normalized */
        $normalized = $method->invoke($handler, $item);

        return $normalized;
    }

    public function testListapreciosNormalizesNestedItemsAndCodigoAliases(): void
    {
        $normalized = $this->normalizeItem([
            'resource' => 'listaprecios',
            'key' => ['codLista'],
        ], [
            'codigo' => 42,
            'descripcion' => 'Lista ERP',
            'items' => [
                ['codigo' => 'ART-1', 'precio' => 10.5],
            ],
        ]);

        $this->assertSame(42, $normalized['codLista'] ?? null);
        $this->assertSame('ART-1', $normalized['articulos'][0]['codArticulo'] ?? null);
        $this->assertSame(10.5, $normalized['articulos'][0]['precio'] ?? null);
    }

    public function testEscalasMapsDescripcionToDescripEs(): void
    {
        $normalized = $this->normalizeItem([
            'resource' => 'escalas',
            'key' => ['codEscala'],
        ], [
            'codEscala' => '01',
            'descripcion' => 'Unidades ERP',
        ]);

        $this->assertSame('Unidades ERP', $normalized['descripEs'] ?? null);
    }
}
