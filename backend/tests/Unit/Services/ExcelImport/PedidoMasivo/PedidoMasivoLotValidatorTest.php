<?php

namespace Tests\Unit\Services\ExcelImport\PedidoMasivo;

use App\Services\ExcelImport\PedidoMasivo\PedidoMasivoClienteVendedorResolver;
use App\Services\ExcelImport\PedidoMasivo\PedidoMasivoLotValidator;
use Tests\TestCase;

final class PedidoMasivoLotValidatorTest extends TestCase
{
    public function testPermiteDosClientesDistintosEnMismoLote(): void
    {
        $vendedorResolver = $this->createMock(PedidoMasivoClienteVendedorResolver::class);
        $vendedorResolver->method('resolve')->willReturnMap([
            ['CLI001', ['codVended' => 'V1', 'nombre' => 'Vendedor 1']],
            ['CLI002', ['codVended' => 'V2', 'nombre' => 'Vendedor 2']],
        ]);

        $validator = new PedidoMasivoLotValidator($vendedorResolver);
        $rows = [
            $this->validRow(2, ['cod_cliente' => 'CLI001', 'cod_articulo' => 'A1', 'cantidad' => 1, 'nivel' => 0]),
            $this->validRow(3, ['cod_cliente' => 'CLI002', 'cod_articulo' => 'A2', 'cantidad' => 1, 'nivel' => 0]),
        ];

        $result = $validator->apply($rows);

        $this->assertFalse($result[0]['tieneError']);
        $this->assertFalse($result[1]['tieneError']);
    }

    public function testPermiteMismoClienteConCabecerasDistintas(): void
    {
        $vendedorResolver = $this->createMock(PedidoMasivoClienteVendedorResolver::class);
        $vendedorResolver->method('resolve')->willReturn(['codVended' => 'V1', 'nombre' => 'Vendedor 1']);

        $validator = new PedidoMasivoLotValidator($vendedorResolver);
        $rows = [
            $this->validRow(2, ['cod_cliente' => 'CLI001', 'cod_articulo' => 'A1', 'cantidad' => 1, 'nivel' => 0, 'observaciones' => 'A']),
            $this->validRow(3, ['cod_cliente' => 'CLI001', 'cod_articulo' => 'A2', 'cantidad' => 1, 'nivel' => 0, 'observaciones' => 'B']),
        ];

        $result = $validator->apply($rows);

        $this->assertFalse($result[0]['tieneError']);
        $this->assertFalse($result[1]['tieneError']);
    }

    public function testMarcaErrorCuandoClienteSinVendedor(): void
    {
        $vendedorResolver = $this->createMock(PedidoMasivoClienteVendedorResolver::class);
        $vendedorResolver->method('resolve')->willReturn(['codVended' => null, 'nombre' => '']);

        $validator = new PedidoMasivoLotValidator($vendedorResolver);
        $rows = [
            $this->validRow(2, ['cod_cliente' => 'CLI001', 'cod_articulo' => 'A1', 'cantidad' => 1]),
        ];

        $result = $validator->apply($rows);

        $this->assertTrue($result[0]['tieneError']);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function validRow(int $numeroFilaExcel, array $datos): array
    {
        return [
            'numeroFilaExcel' => $numeroFilaExcel,
            'estadoFila' => 'valida',
            'filaAjustada' => false,
            'tieneError' => false,
            'errorImportacion' => null,
            'errores' => [],
            'datosOriginales' => $datos,
            'datosNormalizados' => $datos,
        ];
    }
}
