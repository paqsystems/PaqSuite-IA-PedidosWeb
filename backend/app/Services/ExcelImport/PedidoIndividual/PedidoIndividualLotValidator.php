<?php

namespace App\Services\ExcelImport\PedidoIndividual;

use App\Services\ExcelImport\Dto\ExcelRowError;

final class PedidoIndividualLotValidator
{
    /** @var list<string> */
    private const cabeceraFields = [
        'cod_perfil',
        'cod_condvta',
        'cod_transpor',
        'id_de',
        'cod_lista',
        'nivel',
        'bonif1',
        'bonif2',
        'bonif3',
        'expreso',
        'expreso_dire',
        'fecha_entrega',
        'observaciones',
        'leyenda1',
        'leyenda2',
        'leyenda3',
        'leyenda4',
        'leyenda5',
    ];

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

        $referenceCliente = $this->normalizeScalar($validRows[0]['datosNormalizados']['cod_cliente'] ?? null);
        $referenceCabecera = $this->extractCabeceraSignature($validRows[0]['datosNormalizados']);

        foreach ($parsedRows as &$row) {
            if ($row['tieneError'] ?? false) {
                continue;
            }

            $datos = $row['datosNormalizados'];
            $cliente = $this->normalizeScalar($datos['cod_cliente'] ?? null);

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

            $signature = $this->extractCabeceraSignature($datos);
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
     * @param  array<string, mixed>  $datos
     */
    private function extractCabeceraSignature(array $datos): string
    {
        $parts = [];
        foreach (self::cabeceraFields as $field) {
            $parts[] = $field.':'.json_encode($this->normalizeScalar($datos[$field] ?? null));
        }

        return implode('|', $parts);
    }

    private function normalizeScalar(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
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
