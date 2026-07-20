<?php

namespace App\Services\ExcelImport\PedidoMasivo;

use App\Services\ExcelImport\Dto\ExcelRowError;
use App\Services\ExcelImport\PedidoExcel\PedidoExcelImportCabeceraSignature;

/**
 * Validaciones de lote exclusivas de PEDIDO_MASIVO (p. ej. vendedor del cliente).
 * A diferencia de PEDIDO_INDIVIDUAL, cabeceras distintas generan grupos/comprobantes distintos
 * (no se rechaza el archivo por "incoherencia" de cabecera).
 */
final class PedidoMasivoLotValidator
{
    public function __construct(
        private readonly PedidoMasivoClienteVendedorResolver $vendedorResolver,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $parsedRows
     * @return list<array<string, mixed>>
     */
    public function apply(array $parsedRows): array
    {
        foreach ($parsedRows as $index => $row) {
            if ($row['tieneError'] ?? false) {
                continue;
            }

            $datos = $row['datosNormalizados'];
            $codCliente = PedidoExcelImportCabeceraSignature::normalizeScalar($datos['cod_cliente'] ?? null) ?? '';
            $vendedor = $this->vendedorResolver->resolve($codCliente);

            if ($vendedor['codVended'] === null) {
                $this->appendError($parsedRows[$index], new ExcelRowError(
                    'negocio',
                    trans('excel_import.pedidoMasivo.vendedorFaltante'),
                    'PEDIDO_MASIVO_VENDEDOR_FALTANTE',
                    'cod_cliente',
                    'codigo cliente'
                ));
            }
        }

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
