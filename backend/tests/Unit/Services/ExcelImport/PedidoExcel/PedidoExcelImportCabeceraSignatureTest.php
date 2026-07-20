<?php

namespace Tests\Unit\Services\ExcelImport\PedidoExcel;

use App\Services\ExcelImport\PedidoExcel\PedidoExcelImportCabeceraSignature;
use PHPUnit\Framework\TestCase;

final class PedidoExcelImportCabeceraSignatureTest extends TestCase
{
    public function testMasivoGroupKeyDifiereCuandoCabeceraCambiaAunqueSeaMismoCliente(): void
    {
        $base = [
            'cod_cliente' => 'CLI001',
            'nivel' => 0,
            'observaciones' => 'A',
        ];
        $otra = [
            'cod_cliente' => 'CLI001',
            'nivel' => 0,
            'observaciones' => 'B',
        ];

        $keyA = PedidoExcelImportCabeceraSignature::buildMasivoGroupKey('CLI001', 'V1', $base);
        $keyB = PedidoExcelImportCabeceraSignature::buildMasivoGroupKey('CLI001', 'V1', $otra);

        $this->assertNotSame($keyA, $keyB);
    }

    public function testMasivoGroupKeyIgualCuandoCabeceraCompletaCoincide(): void
    {
        $datos = [
            'cod_cliente' => 'CLI001',
            'nivel' => 0,
            'observaciones' => 'Mismo',
            'cod_lista' => 1,
        ];

        $keyA = PedidoExcelImportCabeceraSignature::buildMasivoGroupKey('CLI001', 'V1', $datos);
        $keyB = PedidoExcelImportCabeceraSignature::buildMasivoGroupKey('CLI001', 'V1', $datos);

        $this->assertSame($keyA, $keyB);
    }
}
