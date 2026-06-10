<?php

namespace App\Services\Config;

use App\Support\ParametrosGralTipoValor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ParametrosConsultaService
{
    /**
     * @return array{items: list<array<string, string>>, programa: string, total: int}
     */
    public function listarPorPrograma(string $programa): array
    {
        $programaNormalizado = trim($programa) !== '' ? trim($programa) : 'PedidosWeb';

        if (! $this->canReadFromDatabase()) {
            return [
                'items' => [],
                'programa' => $programaNormalizado,
                'total' => 0,
            ];
        }

        $rows = DB::table('PQ_parametros_gral')
            ->whereRaw('LOWER(Programa) = ?', [strtolower($programaNormalizado)])
            ->orderByRaw('COALESCE(NULLIF(LTRIM(RTRIM(CAPTION)), \'\'), Clave)')
            ->get([
                'Clave',
                'tipo_valor',
                'Valor_String',
                'Valor_Text',
                'Valor_Int',
                'Valor_DateTime',
                'Valor_Bool',
                'Valor_Decimal',
                'CAPTION',
                'TOOLTIP',
            ]);

        $items = $rows
            ->map(fn (object $row): array => $this->mapRow($row))
            ->values()
            ->all();

        return [
            'items' => $items,
            'programa' => $programaNormalizado,
            'total' => count($items),
        ];
    }

    /**
     * @return array{clave: string, caption: string, tooltip: string, tipoValor: string, valorMostrado: string}
     */
    private function mapRow(object $row): array
    {
        $tipoValor = ParametrosGralTipoValor::fromRow($row);
        $columna = ParametrosGralTipoValor::columnaPorTipo($tipoValor);
        $valorRaw = $row->{$columna} ?? null;
        $clave = (string) ($row->Clave ?? '');

        return [
            'clave' => $clave,
            'caption' => filled($row->CAPTION ?? null) ? (string) $row->CAPTION : $clave,
            'tooltip' => (string) ($row->TOOLTIP ?? ''),
            'tipoValor' => $tipoValor,
            'valorMostrado' => $this->formatValorMostrado($tipoValor, $valorRaw),
        ];
    }

    private function formatValorMostrado(string $tipoValor, mixed $valorRaw): string
    {
        if ($valorRaw === null || $valorRaw === '') {
            return '';
        }

        return match ($tipoValor) {
            'B' => filter_var($valorRaw, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
            'I' => (string) (int) $valorRaw,
            'N' => $this->formatDecimal((float) $valorRaw),
            'D' => Carbon::parse($valorRaw)->toIso8601String(),
            default => trim((string) $valorRaw),
        };
    }

    private function formatDecimal(float $value): string
    {
        $formatted = number_format($value, 4, '.', '');

        return rtrim(rtrim($formatted, '0'), '.') ?: '0';
    }

    private function canReadFromDatabase(): bool
    {
        try {
            return Schema::hasTable('PQ_parametros_gral');
        } catch (\Throwable) {
            return false;
        }
    }
}
