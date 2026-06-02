<?php

namespace Tests\Unit\Models;

use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\PqPedidoswebPedidoDetalle;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

class PedidosWebModelsTest extends TestCase
{
    #[Test]
    public function pedidoCabeceraExponeCamposDeTrazabilidadYBloqueo(): void
    {
        $model = new PqPedidoswebPedidoCabecera();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('fechahora_ultima_actividad', $casts);
        $this->assertContains('cod_presupuesto_origen', $model->getFillable());
        $this->assertSame('datetime', $casts['fechahora_ultima_actividad']);
    }

    #[Test]
    public function pedidoDetalleUsaPorcBonifYClaveCompuesta(): void
    {
        $model = new PqPedidoswebPedidoDetalle();

        $this->assertContains('porc_bonif', $model->getFillable());
        $this->assertArrayHasKey('porc_bonif', $model->getCasts());
        $this->assertSame('decimal:4', $model->getCasts()['cantidad']);

        $reflection = new ReflectionClass($model);
        $method = $reflection->getMethod('getCompositeKeyNames');
        $method->setAccessible(true);

        $this->assertSame(['cod_pedido', 'renglon'], $method->invoke($model));
    }

    #[Test]
    public function modelosNoDefinenMetodosDeNegocioCustom(): void
    {
        $modelClasses = [
            PqPedidoswebPedidoCabecera::class,
            PqPedidoswebPedidoDetalle::class,
        ];

        foreach ($modelClasses as $modelClass) {
            $reflection = new ReflectionClass($modelClass);
            $customMethods = array_filter(
                $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
                static fn (\ReflectionMethod $method): bool => $method->getDeclaringClass()->getName() === $modelClass
                    && ! $method->isStatic()
                    && ! in_array($method->getName(), ['__construct', '__call', '__callStatic', '__get', '__set'], true)
                    && ! str_starts_with($method->getName(), 'get')
                    && ! str_starts_with($method->getName(), 'set')
                    && ! str_starts_with($method->getName(), 'new')
                    && ! in_array($method->getName(), [
                        'cabecera', 'detalles', 'cliente', 'vendedor', 'condicionVenta', 'transporte',
                        'listaPrecios', 'articulo',
                    ], true)
            );

            $this->assertSame([], array_map(
                static fn (\ReflectionMethod $method): string => $method->getName(),
                $customMethods
            ), $modelClass.' no debe exponer métodos de negocio custom');
        }
    }
}
