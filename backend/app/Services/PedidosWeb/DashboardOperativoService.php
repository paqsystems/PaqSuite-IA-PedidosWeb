<?php

namespace App\Services\PedidosWeb;

use App\Models\PqPedidoswebPedidoCabecera;
use App\Services\Visibility\VisibleClientsResolver;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class DashboardOperativoService
{
    public function __construct(
        private readonly VisibleClientsResolver $visibleClientsResolver,
        private readonly PedidosWebParameterService $parameterService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function obtener(User $user): array
    {
        $visibleClientCodes = $this->visibleClientsResolver
            ->visibleClientsForUser($user)
            ->pluck('cod_client');

        if ($visibleClientCodes->isEmpty()) {
            return $this->emptyResult();
        }

        $now = Carbon::now();
        $minutosWeb = $this->parameterService->getMinutosWeb();

        $presupuestosQuery = PqPedidoswebPedidoCabecera::query()
            ->whereIn('cod_cliente', $visibleClientCodes)
            ->where('estado', 99);

        $pedidosIngresadosQuery = PqPedidoswebPedidoCabecera::query()
            ->whereIn('cod_cliente', $visibleClientCodes)
            ->where(function (Builder $query) use ($now, $minutosWeb): void {
                $query->where('estado', 0)
                    ->orWhere(function (Builder $subQuery) use ($now, $minutosWeb): void {
                        $subQuery->where('estado', -1)
                            ->where(function (Builder $windowQuery) use ($now, $minutosWeb): void {
                                $windowQuery->whereNull('fechahora_ultima_actividad')
                                    ->orWhere('fechahora_ultima_actividad', '<', $now->copy()->subMinutes($minutosWeb));
                            });
                    });
            });

        $pedidosPendientesQuery = PqPedidoswebPedidoCabecera::query()
            ->whereIn('cod_cliente', $visibleClientCodes)
            ->where('estado', 1);

        $topClientePresupuestos = $this->topCliente($presupuestosQuery);
        $topClientePedidosIngresados = $this->topCliente($pedidosIngresadosQuery);

        return [
            'moneda' => [
                'simbolo' => $this->parameterService->getMonedaSimbolo(),
                'codigo' => $this->parameterService->getMonedaCodigo(),
            ],
            'presupuestosActivos' => [
                'cantidad' => (int) $presupuestosQuery->count(),
                'importe' => (float) $presupuestosQuery->sum('total'),
            ],
            'pedidosIngresados' => [
                'cantidad' => (int) $pedidosIngresadosQuery->count(),
                'importe' => (float) $pedidosIngresadosQuery->sum('total'),
            ],
            'pedidosPendientes' => [
                'cantidad' => (int) $pedidosPendientesQuery->count(),
                'importe' => (float) $pedidosPendientesQuery->sum('total'),
            ],
            'topClientePresupuestos' => $topClientePresupuestos,
            'topClientePedidosIngresados' => $topClientePedidosIngresados,
            'fechaCalculo' => $now->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function topCliente(Builder $query): array
    {
        $row = $query
            ->selectRaw('cod_cliente, SUM(total) AS importe')
            ->with('cliente')
            ->groupBy('cod_cliente')
            ->get()
            ->map(static fn (PqPedidoswebPedidoCabecera $item): array => [
                'cod_client' => (string) $item->cod_cliente,
                'razon_social' => (string) ($item->cliente?->nombre ?? ''),
                'importe' => (float) $item->importe,
            ])
            ->sort(static function (array $a, array $b): int {
                if ((float) $a['importe'] !== (float) $b['importe']) {
                    return (float) $a['importe'] < (float) $b['importe'] ? 1 : -1;
                }

                if ((string) $a['razon_social'] !== (string) $b['razon_social']) {
                    return strcmp((string) $a['razon_social'], (string) $b['razon_social']);
                }

                return strcmp((string) $a['cod_client'], (string) $b['cod_client']);
            })
            ->first();

        if (! is_array($row)) {
            return [
                'cod_client' => '',
                'razon_social' => '',
                'importe' => 0,
            ];
        }

        return $row;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyResult(): array
    {
        return [
            'moneda' => [
                'simbolo' => $this->parameterService->getMonedaSimbolo(),
                'codigo' => $this->parameterService->getMonedaCodigo(),
            ],
            'presupuestosActivos' => ['cantidad' => 0, 'importe' => 0],
            'pedidosIngresados' => ['cantidad' => 0, 'importe' => 0],
            'pedidosPendientes' => ['cantidad' => 0, 'importe' => 0],
            'topClientePresupuestos' => ['cod_client' => '', 'razon_social' => '', 'importe' => 0],
            'topClientePedidosIngresados' => ['cod_client' => '', 'razon_social' => '', 'importe' => 0],
            'fechaCalculo' => now()->toIso8601String(),
        ];
    }
}
