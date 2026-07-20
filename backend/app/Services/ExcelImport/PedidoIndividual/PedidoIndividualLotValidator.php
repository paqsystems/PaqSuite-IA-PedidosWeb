<?php

namespace App\Services\ExcelImport\PedidoIndividual;

use App\Services\ExcelImport\Dto\ExcelRowError;
use App\Services\ExcelImport\PedidoExcel\PedidoExcelImportCabeceraSignature;

final class PedidoIndividualLotValidator
{
    /**
     * @param  list<array<string, mixed>>  $parsedRows
     * @return list<array<string, mixed>>
     */
    public function apply(array $parsedRows): array
    {
        $validRows = array_values(array_filter(
            $parsedRows,
            static fn (array $row): bool => ! ($row['tieneError'] ?? false)
        ));

        if ($validRows === []) {
            return $parsedRows;
        }

        $referenceCliente = PedidoExcelImportCabeceraSignature::normalizeScalar(
            $validRows[0]['datosNormalizados']['cod_cliente'] ?? null
        );
        $referenceCabecera = PedidoExcelImportCabeceraSignature::fromDatos($validRows[0]['datosNormalizados']);

        foreach ($parsedRows as &$row) {
            if ($row['tieneError'] ?? false) {
                continue;
            }

            $datos = $row['datosNormalizados'];
            $cliente = PedidoExcelImportCabeceraSignature::normalizeScalar($datos['cod_cliente'] ?? null);

            if ($cliente !== $referenceCliente) {
                $this->appendError($row, new ExcelRowError(
                    'negocio',
                    trans('excel_import.pedidoIndividual.clienteDistinto'),
                    'PEDIDO_CLIENTE_DISTINTO',
                    'cod_cliente',
                    'codigo cliente'
                ));
                continue;
            }

            $signature = PedidoExcelImportCabeceraSignature::fromDatos($datos);
            if ($signature !== $referenceCabecera) {
                $this->appendError($row, new ExcelRowError(
                    'negocio',
                    trans('excel_import.pedidoIndividual.cabeceraIncoherente'),
                    'PEDIDO_CABECERA_INCOHERENTE',
                    null,
                    null
                ));
            }
        }
        unset($row);

        return $parsedRows;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function appendError(array &$row, ExcelRowError $error): void
    {
        $row['errores'][] = $error;
        $row['tieneError'] = true;
        $row['estadoFila'] = 'con_error';
        $messages = array_map(
            static fn (ExcelRowError $item): string => $item->mensajeError,
            $row['errores']
        );
        $row['errorImportacion'] = implode('; ', $messages);
    }
}
