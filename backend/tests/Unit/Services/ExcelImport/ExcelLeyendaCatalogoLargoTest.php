<?php

namespace Tests\Unit\Services\ExcelImport;

use Database\Seeders\ExcelImport\PedidosWebExcelImportCatalogSeeder;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

final class ExcelLeyendaCatalogoLargoTest extends TestCase
{
    #[Test]
    public function catalogoLeyendasMantieneLargoMaximo255(): void
    {
        $campos = (new ReflectionClass(PedidosWebExcelImportCatalogSeeder::class))
            ->getConstant('camposPedido');

        $this->assertIsArray($campos);

        $leyendas = array_values(array_filter(
            $campos,
            static fn (array $campo): bool => str_starts_with((string) $campo[2], 'leyenda')
        ));

        $this->assertCount(5, $leyendas);

        foreach ($leyendas as $campo) {
            $this->assertSame(255, $campo[4], (string) $campo[2].' no debe bajar largo_maximo a 60');
        }
    }
}
