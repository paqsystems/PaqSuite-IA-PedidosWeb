<?php

namespace App\Services\PedidosWeb;

use App\Models\PqPedidoswebLogIntegracion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class LogIntegracionService
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function registrar(
        string $tipo,
        string $severidad,
        string $origen,
        string $mensaje,
        ?array $payload = null,
        bool $procesado = false
    ): void {
        PqPedidoswebLogIntegracion::query()->create([
            'fecha' => now(),
            'tipo' => $tipo,
            'severidad' => $severidad,
            'origen' => $origen,
            'mensaje' => $mensaje,
            'payload' => $payload !== null ? json_encode($payload, JSON_THROW_ON_ERROR) : null,
            'procesado' => $procesado,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listar(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = min(100, max(1, (int) ($filters['page_size'] ?? 20)));

        $query = PqPedidoswebLogIntegracion::query()->orderByDesc('fecha');

        if (filled($filters['fecha_desde'] ?? null)) {
            $query->where('fecha', '>=', (string) $filters['fecha_desde']);
        }

        if (filled($filters['fecha_hasta'] ?? null)) {
            $query->where('fecha', '<=', (string) $filters['fecha_hasta']);
        }

        if (filled($filters['tipo'] ?? null)) {
            $query->where('tipo', (string) $filters['tipo']);
        }

        if (filled($filters['severidad'] ?? null)) {
            $query->where('severidad', (string) $filters['severidad']);
        }

        if (filled($filters['origen'] ?? null)) {
            $query->where('origen', 'like', '%'.trim((string) $filters['origen']).'%');
        }

        if (($filters['procesado'] ?? null) !== null && $filters['procesado'] !== '') {
            $query->where('procesado', filter_var($filters['procesado'], FILTER_VALIDATE_BOOLEAN));
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        return [
            'items' => collect($paginator->items())->map(static fn (PqPedidoswebLogIntegracion $log): array => [
                'id_log' => (int) $log->id_log,
                'fecha' => optional($log->fecha)?->toIso8601String(),
                'tipo' => (string) $log->tipo,
                'severidad' => (string) $log->severidad,
                'origen' => (string) $log->origen,
                'mensaje' => (string) $log->mensaje,
                'procesado' => (bool) $log->procesado,
            ])->values()->all(),
            'page' => (int) $paginator->currentPage(),
            'page_size' => (int) $paginator->perPage(),
            'total' => (int) $paginator->total(),
            'total_pages' => (int) $paginator->lastPage(),
            'metadata' => [
                'fecha_proceso' => now()->toIso8601String(),
            ],
        ];
    }
}
