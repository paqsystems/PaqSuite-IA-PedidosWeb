<?php

namespace App\Services\ExcelImport\PedidoMasivo;

use App\Services\ExcelImport\Dto\ExcelRowError;
use App\Services\ExcelImport\PedidoExcel\PedidoExcelImportCabeceraSignature;

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
        $validIndices = [];
        foreach ($parsedRows as $index => $row) {
            if (! ($row['tieneError'] ?? false)) {
                $validIndices[] = $index;
            }
        }

        if ($validIndices === []) {
            return $parsedRows;
        }

        /** @var array<string, list<int>> $indicesByGroupKey */
        $indicesByGroupKey = [];

        foreach ($validIndices as $index) {
            $datos = $parsedRows[$index]['datosNormalizados'];
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
                continue;
            }

            $nivel = PedidoExcelImportCabeceraSignature::resolveNivelForGroupKey($datos['nivel'] ?? null);
            $groupKey = $this->buildGroupKey($codCliente, $vendedor['codVended'], $nivel);
            $indicesByGroupKey[$groupKey][] = $index;
        }

        foreach ($indicesByGroupKey as $indices) {
            $referenceIndex = $indices[0];
            $referenceSignature = PedidoExcelImportCabeceraSignature::fromDatos(
                $parsedRows[$referenceIndex]['datosNormalizados']
            );

            foreach (array_slice($indices, 1) as $index) {
                if ($parsedRows[$index]['tieneError'] ?? false) {
                    continue;
                }

                $signature = PedidoExcelImportCabeceraSignature::fromDatos(
                    $parsedRows[$index]['datosNormalizados']
                );

                if ($signature !== $referenceSignature) {
                    $this->appendError($parsedRows[$index], new ExcelRowError(
                        'negocio',
                        trans('excel_import.pedidoMasivo.cabeceraIncoherenteGrupo'),
                        'PEDIDO_MASIVO_CABECERA_INCOHERENTE',
                        null,
                        null
                    ));
                }
            }
        }

        return $parsedRows;
    }

    private function buildGroupKey(string $codCliente, string $codVended, int $nivel): string
    {
        return strtolower($codCliente).'|'.$codVended.'|'.$nivel;
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
