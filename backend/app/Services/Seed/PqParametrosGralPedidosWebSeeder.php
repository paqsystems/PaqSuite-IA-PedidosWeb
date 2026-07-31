<?php

namespace App\Services\Seed;

use App\Support\ParametrosGralTipoValor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PqParametrosGralPedidosWebSeeder
{
    public function seedFromJsonFile(string $jsonPath, bool $recreateTable = false): int
    {
        if ($recreateTable) {
            app(PedidosWebDevSchemaBootstrap::class)->recreateParametrosGralTable();
        } elseif (! Schema::hasTable('PQ_parametros_gral')) {
            app(PedidosWebDevSchemaBootstrap::class)->recreateParametrosGralTable();
        }

        $items = $this->loadSeedItems($jsonPath);

        DB::table('PQ_parametros_gral')->where('Programa', 'PedidosWeb')->delete();

        $inserted = 0;

        foreach ($items as $item) {
            DB::table('PQ_parametros_gral')->insert($this->buildRow($item));
            $inserted++;
        }

        return $inserted;
    }

    /**
     * Inserta solo claves PedidosWeb del JSON que aún no existen.
     * No actualiza valores/CAPTION existentes (seguro para deploy).
     */
    public function insertMissingFromJsonFile(string $jsonPath): int
    {
        if (! Schema::hasTable('PQ_parametros_gral')) {
            return 0;
        }

        $items = $this->loadSeedItems($jsonPath);
        $inserted = 0;

        foreach ($items as $item) {
            $programa = (string) ($item['programa'] ?? 'PedidosWeb');
            $clave = (string) ($item['clave'] ?? '');
            if ($clave === '') {
                continue;
            }

            $exists = DB::table('PQ_parametros_gral')
                ->where('Programa', $programa)
                ->where('Clave', $clave)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('PQ_parametros_gral')->insert($this->buildRow($item));
            $inserted++;
        }

        return $inserted;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadSeedItems(string $jsonPath): array
    {
        if (! is_file($jsonPath)) {
            throw new \RuntimeException("No se encontró el seed JSON: {$jsonPath}");
        }

        /** @var list<array<string, mixed>> $items */
        $items = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);

        return $items;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function buildRow(array $item): array
    {
        $tipoValor = strtoupper(substr((string) ($item['tipoValor'] ?? 'S'), 0, 1));
        if (! in_array($tipoValor, ParametrosGralTipoValor::VALID, true)) {
            $tipoValor = 'S';
        }

        $row = [
            'Programa' => (string) ($item['programa'] ?? 'PedidosWeb'),
            'Clave' => (string) $item['clave'],
            'tipo_valor' => $tipoValor,
            'Valor_String' => null,
            'Valor_Text' => null,
            'Valor_Int' => null,
            'Valor_DateTime' => null,
            'Valor_Bool' => null,
            'Valor_Decimal' => null,
            'CAPTION' => $item['caption'] ?? null,
            'TOOLTIP' => $item['tooltip'] ?? null,
        ];

        $column = ParametrosGralTipoValor::columnaPorTipo($tipoValor);
        $row[$column] = $this->resolveValorColumna($tipoValor, $item);

        return $row;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function resolveValorColumna(string $tipoValor, array $item): mixed
    {
        return match ($tipoValor) {
            'T' => $item['valorText'] ?? null,
            'I' => $item['valorInt'] ?? null,
            'D' => $item['valorDateTime'] ?? null,
            'B' => isset($item['valorBool']) ? (bool) $item['valorBool'] : false,
            'N' => $item['valorDecimal'] ?? null,
            default => $item['valorString'] ?? null,
        };
    }
}
