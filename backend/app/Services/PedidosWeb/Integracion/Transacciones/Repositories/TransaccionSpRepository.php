<?php

namespace App\Services\PedidosWeb\Integracion\Transacciones\Repositories;

use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionLoteContext;
use Illuminate\Support\Facades\DB;

final class TransaccionSpRepository
{
    /**
     * @param  array<string, mixed>  $item
     * @return array{status: string, clave: string|null, error_code: string|null}
     */
    public function apply(
        string $procedure,
        IntegracionTransaccionLoteContext $context,
        array $item,
    ): array {
        $rows = DB::select(
            'EXEC '.$this->identifier($procedure).' ?, ?, ?, ?, ?, ?',
            [
                $context->batchId,
                $context->tenant,
                $context->modo,
                $context->fechaProceso,
                $context->origen,
                json_encode($item, JSON_THROW_ON_ERROR),
            ],
        );

        $row = $rows[0] ?? null;

        return [
            'status' => (string) ($row->status ?? 'applied'),
            'clave' => isset($row->clave) ? (string) $row->clave : null,
            'error_code' => isset($row->error_code) ? (string) $row->error_code : null,
        ];
    }

    /**
     * @return array<int, array{status: string, clave: string|null, error_code: string|null}>
     */
    public function finalize(
        string $procedure,
        IntegracionTransaccionLoteContext $context,
    ): array {
        $rows = DB::select(
            'EXEC '.$this->identifier($procedure).' ?, ?, ?, ?',
            [
                $context->batchId,
                $context->tenant,
                $context->recurso,
                $context->origen,
            ],
        );

        return array_map(static fn (object $row): array => [
            'status' => (string) ($row->status ?? 'omitted'),
            'clave' => isset($row->clave) ? (string) $row->clave : null,
            'error_code' => isset($row->error_code) ? (string) $row->error_code : null,
        ], $rows);
    }

    private function identifier(string $procedure): string
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $procedure)) {
            throw new \InvalidArgumentException('Stored procedure inválido.');
        }

        return '['.$procedure.']';
    }
}
