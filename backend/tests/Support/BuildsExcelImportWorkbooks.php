<?php

namespace Tests\Support;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait BuildsExcelImportWorkbooks
{
    /**
     * @param  list<list<mixed>>  $dataRows
     */
    protected function buildExcelImportUploadedFile(
        array $headers,
        array $dataRows,
        string $originalName = 'articulos_test.xlsx',
        string $sheetName = 'Hoja1',
    ): UploadedFile {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);

        foreach ($headers as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }

        foreach ($dataRows as $rowOffset => $rowValues) {
            foreach ($rowValues as $colIndex => $value) {
                $sheet->setCellValue([$colIndex + 1, $rowOffset + 2], $value);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'excel_import_test_');
        if ($path === false) {
            $this->fail('No se pudo crear archivo temporal para test Excel.');
        }

        $targetPath = $path.'.xlsx';
        @unlink($path);
        (new Xlsx($spreadsheet))->save($targetPath);

        return new UploadedFile(
            $targetPath,
            $originalName,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    /**
     * @return list<string>
     */
    protected function articulosAltaHeaders(): array
    {
        return ['Codigo', 'Descripcion', 'Rubro', 'Precio', 'Fecha Alta'];
    }

    protected function articulosAltaValidFile(): UploadedFile
    {
        return $this->buildExcelImportUploadedFile(
            $this->articulosAltaHeaders(),
            [
                ['ART-001', 'Articulo valido', 'GEN', 10.5, '2026-01-15'],
                ['ART-002', 'Otro articulo', 'GEN', 20, null],
            ]
        );
    }

    protected function articulosAltaStructuralErrorFile(): UploadedFile
    {
        return $this->buildExcelImportUploadedFile(
            ['Descripcion', 'Rubro', 'Precio', 'Fecha Alta'],
            [
                ['Sin codigo', 'GEN', 10, null],
            ]
        );
    }

    protected function articulosAltaRowErrorFile(): UploadedFile
    {
        return $this->buildExcelImportUploadedFile(
            $this->articulosAltaHeaders(),
            [
                ['ART-OK', 'Fila valida', 'GEN', 5, '2026-02-01'],
                ['ART-BAD', 'Fila invalida', 'GEN', 5, 'fecha-invalida'],
            ]
        );
    }

    /**
     * @return list<string>
     */
    protected function pedidoMasivoHeaders(): array
    {
        return [
            'codigo cliente',
            'codigo de articulo',
            'cantidad',
            'precio lista',
            'bonif renglon',
            'codigo perfil',
            'condicion de venta',
            'codigo transporte',
            'direccion entrega',
            'codigo lista',
            'nivel',
            'bonificacion 1',
            'bonificacion 2',
            'bonificacion 3',
            'expreso',
            'direccion expreso',
            'fecha entrega',
            'observaciones',
            'leyenda 1',
            'leyenda 2',
            'leyenda 3',
            'leyenda 4',
            'leyenda 5',
        ];
    }

    protected function pedidoMasivoMultiGrupoFile(): UploadedFile
    {
        return $this->buildExcelImportUploadedFile(
            $this->pedidoMasivoHeaders(),
            [
                ['CLIMVP001', 'ART-HP-001', 1, null, null, null, null, null, null, null, 0, null, null, null, null, null, null, null, null, null, null, null, null],
                ['CLIMVP002', 'ART-HP-001', 2, null, null, null, null, null, null, null, 0, null, null, null, null, null, null, null, null, null, null, null, null],
            ],
            'pedido_masivo_test.xlsx'
        );
    }
}
