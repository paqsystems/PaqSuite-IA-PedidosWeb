<?php

namespace Tests\Unit;

use App\Services\Menu\MenuNodeTypeResolver;
use PHPUnit\Framework\TestCase;

final class MenuNodeTypeResolverTest extends TestCase
{
    private MenuNodeTypeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new MenuNodeTypeResolver();
    }

    public function testRouteNameNonEmptyResolvesProcess(): void
    {
        $this->assertSame('process', $this->resolver->resolve('/pedidos/carga', null));
        $this->assertSame('process', $this->resolver->resolve('/pedidos/carga', 'G'));
    }

    public function testTipoProcesoPResolvesProcessWithoutRoute(): void
    {
        $this->assertSame('process', $this->resolver->resolve('', 'P'));
        $this->assertSame('process', $this->resolver->resolve(null, ' p '));
    }

    public function testEmptyRouteAndNonProcessTipoResolvesGroup(): void
    {
        $this->assertSame('group', $this->resolver->resolve('', 'G'));
        $this->assertSame('group', $this->resolver->resolve(null, null));
    }
}
