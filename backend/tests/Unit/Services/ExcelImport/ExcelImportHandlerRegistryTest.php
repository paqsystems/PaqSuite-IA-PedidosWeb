<?php

namespace Tests\Unit\Services\ExcelImport;

use App\Services\ExcelImport\ExcelImportHandlerRegistry;
use App\Services\ExcelImport\Handlers\PedidoIndividualExcelImportHandler;
use App\Services\ExcelImport\Handlers\PedidoMasivoExcelImportHandler;
use Tests\TestCase;

final class ExcelImportHandlerRegistryTest extends TestCase
{
    public function testResolvesHandlerKeyWithDotsInName(): void
    {
        $registry = $this->app->make(ExcelImportHandlerRegistry::class);

        $handler = $registry->resolve('Importacion.Pedidos.IndividualHandler');

        $this->assertInstanceOf(PedidoIndividualExcelImportHandler::class, $handler);
    }

    public function testResolvesPedidoMasivoHandler(): void
    {
        $registry = $this->app->make(ExcelImportHandlerRegistry::class);

        $handler = $registry->resolve('Importacion.Pedidos.MasivoHandler');

        $this->assertInstanceOf(PedidoMasivoExcelImportHandler::class, $handler);
    }
}
