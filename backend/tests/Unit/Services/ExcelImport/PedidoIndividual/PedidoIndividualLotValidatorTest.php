<?php

namespace Tests\Unit\Services\ExcelImport\PedidoIndividual;

use App\Services\ExcelImport\PedidoIndividual\PedidoIndividualLotValidator;
use Tests\TestCase;

final class PedidoIndividualLotValidatorTest extends TestCase
{
    public function testMarcaErrorCuandoCodClienteDistinto(): void
    {
        $validator = new PedidoIndividualLotValidator();
        $rows = [
            $this->validRow(2, ['cod_cliente' => 'CLI001', 'cod_articulo' => 'A1', 'cantidad' => 1]),
            $this->validRow(3, ['cod_cliente' => 'CLI002', 'cod_articulo' => 'A2', 'cantidad' => 1]),
        ];

        $result = $validator->apply($rows);

        $this->assertTrue($result[1]['tieneError']);
        $this->assertStringContainsString('customer', strtolower($result[1]['errorImportacion'] ?? ''));
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
