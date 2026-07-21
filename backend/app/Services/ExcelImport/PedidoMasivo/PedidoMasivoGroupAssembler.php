<?php

namespace App\Services\ExcelImport\PedidoMasivo;

use App\Models\User;
use App\Services\ExcelImport\PedidoExcel\PedidoExcelImportCabeceraSignature;
use App\Services\PedidosWeb\CabeceraInicialService;
use Illuminate\Support\Str;

final class PedidoMasivoGroupAssembler
{
    public function __construct(
        private readonly PedidoMasivoClienteVendedorResolver $vendedorResolver,
        private readonly CabeceraInicialService $cabeceraInicialService,
    ) {}

    /**
     * @param  list<array{numeroFilaExcel: int, datos: array<string, mixed>}>  $validItems
     * @return list<array<string, mixed>>
     */
    public function assemble(array $validItems, User $user): array
    {
        if ($validItems === []) {
            return [];
        }

        /** @var array<string, list<array{numeroFilaExcel: int, datos: array<string, mixed>}>> $rowsByGroup */
        $rowsByGroup = [];
        /** @var list<string> $groupOrder */
        $groupOrder = [];

        foreach ($validItems as $item) {
            $datos = $item['datos'];
            $codCliente = trim((string) ($datos['cod_cliente'] ?? ''));
            $vendedor = $this->vendedorResolver->resolve($codCliente);
            $codVended = (string) ($vendedor['codVended'] ?? '');
            $groupKey = PedidoExcelImportCabeceraSignature::buildMasivoGroupKey(
                $codCliente,
                $codVended,
                $datos
            );

            if (! isset($rowsByGroup[$groupKey])) {
                $rowsByGroup[$groupKey] = [];
                $groupOrder[] = $groupKey;
            }

            $rowsByGroup[$groupKey][] = $item;
        }

        $grupos = [];
        foreach ($groupOrder as $groupKey) {
            $groupRows = $rowsByGroup[$groupKey];
            $firstRow = $groupRows[0]['datos'];
            $codCliente = trim((string) ($firstRow['cod_cliente'] ?? ''));
            $vendedor = $this->vendedorResolver->resolve($codCliente);
            $nivel = PedidoExcelImportCabeceraSignature::resolveNivelForGroupKey($firstRow['nivel'] ?? null);

            $cabeceraBundle = $this->cabeceraInicialService->buildForCliente($codCliente, $user);
            $cabeceraBase = $cabeceraBundle['cabecera'];

            $grupos[] = [
                'idGrupo' => 'tmp-'.(string) Str::uuid(),
                'clave' => [
                    'codCliente' => $codCliente,
                    'codVended' => (string) ($vendedor['codVended'] ?? ''),
                    'nivel' => $nivel,
                ],
                'cabecera' => $this->buildCabeceraFromRow($firstRow, $cabeceraBase),
                'renglones' => $this->buildRenglones($groupRows),
                'vendedor' => [
                    'codVended' => (string) ($vendedor['codVended'] ?? ''),
                    'nombre' => $vendedor['nombre'],
                ],
            ];
        }

        return $grupos;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $cabeceraBase
     * @return array<string, mixed>
     */
    private function buildCabeceraFromRow(array $row, array $cabeceraBase): array
    {
        $listaPreciosBase = (int) ($cabeceraBase['lista_precios'] ?? 0);

        return [
            'cod_cliente' => trim((string) ($row['cod_cliente'] ?? $cabeceraBase['cod_cliente'] ?? '')),
            'razon_soci' => (string) ($cabeceraBase['razon_soci'] ?? ''),
            'cod_vended' => $cabeceraBase['cod_vended'] ?? null,
            'vendedor_nombre' => $cabeceraBase['vendedor_nombre'] ?? '',
            'cod_condvta' => $this->resolveOptionalInt($row['cod_condvta'] ?? null, (int) ($cabeceraBase['cod_condvta'] ?? 0)),
            'cod_transpor' => $this->isFilled($row['cod_transpor'] ?? null)
                ? $row['cod_transpor']
                : ($cabeceraBase['cod_transpor'] ?? null),
            'id_de' => $this->resolveOptionalInt($row['id_de'] ?? null, (int) ($cabeceraBase['id_de'] ?? 0)),
            'nivel' => PedidoExcelImportCabeceraSignature::resolveNivelForGroupKey($row['nivel'] ?? $cabeceraBase['nivel'] ?? 0),
            'lista_precios' => $this->resolveOptionalInt($row['cod_lista'] ?? null, $listaPreciosBase),
            'lista_precios_descripcion' => (string) ($cabeceraBase['lista_precios_descripcion'] ?? ''),
            'moneda' => (int) ($cabeceraBase['moneda'] ?? 1),
            'incluye_iva' => (bool) ($cabeceraBase['incluye_iva'] ?? false),
            'bonif_1' => (float) ($row['bonif1'] ?? $cabeceraBase['bonif_1'] ?? 0),
            'bonif_2' => (float) ($row['bonif2'] ?? $cabeceraBase['bonif_2'] ?? 0),
            'bonif_3' => (float) ($row['bonif3'] ?? $cabeceraBase['bonif_3'] ?? 0),
            'expreso' => $this->isFilled($row['expreso'] ?? null) ? $row['expreso'] : ($cabeceraBase['expreso'] ?? null),
            'expreso_dire' => $this->isFilled($row['expreso_dire'] ?? null)
                ? $row['expreso_dire']
                : ($cabeceraBase['expreso_dire'] ?? null),
            'fecha_entrega' => $this->isFilled($row['fecha_entrega'] ?? null)
                ? $row['fecha_entrega']
                : ($cabeceraBase['fecha_entrega'] ?? null),
            'observaciones' => (string) ($row['observaciones'] ?? $cabeceraBase['observaciones'] ?? ''),
            'cod_perfil' => $this->isFilled($row['cod_perfil'] ?? null)
                ? (string) $row['cod_perfil']
                : (string) ($cabeceraBase['cod_perfil'] ?? ''),
            'leyenda_1' => $row['leyenda1'] ?? $cabeceraBase['leyenda_1'] ?? null,
            'leyenda_2' => $row['leyenda2'] ?? $cabeceraBase['leyenda_2'] ?? null,
            'leyenda_3' => $row['leyenda3'] ?? $cabeceraBase['leyenda_3'] ?? null,
            'leyenda_4' => $row['leyenda4'] ?? $cabeceraBase['leyenda_4'] ?? null,
            'leyenda_5' => $row['leyenda5'] ?? $cabeceraBase['leyenda_5'] ?? null,
        ];
    }

    /**
     * @param  list<array{numeroFilaExcel: int, datos: array<string, mixed>}>  $groupRows
     * @return list<array<string, mixed>>
     */
    private function buildRenglones(array $groupRows): array
    {
        $renglones = [];
        $renglon = 1;

        foreach ($groupRows as $item) {
            $row = $item['datos'];
            $renglones[] = [
                'renglon' => $renglon++,
                'numeroFilaExcel' => $item['numeroFilaExcel'],
                'cod_articulo' => (string) ($row['cod_articulo'] ?? ''),
                'descripcion_articulo' => (string) ($row['descripcion_articulo'] ?? ''),
                'cantidad' => (float) ($row['cantidad'] ?? 0),
                'precio' => (float) ($row['precio'] ?? 0),
                'porc_bonif' => (float) ($row['porc_bonif'] ?? $row['bonif_renglon'] ?? 0),
                'porc_iva' => (float) ($row['porc_iva'] ?? 0),
            ];
        }

        return $renglones;
    }

    private function resolveOptionalInt(mixed $value, int $fallback): int
    {
        if (! $this->isFilled($value)) {
            return $fallback;
        }

        return (int) $value;
    }

    private function isFilled(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        return true;
    }
}
